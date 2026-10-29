<?php

namespace Modules\Twilio\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Twilio\DataView\TwilioGrid;
use Modules\Twilio\Models\PhoneNumber;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;
use Modules\Twilio\DataView\TwilioPhoneMessageGrid;
use Throwable;

class DashboardController extends Controller
{
    public function index()
    {
        $lists = fn_datagrid(TwilioGrid::class)->process();
        return view('twilio::phone_numbers.index', compact('lists'));
    }

    public function show(Request $request, $id)
    {
        $phoneNumber = PhoneNumber::findOrFail($id);
        $lists = fn_datagrid(TwilioPhoneMessageGrid::class)->process();
        return view('twilio::phone_numbers.show', compact('lists', 'phoneNumber'));
    }

    public function messageOverview($id)
    {
        $message = \Modules\Twilio\Models\TwilioMessage::with(['phoneNumber', 'originalMessage'])->findOrFail($id);
        return view('twilio::messages.overview', compact('message'));
    }

    public function destroy(Request $request, $id)
    {
        try {
            $phoneNumber = PhoneNumber::findOrFail($id);
            $phoneNumber->delete();
            return response()->json([
                'success' => true,
                'message' => 'Phone number deleted successfully',
                'redirect' => route('admin.twilio.index'),
            ]);
        } catch (Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function massSend(Request $request)
    {
        try {
            $ids = $request->input('ids', []);
            $numbers = PhoneNumber::whereIn('id', $ids)->get();

            $smsService = app(\Modules\Twilio\Services\SmsService::class);
            $results = [];
            foreach ($numbers as $number) {
                try {

                    $smsService->sendToNumber($number, $number->message ?? '');
                    $results[] = ['id' => $number->id, 'to' => $number->phone_number, 'status' => 'queued'];
                } catch (\Exception $e) {
                    ApiExceptionHandler::handle($e);
                    $results[] = ['id' => $number->id, 'to' => $number->phone_number, 'status' => 'failed', 'error' => $e->getMessage()];
                }
            }
            session()->flash('success', 'Messages queued successfully');
            return redirect()->back()->withErrors(['success' => true, 'message' => 'Messages queued successfully']);
        } catch (Throwable $e) {
            ApiExceptionHandler::handle($e);
            session()->flash('error', 'Failed to send messages');
            return redirect()->back()->withErrors(['error' => true, 'error' => 'Failed to send messages:']);
        }
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:twilio_phone_numbers,id',
        ]);
        try {
            PhoneNumber::whereIn('id', $request->ids)->delete();
            session()->flash('success', 'Phone numbers deleted successfully!');
            return redirect()->back();
        } catch (Throwable $e) {
            ApiExceptionHandler::handle($e);
            session()->flash('error', 'Failed to delete phone numbers');
            return redirect()->back();
        }
    }

    public function massExport(Request $request)
    {
        try {
            $ids = $request->input('ids', []);
            $query = PhoneNumber::with('messages');

            if (!empty($ids)) {
                $query->whereIn('id', $ids);
            }

            $phoneNumbers = $query->get();

            if ($phoneNumbers->isEmpty()) {
                session()->flash('error', 'No phone numbers found for export');
                return redirect()->back();
            }

            // Create CSV content
            $csvData = [];
            $headers = [
                'Phone Number',
                'Phone Status',
                'Phone Message',
                'Phone Response',
                'Phone Sent At',
                'Phone Response At',
                'Phone Retry Count',
                'Message Status',
                'Message Body',
                'Message Sent At',
                'Message Error'
            ];

            $csvData[] = $headers;

            foreach ($phoneNumbers as $phoneNumber) {
                // If phone number has no messages, create one row with phone number data
                if ($phoneNumber->messages->isEmpty()) {
                    $csvData[] = [
                        $phoneNumber->phone_number,
                        $phoneNumber->status,
                        $phoneNumber->message,
                        $phoneNumber->response,
                        $phoneNumber->sent_at ? $phoneNumber->sent_at->format('Y-m-d H:i:s') : '',
                        $phoneNumber->response_at ? $phoneNumber->response_at->format('Y-m-d H:i:s') : '',
                        $phoneNumber->retry_count ?? 0,
                        '', // Message status
                        '', // Message body
                        '', // Message sent at
                        ''  // Message error
                    ];
                } else {
                    // Create one row per message with phone number data repeated
                    foreach ($phoneNumber->messages as $message) {
                        $csvData[] = [
                            $phoneNumber->phone_number,
                            $phoneNumber->status,
                            $phoneNumber->message,
                            $phoneNumber->response,
                            $phoneNumber->sent_at ? $phoneNumber->sent_at->format('Y-m-d H:i:s') : '',
                            $phoneNumber->response_at ? $phoneNumber->response_at->format('Y-m-d H:i:s') : '',
                            $phoneNumber->retry_count ?? 0,
                            $message->status,
                            $message->body,
                            $message->sent_at ? $message->sent_at->format('Y-m-d H:i:s') : '',
                            $message->error ?? ''
                        ];
                    }
                }
            }

            // Generate CSV string
            $output = fopen('php://temp', 'r+');
            foreach ($csvData as $row) {
                fputcsv($output, $row);
            }
            rewind($output);
            $csvContent = stream_get_contents($output);
            fclose($output);

            // Generate filename with timestamp
            $filename = 'twilio_export_' . now()->format('Y-m-d_H-i-s') . '.csv';

            return response($csvContent)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        } catch (Throwable $e) {
            ApiExceptionHandler::handle($e);
            session()->flash('error', 'Failed to export data: ' . $e->getMessage());
            return redirect()->back();
        }
    }


    public function configuration()
    {
        return view('twilio::configuration.index');
    }
}
