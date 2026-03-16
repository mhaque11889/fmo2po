<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestHistory extends Model
{
    use HasFactory;
    protected $table = 'request_history';

    protected $fillable = [
        'requirement_request_id',
        'user_id',
        'action',
        'changes',
        'remarks',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(RequirementRequest::class, 'requirement_request_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get a human-readable description of the action
     */
    public function getActionDescriptionAttribute(): string
    {
        return match ($this->action) {
            'created' => 'Request created',
            'edited' => 'Request details modified',
            'approved' => 'Request approved',
            'rejected' => 'Request rejected',
            'cancelled' => 'Request cancelled',
            'assigned' => 'Request assigned',
            'in_progress' => 'Marked as in progress',
            'completed' => 'Marked as completed',
            default => ucfirst($this->action),
        };
    }
}
