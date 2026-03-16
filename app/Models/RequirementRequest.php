<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RequirementRequest extends Model
{
    use HasFactory;
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
        'completed_seen_at',
        'clarification_remarks',
        'clarification_requested_by',
        'clarification_requested_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'assigned_at' => 'datetime',
        'progress_at' => 'datetime',
        'completed_at' => 'datetime',
        'completed_seen_at' => 'datetime',
        'clarification_requested_at' => 'datetime',
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

    public function clarificationRequester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'clarification_requested_by');
    }

    public function history(): HasMany
    {
        return $this->hasMany(RequestHistory::class)->orderBy('created_at', 'desc');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(RequestAttachment::class);
    }

    public function nudges(): HasMany
    {
        return $this->hasMany(RequestNudge::class)->orderBy('created_at', 'desc');
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

    public function needsClarification(): bool
    {
        return $this->status === 'clarification_needed';
    }

    public function canBeEditedByCreator(): bool
    {
        return $this->isPending() || $this->needsClarification();
    }

    public function canBeEditedByFmoAdmin(): bool
    {
        return $this->isPending();
    }

    public function canEscalate(): bool
    {
        // Can escalate if status is approved, assigned, or in_progress AND both assignment fields are set
        if (!($this->isApproved() || $this->isAssigned() || $this->isInProgress())) {
            return false;
        }

        return !is_null($this->assigned_to) && !is_null($this->assigned_by);
    }

    public function getEscalationMailtoLink(): ?string
    {
        if (!$this->canEscalate()) {
            return null;
        }

        // Get assignee and assigner emails, excluding super_admin users
        $assigneeEmail = ($this->assignee && $this->assignee->role !== 'super_admin') ? $this->assignee->email : null;
        $assignerEmail = ($this->assigner && $this->assigner->role !== 'super_admin') ? $this->assigner->email : null;

        $toEmails = implode(',', array_filter([$assigneeEmail, $assignerEmail]));

        if (empty($toEmails)) {
            return null;
        }

        // Get all active FMO admins for CC
        $fmoAdmins = User::where('role', 'fmo_admin')
            ->where('is_active', true)
            ->pluck('email')
            ->implode(',');

        $ccEmails = $fmoAdmins ?: '';

        // Construct email subject
        $subject = "Clarification Request - Request #{$this->id}: {$this->item}";

        // Format email body with request details
        $dimensions = $this->dimensions ?: 'N/A';
        $remarks = $this->remarks ?: 'N/A';

        // Get human-readable status
        $statusMap = [
            'pending' => 'Pending',
            'approved' => 'Approved',
            'assigned' => 'Assigned',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'rejected' => 'Rejected',
            'cancelled' => 'Cancelled',
            'clarification_needed' => 'Clarification Needed',
        ];
        $statusDisplay = $statusMap[$this->status] ?? ucfirst($this->status);

        $body = "Request ID: {$this->id}\n";
        $body .= "Item: {$this->item}\n";
        $body .= "Quantity: {$this->qty}\n";
        $body .= "Dimensions: {$dimensions}\n";
        $body .= "Location: {$this->location}\n";
        $body .= "Remarks: {$remarks}\n";
        $body .= "Current Status: {$statusDisplay}\n";
        $body .= "\nPlease provide clarification on this request or advise on current progress.";

        // Build Gmail compose URL
        $gmailUrl = 'https://mail.google.com/mail/?view=cm&fs=1';
        $gmailUrl .= '&to=' . urlencode($toEmails);
        $gmailUrl .= '&cc=' . urlencode($ccEmails);
        $gmailUrl .= '&su=' . urlencode($subject);
        $gmailUrl .= '&body=' . urlencode($body);

        return $gmailUrl;
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
