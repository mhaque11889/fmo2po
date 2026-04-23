<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'avatar',
        'settings',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'settings' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Default settings for users
     */
    public static function defaultSettings(): array
    {
        return [
            'refresh_interval'         => 60,
            'notification_sound'       => 'chime',
            'notify_on_new_request'    => true,
            'notify_on_status_change'  => true,
            'notify_on_task_assigned'  => true,
            // Email notification master switch: all | key_only | custom | none
            'email_notifications'      => 'none',
            // FMO User flags
            'email_on_approved'        => false,
            'email_on_rejected'        => false,
            'email_on_clarification'   => false,
            'email_on_assigned'        => false,
            'email_on_in_progress'     => false,
            'email_on_completed'       => false,
            // FMO Admin flags
            'email_on_new_request'     => false,
            'email_on_resubmitted'     => false,
            'email_on_po_assigned'     => false,
            // PO Admin flags
            'email_on_ready_to_assign' => false,
            // PO User flags
            'email_on_assigned_to_me'  => false,
        ];
    }

    public function shouldEmailNotify(string $eventKey): bool
    {
        $pref = $this->getSetting('email_notifications', 'key_only');

        if ($pref === 'none') return false;
        if ($pref === 'all') return true;

        $keyOnly = match($this->role) {
            'fmo_user'  => ['approved', 'rejected', 'clarification', 'completed'],
            'fmo_admin' => ['new_request', 'resubmitted', 'completed'],
            'po_admin'  => ['ready_to_assign', 'completed'],
            'po_user'   => ['assigned_to_me'],
            default     => [],
        };

        if ($pref === 'key_only') return in_array($eventKey, $keyOnly);

        return (bool) $this->getSetting("email_on_{$eventKey}", false);
    }

    /**
     * Get a specific setting with fallback to default
     */
    public function getSetting(string $key, $default = null)
    {
        $defaults = self::defaultSettings();
        $settings = $this->settings ?? [];

        return $settings[$key] ?? $defaults[$key] ?? $default;
    }

    /**
     * Get all settings merged with defaults
     */
    public function getAllSettings(): array
    {
        return array_merge(self::defaultSettings(), $this->settings ?? []);
    }

    /**
     * Update a specific setting
     */
    public function updateSetting(string $key, $value): void
    {
        $settings = $this->settings ?? [];
        $settings[$key] = $value;
        $this->settings = $settings;
        $this->save();
    }

    public function createdRequests()
    {
        return $this->hasMany(RequirementRequest::class, 'created_by');
    }

    public function approvedRequests()
    {
        return $this->hasMany(RequirementRequest::class, 'approved_by');
    }

    public function assignedRequests()
    {
        return $this->hasMany(RequirementRequest::class, 'assigned_to');
    }

    public function userGroups(): BelongsToMany
    {
        return $this->belongsToMany(UserGroup::class, 'user_group_members')->withTimestamps();
    }

    /**
     * Returns IDs of all users in the same group(s), including self.
     * Falls back to [self] if user is not in any group.
     */
    public function getGroupMemberIds(): array
    {
        $groupIds = $this->userGroups()->pluck('user_groups.id');

        if ($groupIds->isEmpty()) {
            return [$this->id];
        }

        return UserGroup::whereIn('id', $groupIds)
            ->with('members')
            ->get()
            ->flatMap(fn($g) => $g->members->pluck('id'))
            ->push($this->id)
            ->unique()
            ->values()
            ->all();
    }

    public function isFmoUser(): bool
    {
        return $this->role === 'fmo_user';
    }

    public function isFmoAdmin(): bool
    {
        return $this->role === 'fmo_admin';
    }

    public function isPoAdmin(): bool
    {
        return $this->role === 'po_admin';
    }

    public function isPoUser(): bool
    {
        return $this->role === 'po_user';
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if user can be deactivated based on their role and active tasks
     * Returns array: [bool canDeactivate, string|null reason]
     */
    public function canBeDeactivated(): array
    {
        // FMO User: Cannot deactivate if they have tasks that are not in terminal states (rejected or completed)
        if ($this->isFmoUser()) {
            $activeTaskCount = RequirementRequest::where('created_by', $this->id)
                ->whereNotIn('status', ['rejected', 'completed'])
                ->count();

            if ($activeTaskCount > 0) {
                return [false, "User has {$activeTaskCount} active task(s) initiated. Cannot deactivate until all are rejected or completed."];
            }
        }

        // FMO Admin: No constraints
        if ($this->isFmoAdmin()) {
            return [true, null];
        }

        // PO User: Cannot deactivate if they have assigned tasks in active states
        if ($this->isPoUser()) {
            $activeTaskCount = RequirementRequest::where('assigned_to', $this->id)
                ->whereNotIn('status', ['completed', 'rejected', 'cancelled'])
                ->count();

            if ($activeTaskCount > 0) {
                return [false, "User has {$activeTaskCount} active task(s) assigned. Cannot deactivate until all are completed or rejected."];
            }
        }

        // PO Admin: Cannot deactivate if they have assigned tasks in active states (either assigned to them or assigned by them)
        if ($this->isPoAdmin()) {
            // Check tasks assigned to them
            $assignedToCount = RequirementRequest::where('assigned_to', $this->id)
                ->whereNotIn('status', ['completed', 'rejected', 'cancelled'])
                ->count();

            if ($assignedToCount > 0) {
                return [false, "User has {$assignedToCount} active task(s) assigned to them. Cannot deactivate until all are completed or rejected."];
            }

            // Check tasks they've assigned
            $assignedByCount = RequirementRequest::where('assigned_by', $this->id)
                ->whereNotIn('status', ['completed', 'rejected', 'cancelled'])
                ->count();

            if ($assignedByCount > 0) {
                return [false, "User has {$assignedByCount} active task(s) assigned by them. Cannot deactivate until all are completed or rejected."];
            }
        }

        return [true, null];
    }
}
