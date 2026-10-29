<?php

namespace Modules\Twilio\Services;

use Twilio\Rest\Client;

class TwilioService
{
    protected $client;
    protected $sid;
    protected $token;
    protected $from;

    public function __construct()
    {
        $this->sid = fn_get_setting('twilio.sid');
        $this->token = fn_get_setting('twilio.token');
        $this->from = fn_get_setting('twilio.phone');
        $this->client = new Client($this->sid, $this->token);
    }

    public function sendMessage($to, $message)
    {
        return $this->client->messages->create($to, [
            'from' => $this->from,
            'body' => $message,
        ]);
    }

    // Add more Twilio API methods as needed
}
