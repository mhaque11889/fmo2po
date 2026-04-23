<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequirementRequestItem extends Model
{
    protected $fillable = [
        'requirement_request_id',
        'item',
        'qty',
        'specifications',
        'sort_order',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(RequirementRequest::class, 'requirement_request_id');
    }
}
