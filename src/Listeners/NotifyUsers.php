<?php

namespace Spinzar\Firewall\Listeners;

use Spinzar\Firewall\Events\AttackDetected as Event;
use Spinzar\Firewall\Notifications\AttackDetected as Notification;

class NotifyUsers
{
    /**
     * Handle the event.
     *
     * @param Event $event
     *
     * @return void
     */
    public function handle(Event $event)
    {
        $model = config('firewall.models.user');

        if (!class_exists($model)) {
            return;
        }

        $emails = config('firewall.notifications.mail.to');

        foreach ($emails as $email) {
            $user = $model::where('email', $email)->first();

            if (empty($user)) {
                continue;
            }

            $user->notify(new Notification($event->log));
        }
    }
}
