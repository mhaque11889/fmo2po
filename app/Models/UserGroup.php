<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserGroup extends Model
{
    protected $fillable = ['name', 'description', 'type', 'created_by', 'group_approver_id'];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function groupApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'group_approver_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_group_members')->withTimestamps();
    }

    public function categoryApprovers(): HasMany
    {
        return $this->hasMany(UserGroupCategoryApprover::class);
    }

    // Returns the effective approver ID for a category, falling back to group_approver_id.
    // Returns null if neither is set (request skips group approval).
    public function getApproverForCategory(int $categoryId): ?int
    {
        $override = $this->categoryApprovers->firstWhere('category_id', $categoryId);
        return $override ? $override->approver_id : $this->group_approver_id;
    }

    public function hasPreApproval(): bool
    {
        return !is_null($this->group_approver_id);
    }
}
