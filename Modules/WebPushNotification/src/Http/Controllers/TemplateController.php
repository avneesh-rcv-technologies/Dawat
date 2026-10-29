<?php

namespace Modules\WebPushNotification\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\WebPushNotification\DataView\Template;
use Modules\WebPushNotification\Models\NotificationTemplate;
use Modules\WebPushNotification\Http\Requests\TemplateRequest;

class TemplateController extends Controller
{
    public function index(Request $request)
    {
         $lists = fn_datagrid(Template::class)->process();

        return view('webpushnotification::templates.index', compact('lists'));
    }

    public function create()
    {
        return view('webpushnotification::templates.form');
    }

    public function store(TemplateRequest $request)
    {
        try {
            $data = $request->validated();
            if (!empty($data['variables']) && is_string($data['variables'])) {
                $data['variables'] = json_decode($data['variables'], true);
            }
            $data['is_active'] = $request->input('is_active', 1);
            NotificationTemplate::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Template Created Successfully!',
                'redirect_url' => route('push-notification-template.index'),
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
            $template = NotificationTemplate::findOrFail($id);
            return view('webpushnotification::templates.form', compact('template'));
        } catch (\Throwable $e) {
            return response()->json([
                'errors' => 'Something went wrong. Please try again. ' . $e->getMessage()
            ]);
        }
    }

    public function update(TemplateRequest $request, $id)
    {
        try {
            $template = NotificationTemplate::findOrFail($id);
            $data = $request->validated();
            if (!empty($data['variables']) && is_string($data['variables'])) {
                $data['variables'] = json_decode($data['variables'], true);
                }
            $data['is_active'] = $request->input('is_active', 1);
            $template->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Template Updated Successfully!',
                'redirect_url' => route('push-notification-template.index'),
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
            NotificationTemplate::destroy($id);
            return response()->json([
                'success' => true,
                'message' => 'Template Deleted Successfully',
                'redirect_url' => route('push-notification-template.index'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'errors' => 'Unable to delete ' . $e->getMessage()
            ]);
        }
    }
}
