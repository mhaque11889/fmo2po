<?php

namespace App\Events;

use App\Models\RequirementRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RequestClarificationRequested
{
    use Dispatchable, SerializesModels;

    public function __construct(public RequirementRequest $request) {}
}
