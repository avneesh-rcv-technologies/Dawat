<?php

namespace Modules\Cms\Http\Controllers\Page;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;
use Modules\Cms\Models\CmsPage;
use Modules\Cms\DataView\CmsPageGrid;
use Modules\Cms\Services\CmsPageService;
use Throwable;
use App\Rules\NoScripts;
use App\Traits\SanitizesInput;

class PagesController extends Controller
{
    // use SanitizesInput;
    protected CmsPageService $service;

    public function __construct(CmsPageService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $lists = fn_datagrid(CmsPageGrid::class)->process();
        return view('cms::pages.index', compact('lists'));
    }

    public function create()
    {
        return view('cms::pages.form');
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'page_title' => ['required', 'string', 'max:100', new NoScripts, 'regex:/^[\p{L}0-9\s\-\&\(\)\:\/\'\",\.]+$/u'],
                'description' => ['required', 'string'],
                'slug' => 'nullable|string|max:120|unique:cms_page_settings,slug',
                'status' => 'required|boolean',
                'meta_title' => ['nullable', 'string', 'max:100', new NoScripts],
                'meta_description' => ['nullable', 'string', 'max:150', new NoScripts],
                'meta_keywords' => ['nullable', 'string', 'max:150', new NoScripts],
            ]);

            $validator->setCustomMessages([
                'page_title.regex' => 'Page title contains invalid characters. Only letters, numbers, spaces and common punctuation (- & ( ) : / \' \" , .) are allowed.',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $this->service->create($request);

            if ($request->boolean('is_ajax')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Page created successfully!',
                    'redirect_url' => route('cms.pages.index'),
                ]);
            }

            return redirect()
                ->route('cms.pages.index')
                ->with('success', 'Page created successfully!');
        } catch (Throwable $e) {
            if ($request->boolean('is_ajax')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Something went wrong: ' . $e->getMessage(),
                ], 200);
            }
            return ApiExceptionHandler::handle($e);
        }
    }

    public function edit(CmsPage $page)
    {
        return view('cms::pages.form', compact('page'));
    }

    public function update(Request $request, CmsPage $page)
    {
        try {
            $validator = Validator::make($request->all(), [
                'page_title' => ['required', 'string', 'max:100', new NoScripts, 'regex:/^[\p{L}0-9\s\-\&\(\)\:\/\'\",\.]+$/u'],
                'description' => ['required', 'string', new NoScripts(allowHtml: true)],
                'slug' => 'nullable|string|max:120|unique:cms_page_settings,slug,' . $page->id,
                'status' => 'required|boolean',
                'meta_title' => ['nullable', 'string', 'max:100', new NoScripts],
                'meta_description' => ['nullable', 'string', 'max:150', new NoScripts],
                'meta_keywords' => ['nullable', 'string', 'max:150', new NoScripts],
            ]);

            $validator->setCustomMessages([
                'page_title.regex' => 'Page title contains invalid characters. Only letters, numbers, spaces and common punctuation (- & ( ) : / \' \" , .) are allowed.',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 200);
            }

            $this->service->update($request, $page);

            if ($request->boolean('is_ajax')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Page updated successfully!',
                    'redirect_url' => route('cms.pages.index'),
                ]);
            }

            return redirect()
                ->route('cms.pages.index')
                ->with('success', 'Page updated successfully!');
        } catch (Throwable $e) {
            if ($request->boolean('is_ajax')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Something went wrong: ' . $e->getMessage(),
                ], 200);
            }
            return ApiExceptionHandler::handle($e);
        }
    }

    public function destroy(CmsPage $page)
    {
        try {
            $this->service->delete($page);

            return response()->json([
                'success' => true,
                'message' => 'Page deleted successfully!',
                'redirect_url' => route('cms.pages.index'),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }

    public function bulkDelete(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:cms_pages,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $this->service->bulkDelete($request->ids);

            return response()->json([
                'success' => true,
                'message' => 'Pages deleted successfully!',
            ]);
        } catch (Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function toggleStatus(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:cms_pages,id',
                'status' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $this->service->updateStatus($request->ids, $request->boolean('status'));

            return response()->json([
                'success' => true,
                'message' => 'Page status updated successfully!',
            ]);
        } catch (Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }
}
