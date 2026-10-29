<?php

namespace Modules\Twilio\Models;

use Illuminate\Database\Eloquent\Model;

class TwilioMessage extends Model
{
    protected $table = 'twilio_messages';

    protected $fillable = [
        'phone_number_id',
        'admin_id',
        'to',
        'body',
        'delivery_sid',
        'status',
        'error',
        'sent_at',
        'response_at',
        'response',
        'campaign_id',
        'is_auto_response',
        'original_message_id',
        'has_auto_response',
        'auto_response_at',
        'needs_manual_review',
        'manual_review_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'response_at' => 'datetime',
        'auto_response_at' => 'datetime',
        'manual_review_at' => 'datetime',
        'is_auto_response' => 'boolean',
        'has_auto_response' => 'boolean',
        'needs_manual_review' => 'boolean',
    ];

    public function phoneNumber()
    {
        return $this->belongsTo(PhoneNumber::class, 'phone_number_id');
    }

    public function originalMessage()
    {
        return $this->belongsTo(TwilioMessage::class, 'original_message_id');
    }

    public function campaign()
    {
        return $this->belongsTo(ComposeCampaign::class, 'campaign_id');
    }
}
