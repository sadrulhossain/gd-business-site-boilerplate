<?php

namespace App\Notifications;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Emails the site owner about a new contact message (queued; replies go to the sender). */
class NewContactMessage extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ContactMessage $contactMessage) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $m = $this->contactMessage;

        return (new MailMessage)
            ->subject('New contact message'.($m->subject ? ': '.$m->subject : ''))
            ->replyTo($m->email, $m->name)
            ->line("From: {$m->name} <{$m->email}>".($m->phone ? " ({$m->phone})" : ''))
            ->line($m->message);
    }
}
