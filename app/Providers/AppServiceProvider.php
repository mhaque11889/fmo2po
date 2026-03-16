<?php

namespace App\Providers;

use App\Events\RequestApproved;
use App\Events\RequestAssigned;
use App\Events\RequestClarificationRequested;
use App\Events\RequestCompleted;
use App\Events\RequestMarkedInProgress;
use App\Events\RequestRejected;
use App\Events\RequestResubmitted;
use App\Events\RequestSubmitted;
use App\Listeners\SendRequestEmailNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $events = [
            RequestSubmitted::class,
            RequestApproved::class,
            RequestRejected::class,
            RequestClarificationRequested::class,
            RequestResubmitted::class,
            RequestAssigned::class,
            RequestMarkedInProgress::class,
            RequestCompleted::class,
        ];

        foreach ($events as $event) {
            Event::listen($event, SendRequestEmailNotification::class);
        }
    }
}
