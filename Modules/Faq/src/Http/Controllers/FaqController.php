<?php

namespace Modules\Faq\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Validation\Rule;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;
use Modules\Blogs\Models\Blog;
use Modules\Blogs\Services\BlogService;
use Modules\Faq\DataView\FaqGrid;
use Modules\Faq\Models\Faq;

class FaqController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $lists = fn_datagrid(FaqGrid::class)->process();
        return view('faq::faqs.index', compact('lists'));
    }


    public function editGroup(string $group)
    {

        // Laravel has already decoded the route parameter once. Decode again only
        // to support older links that URL-encoded the value before route().
        $group = rawurldecode($group);

        $groupName = $group === '__ungrouped__' ? null : $group;

        $faqs = Faq::with('blogs')
            ->where('group_name', $groupName)
            ->get();

        if ($faqs->isEmpty()) {
            return redirect()->route('admin.faqs.index')->with('status', 'Group not found.');
        }

        $blogs = Blog::orderBy('title')->get(['id', 'title']);
        // All FAQs in a group share the same post/area/service mapping — use first FAQ's mappings as default
        $selectedBlogIds = $faqs->first()->blogs()->pluck('blogs.id')->toArray();

        return view('faq::faqs.edit-group', compact('faqs', 'groupName', 'group', 'blogs', 'selectedBlogIds'));
    }

    public function updateGroup(Request $request, string $group)
    {
        try {
            $group = rawurldecode($group);

            $rules = [
                'group_name' => [
                    'required',
                    'string',
                    'max:190',
                    Rule::unique('faqs', 'group_name')->ignore($group, 'group_name'),
                ],
                'faqs'       => 'required|array|min:1',
                'blog_ids'   => 'nullable|array',
                'blog_ids.*' => 'exists:blogs,id',
            ];

            foreach ($request->input('faqs', []) as $key => $entry) {
                if (isset($entry['id'])) {
                    $rules["faqs.$key.id"] = 'required|exists:faqs,id';
                }
                $rules["faqs.$key.question"] = [
                    'required',
                    'string',
                    'max:255',
                    function ($attribute, $value, $fail) use ($entry) {
                        $query = Faq::where('question', $value);
                        if (isset($entry['id'])) {
                            $query->where('id', '!=', $entry['id']);
                        }
                        if ($query->exists()) {
                            $fail('The question has already been taken.');
                        }
                    },
                ];
                $rules["faqs.$key.answer"]     = 'required|string';
                $rules["faqs.$key.sort_order"] = 'nullable|integer|min:0';
            }

            $validated = $request->validate($rules);

            $newGroupName = $validated['group_name'] ?? null;
            $blogIds      = $validated['blog_ids'] ?? [];
            $submittedIds = [];

            foreach ($validated['faqs'] as $entry) {
                if (isset($entry['id'])) {
                    $faq = Faq::findOrFail($entry['id']);
                    $faq->update([
                        'question'   => $entry['question'],
                        'answer'     => $entry['answer'],
                        'sort_order' => $entry['sort_order'] ?? 0,
                        'group_name' => $newGroupName,
                    ]);
                    $submittedIds[] = $faq->id;
                } else {
                    $faq = Faq::create([
                        'question'   => $entry['question'],
                        'answer'     => $entry['answer'],
                        'sort_order' => $entry['sort_order'] ?? 0,
                        'group_name' => $newGroupName,
                    ]);
                    $submittedIds[] = $faq->id;
                }
                $faq->blogs()->sync($blogIds);
            }

            // Delete FAQs that were in the original group but not submitted
            Faq::where('group_name', $group === '__ungrouped__' ? null : $group)
                ->whereNotIn('id', $submittedIds)
                ->delete();

            $this->clearMappedBlogCaches($blogIds);

            return redirect()
                ->route('admin.faqs.index')
                ->with('success', 'FAQ Group updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            ApiExceptionHandler::handle($e);
            return redirect()
                ->route('admin.faqs.group.edit', $group)
                ->with('error', $e->getMessage());
        }
    }

    public function destroyGroup(string $group)
    {
        try {
            $groupName = $group === '__ungrouped__'
                ? null
                : rawurldecode($group);

            $faqs = Faq::with('blogs')->where('group_name', $groupName)->get();
            $slugs = $faqs->flatMap(fn ($faq) => $faq->blogs->pluck('post_url'))->unique()->filter();

            Faq::where('group_name', $groupName)->delete();

            foreach ($slugs as $slug) {
                BlogService::forgetFrontendBlogDetailCache((string) $slug);
            }

            if (request()->ajax() || request()->has('is_ajax')) {
                return response()->json([
                    'success' => true,
                    'message' => 'FAQ deleted successfully.',
                    'redirect_url' => route('admin.faqs.index')
                ]);
            }

            return redirect()
                ->route('admin.faqs.index')
                ->with('success', 'FAQ deleted successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            ApiExceptionHandler::handle($e);
            return redirect()
                ->route('admin.faqs.index')
                ->with('error', $e->getMessage());
        }
    }

    public function create()
    {
        $blogs = Blog::orderBy('title')->get(['id', 'title']);
        return view('faq::faqs.create', compact('blogs'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'group_name'         => 'required|string|unique:faqs,group_name|max:190',
                'faqs'               => 'required|array|min:1',
                'faqs.*.question'    => 'required|string|unique:faqs,question|max:255',
                'faqs.*.answer'      => 'required|string',
                'faqs.*.sort_order'  => 'nullable|integer|min:0',
                'blog_ids'           => 'nullable|array',
                'blog_ids.*'         => 'exists:blogs,id',
            ]);

            $blogIds    = $validated['blog_ids'] ?? [];
            $groupName  = $validated['group_name'] ?? null;

            foreach ($validated['faqs'] as $entry) {
                $faq = Faq::create([
                    'question'   => $entry['question'],
                    'answer'     => $entry['answer'],
                    'sort_order' => $entry['sort_order'] ?? 0,
                    'group_name' => $groupName,
                ]);
                $faq->blogs()->sync($blogIds);
            }

            $count = count($validated['faqs']);
            $this->clearMappedBlogCaches($blogIds);

            return redirect()
                ->route('admin.faqs.index')
                ->with('success', 'FAQ created successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            ApiExceptionHandler::handle($e);
            return redirect()
                ->route('admin.faqs.create')
                ->with('error', $e->getMessage());
        }
    }

    public function edit(Faq $faq)
    {
        $blogs = Blog::orderBy('title')->get(['id', 'title']);
        $selectedBlogIds = $faq->blogs()->pluck('blogs.id')->toArray();
        return view('faq::faqs.edit', compact('faq', 'blogs', 'selectedBlogIds'));
    }

    public function update(Request $request, Faq $faq)
    {
        try {
            $rules = [
                'group_name' => [
                    'required',
                    'string',
                    'max:190',
                    Rule::unique('faqs', 'group_name')->ignore($faq->group_name, 'group_name'),
                ],
                'faqs'       => 'required|array|min:1',
                'blog_ids'   => 'nullable|array',
                'blog_ids.*' => 'exists:blogs,id',
            ];

            foreach ($request->input('faqs', []) as $key => $entry) {
                if (isset($entry['id'])) {
                    $rules["faqs.$key.id"] = 'required|exists:faqs,id';
                }
                $rules["faqs.$key.question"] = [
                    'required',
                    'string',
                    'max:255',
                    function ($attribute, $value, $fail) use ($entry) {
                        $query = Faq::where('question', $value);
                        if (isset($entry['id'])) {
                            $query->where('id', '!=', $entry['id']);
                        }
                        if ($query->exists()) {
                            $fail('The question has already been taken.');
                        }
                    },
                ];
                $rules["faqs.$key.answer"]     = 'required|string';
                $rules["faqs.$key.sort_order"] = 'nullable|integer|min:0';
            }

            $validated = $request->validate($rules);

            $newGroupName = $validated['group_name'] ?? null;
            $blogIds      = $validated['blog_ids'] ?? [];
            $submittedIds = [];

            // We need to track the old group name to handle deletions/group moves
            $oldGroupName = $faq->group_name;

            foreach ($validated['faqs'] as $entry) {
                if (isset($entry['id'])) {
                    $item = Faq::findOrFail($entry['id']);
                    $item->update([
                        'question'   => $entry['question'],
                        'answer'     => $entry['answer'],
                        'sort_order' => $entry['sort_order'] ?? 0,
                        'group_name' => $newGroupName,
                    ]);
                    $submittedIds[] = $item->id;
                } else {
                    $item = Faq::create([
                        'question'   => $entry['question'],
                        'answer'     => $entry['answer'],
                        'sort_order' => $entry['sort_order'] ?? 0,
                        'group_name' => $newGroupName,
                    ]);
                    $submittedIds[] = $item->id;
                }
                $item->blogs()->sync($blogIds);
            }

            $this->clearMappedBlogCaches($blogIds);

            return redirect()
                ->route('admin.faqs.index')
                ->with('success', 'FAQ updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            ApiExceptionHandler::handle($e);
            return redirect()
                ->route('admin.faqs.index')
                ->with('error', $e->getMessage());
        }
    }

    public function destroy(Faq $faq)
    {
        try {
            $slugs = $faq->blogs()->pluck('post_url')->all();
            $faq->delete();
            foreach ($slugs as $slug) {
                BlogService::forgetFrontendBlogDetailCache((string) $slug);
            }

            return response()->json([
                'success' => true,
                'message' => 'FAQ deleted successfully.',
                'redirect_url' => route('admin.faqs.index')
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            ApiExceptionHandler::handle($e);
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ]);
        }
    }

    protected function clearMappedBlogCaches(array $blogIds): void
    {
        if (empty($blogIds)) {
            return;
        }

        $slugs = Blog::whereIn('id', $blogIds)->pluck('post_url');
        foreach ($slugs as $slug) {
            BlogService::forgetFrontendBlogDetailCache((string) $slug);
        }
    }
}
