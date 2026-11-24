<?php

namespace Modules\Seo\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Seo\Http\Requests\StoreRedirectRequest;
use Modules\Seo\Models\SeoRedirect;
use Modules\Seo\Repositories\RedirectRepository;
use Modules\Seo\Services\RedirectCache;

class RedirectController extends Controller
{
    public function __construct(
        protected RedirectRepository $repository,
        protected RedirectCache $cache,
    ) {}

    public function index(): View
    {
        $redirects = $this->repository->paginate();

        return view('seo::admin.redirects.index', compact('redirects'));
    }

    public function create(): View
    {
        return view('seo::admin.redirects.form', ['redirect' => new SeoRedirect()]);
    }

    public function store(StoreRedirectRequest $request): RedirectResponse
    {
        $this->repository->create($this->payload($request));
        $this->cache->flush();

        return redirect()
            ->route('admin.seo.redirects.index')
            ->with('success', 'Redirect created successfully.');
    }

    public function edit(SeoRedirect $redirect): View
    {
        return view('seo::admin.redirects.form', compact('redirect'));
    }

    public function update(StoreRedirectRequest $request, SeoRedirect $redirect): RedirectResponse
    {
        $this->repository->update($redirect, $this->payload($request));
        $this->cache->flush();

        return redirect()
            ->route('admin.seo.redirects.index')
            ->with('success', 'Redirect updated successfully.');
    }

    public function destroy(SeoRedirect $redirect): RedirectResponse
    {
        $this->repository->delete($redirect);
        $this->cache->flush();

        return redirect()
            ->route('admin.seo.redirects.index')
            ->with('success', 'Redirect deleted successfully.');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
    'csv' => [
        'required',
        'file',
        'mimes:csv,txt',
        'max:5120', // 5 MB (5120 KB)
    ],
]);

        $handle = fopen($request->file('csv')->getRealPath(), 'r');
        $header = fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);
            if (empty($data['from_path']) || empty($data['to_url'])) {
                continue;
            }

            $this->repository->create([
                'from_path' => $data['from_path'],
                'to_url' => $data['to_url'],
                'status_code' => (int) ($data['status_code'] ?? 301),
                'match_type' => $data['match_type'] ?? 'exact',
                'is_active' => filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        fclose($handle);
        $this->cache->flush();

        return redirect()
            ->route('admin.seo.redirects.index')
            ->with('success', 'Redirects imported successfully.');
    }

    protected function payload(StoreRedirectRequest $request): array
    {
        $matcher = app(\Modules\Seo\Services\RedirectMatcher::class);

        return [
            'from_path' => $matcher->normalizePath($request->input('from_path')),
            'to_url' => $request->input('to_url'),
            'status_code' => (int) $request->input('status_code', 301),
            'match_type' => $request->input('match_type', 'exact'),
            'is_active' => $request->boolean('is_active', true),
            'preserve_query' => $request->has('preserve_query') ? $request->boolean('preserve_query') : null,
            'notes' => $request->input('notes'),
        ];
    }
}
