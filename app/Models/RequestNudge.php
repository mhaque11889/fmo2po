<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestNudge extends Model
{
    protected $fillable = [
        'requirement_request_id',
        'sent_by',
        'target_user_id',
        'message',
        'acknowledged_at',
        'reply',
        'replied_at',
        'reply_seen_at',
    ];

    protected $casts = [
        'acknowledged_at' => 'datetime',
        'replied_at' => 'datetime',
        'reply_seen_at' => 'datetime',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(RequirementRequest::class, 'requirement_request_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function isAcknowledged(): bool
    {
        return $this->acknowledged_at !== null;
    }

    public function hasReply(): bool
    {
        return $this->reply !== null;
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('acknowledged_at');
    }
}
