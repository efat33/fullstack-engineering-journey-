<?php

interface NotificationService
{
    public function sendWelcomeMessage(User $user): void;
}
