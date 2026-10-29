<?php

namespace Modules\Twilio\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Twilio\Models\SmsTemplate;
use Modules\Base\Http\Controllers\BaseController;
use Modules\Twilio\DataView\SmsTemplateGrid;
use Modules\Twilio\Http\Requests\SmsTemplateRequest;

class SmsTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $lists = fn_datagrid(SmsTemplateGrid::class)->process();

        return view('twilio::templates.index', compact('lists'));
    }

    public function create()
    {
        return view('twilio::templates.form');
    }

    public function store(SmsTemplateRequest $request)
    {
        try {
            $data = $request->validated();
            if (!empty($data['variables']) && is_string($data['variables'])) {
                $data['variables'] = json_decode($data['variables'], true);
            }
            $data['is_active'] = $request->input('is_active', 1);

            if (array_key_exists('body', $data)) {
                $data['content'] = $data['body'];
                unset($data['body']);
            }
            SmsTemplate::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Template Created Successfully!',
                'redirect_url' => route('sms-templates.index'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'errors' => 'Something went wrong. Please try again. ' . $e->getMessage()
            ]);
        }
    }

    public function edit(Request $request, $id)
    {
        try {
            $template = SmsTemplate::findOrFail($id);
            return view('twilio::templates.form', compact('template'));
        } catch (\Throwable $e) {
            return response()->json([
                'errors' => 'Something went wrong. Please try again. ' . $e->getMessage()
            ]);
        }
    }

    public function update(SmsTemplateRequest $request, $id)
    {
        try {
            $template = SmsTemplate::findOrFail($id);
            $data = $request->validated();
            if (!empty($data['variables']) && is_string($data['variables'])) {
                $data['variables'] = json_decode($data['variables'], true);
            }
            $data['is_active'] = $request->input('is_active', 1);
            if (array_key_exists('body', $data)) {
                $data['content'] = $data['body'];
                unset($data['body']);
            }
            $template->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Template Updated Successfully!',
                'redirect_url' => route('sms-templates.index'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'errors' => 'Something went wrong. Please try again. ' . $e->getMessage()
            ]);
        }
    }

   public function destroy(Request $request, $id)
{
    try {
      
        $template = SmsTemplate::find($id);
   
        if (!$template) {
            return response()->json([
                'success' => false,
                'message' => 'Template not found'
            ], 200);
        }

        $template->delete();

        return response()->json([
            'success' => true,
            'message' => 'Template Deleted Successfully',
            'redirect_url' => route('sms-templates.index'),
        ], 200);

    } catch (\Throwable $e) {
        \Log::error('SMS Template Delete Error: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Unable to delete template. Please try again.'
        ], 500);
    }
}
    /**
     * Toggle template status
     */
    public function toggleStatus($id)
    {
        $template = SmsTemplate::findOrFail($id);
        $template->update(['is_active' => !$template->is_active]);

        return $this->successResponse(
            __('SMS Template status updated successfully'),
            ['reload' => true]
        );
    }
}
