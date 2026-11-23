<?php

final class ConsoleNotificationService implements NotificationService
{
    public function sendWelcomeMessage(User $user): void
    {
        echo "Welcome email sent to: {$user->email}\n";
    }
}
