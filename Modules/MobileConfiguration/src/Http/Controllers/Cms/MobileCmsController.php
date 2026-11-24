<?php

namespace Modules\MobileConfiguration\Http\Controllers\Cms;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Rules\NoScripts;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;
use Modules\MobileConfiguration\Models\MobileCms;
use Modules\MobileConfiguration\DataView\MobileCmsGrid;
use Modules\MobileConfiguration\Services\MobileCmsService;
use Throwable;

class MobileCmsController extends Controller
{
    protected MobileCmsService $service;

    public function __construct(MobileCmsService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $lists = fn_datagrid(MobileCmsGrid::class)->process();
        return view('mobileconfiguration::cms.index', compact('lists'));
    }

    public function create()
    {
        return view('mobileconfiguration::cms.form');
    }

    public function store(Request $request)
    {
        try {
            $validator = $this->validator($request);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $this->service->create($request);

            if ($request->boolean('is_ajax')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Mobile CMS created successfully!',
                    'redirect_url' => route('mobileconfiguration.cms.index'),
                ]);
            }

            return redirect()
                ->route('mobileconfiguration.cms.index')
                ->with('success', 'Mobile CMS created successfully!');
        } catch (Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function edit(MobileCms $cm)
    {
        $cm->load('cmsPage');

        return view('mobileconfiguration::cms.form', ['mobileCms' => $cm]);
    }

    /**
     * The page content is entered here directly; the linked CMS page is created/updated by the service.
     */
    protected function validator(Request $request, ?int $cmsPageId = null): \Illuminate\Validation\Validator
    {
        $validator = Validator::make($request->all(), [
            'page_title' => ['required', 'string', 'max:100', new NoScripts, 'regex:/^[\p{L}0-9\s\-\&\(\)\:\/\'\",\.]+$/u'],
            'slug' => ['nullable', 'string', 'max:120', 'alpha_dash', Rule::unique('cms_page_settings', 'slug')->ignore($cmsPageId)],
            'description' => ['required', 'string', new NoScripts(allowHtml: true)],
            'meta_title' => ['nullable', 'string', 'max:100', new NoScripts],
            'meta_description' => ['nullable', 'string', 'max:150', new NoScripts],
            'meta_keywords' => ['nullable', 'string', 'max:150', new NoScripts],
            'status' => 'required|boolean',
            'position' => 'nullable|integer|min:0',
            'image' => 'nullable|image|max:5120',
        ]);

        $validator->setCustomMessages([
            'page_title.regex' => 'Title contains invalid characters. Only letters, numbers, spaces and common punctuation (- & ( ) : / \' " , .) are allowed.',
        ]);

        return $validator;
    }

    public function update(Request $request, MobileCms $cm)
    {
        try {
            $validator = $this->validator($request, $cm->cms_page_id);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $this->service->update($request, $cm);

            if ($request->boolean('is_ajax')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Mobile CMS updated successfully!',
                    'redirect_url' => route('mobileconfiguration.cms.index'),
                ]);
            }

            return redirect()
                ->route('mobileconfiguration.cms.index')
                ->with('success', 'Mobile CMS updated successfully!');
        } catch (Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function destroy(MobileCms $cm)
    {
        try {
            $this->service->delete($cm);

            return response()->json([
                'success' => true,
                'message' => 'Mobile CMS deleted successfully!',
                'redirect_url' => route('mobileconfiguration.cms.index'),
            ]);
        } catch (Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }
    public function bulkDelete(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:mobile_cms,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $this->service->bulkDelete($request->ids);

            return response()->json([
                'success' => true,
                'message' => 'Mobile CMS deleted successfully!',
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
                'ids.*' => 'integer|exists:mobile_cms,id',
                'status' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 200);
            }

            $this->service->updateStatus($request->ids, $request->boolean('status'));

            return response()->json([
                'success' => true,
                'message' => 'Mobile CMS status updated successfully!',

            ]);
        } catch (Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }
}
