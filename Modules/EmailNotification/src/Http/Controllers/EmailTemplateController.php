<?php

namespace Modules\EmailNotification\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\EmailNotification\DataView\Template;
use Modules\EmailNotification\Http\Requests\TemplateRequest;
use Modules\EmailNotification\Models\NotificationTemplate;

class EmailTemplateController extends Controller
{
    public function index(Request $request)
    {
        $lists = fn_datagrid(Template::class)->process();

        return view('emailnotification::index', compact('lists'));
    }


    public function create()
    {
        return view('emailnotification::form');
    }

    public function store(TemplateRequest $request)
    {
        try {
            NotificationTemplate::create($request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Template Created Successfully!',
                'redirect_url' => route('notification.email.templates.index'),
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
            $template = NotificationTemplate::findorfail($id);
            return view('emailnotification::form', compact('template'));
        } catch (\Throwable $e) {
            return response()->json([
                'errors' => 'Something went wrong. Please try again. ' . $e->getMessage()
            ]);
        }
    }

    public function update(TemplateRequest $request, $id)
    {

        try {
            $template = NotificationTemplate::findorfail($id);
            $template->update($request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Template Updated Successfully!',
                'redirect_url' => route('notification.email.templates.index'),
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
                'redirect_url' => route('notification.email.templates.index'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'errors' => 'Unable to delete ' . $e->getMessage()
            ]);
        }
    }
    public function editEmailDesign($key)
    {
        abort_unless(in_array($key, ['header', 'footer'], true), 404);

        $fullKey = "mail.$key";
        $setting = fn_get_setting($fullKey);

        return view('emailnotification::edit', [
            'key'  => $key,
            'html' => $setting['html'] ?? '',
        ]);
    }

    public function updateEmailDesign(Request $request)
    {
        $validated = $request->validate([
            'key'  => 'required|in:header,footer',
            'html' => 'present|nullable|string',
        ]);

        $fullKey = "mail." . $validated['key'];

        fn_set_setting($fullKey, [
            'html' => $validated['html'] ?? '',
        ]);

        return redirect()
            ->route('notification.email-design.edit', $validated['key'])
            ->with('success', ucfirst($validated['key']) . ' updated successfully!');
    }
}
