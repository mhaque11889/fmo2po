<?php

namespace App\Listeners;

use App\Events\RequestApproved;
use App\Events\RequestAssigned;
use App\Events\RequestClarificationRequested;
use App\Events\RequestCompleted;
use App\Events\RequestMarkedInProgress;
use App\Events\RequestRejected;
use App\Events\RequestResubmitted;
use App\Events\RequestSubmitted;
use App\Models\User;
use App\Notifications\RequestStatusNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendRequestEmailNotification implements ShouldQueue
{
    public string $queue = 'emails';

    public function handle(object $event): void
    {
        $request = $event->request->load(['creator', 'assignee', 'approver']);
        $recipients = $this->resolveRecipients($event, $request);

        foreach ($recipients as [$user, $eventKey]) {
            if ($user && $user->email && $user->shouldEmailNotify($eventKey)) {
                $user->notify(new RequestStatusNotification($request, $eventKey));
            }
        }
    }

    private function resolveRecipients(object $event, $request): array
    {
        $creator  = $request->creator;
        $assignee = $request->assignee;
        $fmoAdmins = User::where('role', 'fmo_admin')->where('is_active', true)->whereNotNull('email')->get();
        $poAdmins  = User::where('role', 'po_admin')->where('is_active', true)->whereNotNull('email')->get();

        return match(true) {
            $event instanceof RequestSubmitted            => $this->forAll($fmoAdmins, 'new_request'),
            $event instanceof RequestApproved             => array_merge(
                                                                [[$creator, 'approved']],
                                                                $this->forAll($poAdmins, 'ready_to_assign')
                                                            ),
            $event instanceof RequestRejected             => [[$creator, 'rejected']],
            $event instanceof RequestClarificationRequested => [[$creator, 'clarification']],
            $event instanceof RequestResubmitted          => $this->forAll($fmoAdmins, 'resubmitted'),
            $event instanceof RequestAssigned             => [[$creator, 'assigned'], [$assignee, 'assigned_to_me']],
            $event instanceof RequestMarkedInProgress     => [[$creator, 'in_progress']],
            $event instanceof RequestCompleted            => array_merge(
                                                                [[$creator, 'completed']],
                                                                $this->forAll($fmoAdmins, 'completed'),
                                                                $this->forAll($poAdmins, 'completed')
                                                            ),
            default => [],
        };
    }

    /** Convert a collection of users to [[user, eventKey], ...] pairs */
    private function forAll($users, string $eventKey): array
    {
        return $users->map(fn($u) => [$u, $eventKey])->toArray();
    }
}
