<?php

namespace App\Notifications;

use App\Models\RequirementRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequestStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public RequirementRequest $request,
        public string $eventKey
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $item = $this->request->item ?? 'N/A';
        $id   = $this->request->id;
        $url  = route('requests.show', $id);

        [$subject, $headline, $detail] = match($this->eventKey) {
            'approved'        => [
                "Your request has been approved — #{$id}",
                "Your request \"{$item}\" has been approved by the FMO Admin and is now with the Purchase Office.",
                null,
            ],
            'rejected'        => [
                "Your request was not approved — #{$id}",
                "Your request \"{$item}\" has been rejected.",
                $this->request->completion_remarks,
            ],
            'clarification'   => [
                "Clarification needed on your request — #{$id}",
                "The FMO Admin has requested clarification on your request \"{$item}\".",
                $this->request->clarification_remarks,
            ],
            'assigned'        => [
                "Your request has been assigned — #{$id}",
                "Your request \"{$item}\" has been assigned to a Purchase Office team member.",
                null,
            ],
            'in_progress'     => [
                "Work has started on your request — #{$id}",
                "A Purchase Office team member has started working on your request \"{$item}\".",
                $this->request->progress_remarks,
            ],
            'completed'       => [
                "Your request has been completed — #{$id}",
                "Your request \"{$item}\" has been completed by the Purchase Office.",
                $this->request->completion_remarks,
            ],
            'new_request'     => [
                "New request submitted for review — #{$id}",
                "A new request \"{$item}\" has been submitted by {$this->request->creator?->name} and is awaiting your review.",
                $this->request->remarks,
            ],
            'resubmitted'     => [
                "A request has been resubmitted — #{$id}",
                "Request \"{$item}\" has been updated by {$this->request->creator?->name} after clarification and is ready for your review.",
                null,
            ],
            'ready_to_assign' => [
                "New request ready to assign — #{$id}",
                "A request \"{$item}\" has been approved by FMO Admin and is ready to be assigned to your team.",
                null,
            ],
            'assigned_to_me'  => [
                "A request has been assigned to you — #{$id}",
                "Request \"{$item}\" has been assigned to you by the Purchase Office Admin.",
                null,
            ],
            'po_assigned'     => [
                "Request assigned to a PO team member — #{$id}",
                "Request \"{$item}\" has been assigned to {$this->request->assignee?->name}.",
                null,
            ],
            default => [
                "Update on request #{$id}",
                "There has been an update on request \"{$item}\".",
                null,
            ],
        };

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting("Hello {$notifiable->name},")
            ->line($headline);

        if ($detail) {
            $mail->line("**Remarks:** {$detail}");
        }

        return $mail
            ->action('View Request', $url)
            ->line('You are receiving this email based on your notification preferences.')
            ->line('You can manage your preferences in [Settings](' . route('settings.index') . ').');
    }
}
