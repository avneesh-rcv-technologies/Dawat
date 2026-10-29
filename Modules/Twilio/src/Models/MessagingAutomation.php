<?php

namespace Modules\Twilio\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Acl\Models\Admin;

class MessagingAutomation extends Model
{
    protected $table = 'twilio_messaging_automation';

    protected $fillable = [
        'admin_id',
        'keyword',
        'response_type',
        'response_text',
        'is_active',
        'priority',
        'use_chatgpt',
        'chatgpt_prompt',
        'case_sensitive',
        'exact_match',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'use_chatgpt' => 'boolean',
        'case_sensitive' => 'boolean',
        'exact_match' => 'boolean',
    ];

    /**
     * Get the admin that owns the automation rule.
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    /**
     * Check if this automation rule matches the given message.
     *
     * @param string $message
     * @return bool
     */
    public function matches(string $message): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $keyword = $this->keyword;

        if (!$this->case_sensitive) {
            $keyword = strtolower($keyword);
            $message = strtolower($message);
        }

        if ($this->exact_match) {
            return $message === $keyword;
        }

        return str_contains($message, $keyword);
    }

    /**
     * Get the response for this automation rule.
     *
     * @param string $inboundMessage
     * @return string|null
     */
    public function getResponse(string $inboundMessage): ?string
    {
        if ($this->use_chatgpt && !empty($this->chatgpt_prompt)) {
            // This will be handled by the service
            return null;
        }

        return $this->response_text;
    }
}
