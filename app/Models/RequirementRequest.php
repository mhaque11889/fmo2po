<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RequirementRequest extends Model
{
    protected $fillable = [
        'created_by',
        'item',
        'dimensions',
        'qty',
        'location',
        'remarks',
        'status',
        'approved_by',
        'approved_at',
        'assigned_to',
        'assigned_by',
        'assigned_at',
        'progress_remarks',
        'progress_at',
        'completion_remarks',
        'completed_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'assigned_at' => 'datetime',
        'progress_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function history(): HasMany
    {
        return $this->hasMany(RequestHistory::class)->orderBy('created_at', 'desc');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(RequestAttachment::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isAssigned(): bool
    {
        return $this->status === 'assigned';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function canBeEditedByCreator(): bool
    {
        return $this->isPending();
    }

    public function canBeEditedByFmoAdmin(): bool
    {
        return $this->isPending();
    }

    public static function logHistory(int $requestId, int $userId, string $action, ?array $changes = null, ?string $remarks = null): void
    {
        RequestHistory::create([
            'requirement_request_id' => $requestId,
            'user_id' => $userId,
            'action' => $action,
            'changes' => $changes,
            'remarks' => $remarks,
        ]);
    }
}
