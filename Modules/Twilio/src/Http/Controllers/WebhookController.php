<?php

namespace Modules\Twilio\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Twilio\Services\SmsService;

class WebhookController extends Controller
{
    public function handle(Request $request, SmsService $smsService)
    {
        $payload = $request->all();
        Log::info('Twilio webhook received', $payload);

        $smsService->handleInbound($payload);

        // Twilio expects an empty 200 OK or XML response
        return response('OK', 200);
    }
}
