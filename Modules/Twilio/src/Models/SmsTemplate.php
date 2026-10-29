<?php

namespace Modules\Twilio\Models;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;

class SmsTemplate extends Model
{
    // use SoftDeletes;

    protected $table = 'sms_templates';
    
    protected $fillable = [
        'name',
        'subject',
        'content',
        'event_code',
        'is_active',
        'variables'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'variables' => 'array'
    ];

    public function compile(array $data = [])
    {
        $content = $this->content;
        
        foreach ($data as $key => $value) {
            $content = str_replace("{{" . $key . "}}", $value, $content);
        }
        
        return [
            'content' => $content,
            'subject' => $this->subject
        ];
    }
}
