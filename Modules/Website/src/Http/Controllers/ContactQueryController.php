<?php

namespace Modules\Website\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Website\DataView\ContactQueryGrid;
use Modules\Website\Http\Requests\StoreContactQueryRequest;
use Modules\Website\Models\ContactQuery;
use Modules\Website\Jobs\SendContactQueryThankYou;
use Modules\Notifications\Models\NotificationLog;

class ContactQueryController extends Controller
{
    public function store(StoreContactQueryRequest $request)
    {
        $query = ContactQuery::create($request->safe()->only(['first_name', 'last_name', 'email', 'message']));

        try {
            app(\Modules\Website\Services\ContactQueryAdminNotifier::class)->notify($query);
        } catch (\Throwable $exception) {
            report($exception);
        }

        $log = null;
        try {
            $log = NotificationLog::create([
                'event_code' => 'contact_query_thank_you',
                'channel_name' => 'email',
                'notifiable_type' => $query->getMorphClass(),
                'notifiable_id' => $query->id,
                'content' => ['recipient' => $query->email, 'template' => 'contact_query_thank_you'],
                'status' => 'queued',
            ]);
            SendContactQueryThankYou::dispatch($query->email, $query->first_name, $log->id)->afterCommit();
        } catch (\Throwable $exception) {
            // The enquiry is saved even if the queue or synchronous mail transport is unavailable.
            $log?->update(['status' => 'failed', 'error_message' => $exception->getMessage()]);
            report($exception);
        }

        return redirect()->route('website.contact')->with('contact_success', 'Thank you! Your message has been received. Our team will get back to you soon.');
    }

    public function index()
    {
        $lists = fn_datagrid(ContactQueryGrid::class)->process();

        return view('website::admin.contact-queries.index', compact('lists'));
    }
    public function show(ContactQuery $contactQuery)
    {
        return view('website::admin.contact-queries.show', compact('contactQuery'));
    }

    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:1000'],
            'ids.*' => ['required', 'integer', 'distinct', 'exists:contact_queries,id'],
        ]);

        ContactQuery::whereIn('id', $validated['ids'])->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Selected contact queries deleted successfully.',
                'redirect_url' => route('website.contact-queries.index'),
            ]);
        }

        return redirect()->route('website.contact-queries.index')
            ->with('success', 'Selected contact queries deleted successfully.');
    }

    public function destroy(Request $request, ContactQuery $contactQuery)
    {
        $contactQuery->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Contact query deleted successfully.',
                'redirect_url' => route('website.contact-queries.index'),
            ]);
        }

        return redirect()->route('website.contact-queries.index')
            ->with('success', 'Contact query deleted successfully.');
    }
}
