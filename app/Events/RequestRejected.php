<?php

namespace App\Events;

use App\Models\RequirementRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RequestRejected
{
    use Dispatchable, SerializesModels;

    public function __construct(public RequirementRequest $request) {}
}
