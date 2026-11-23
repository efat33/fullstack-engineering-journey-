<?php

final class UserRegistrationService
{
    public function __construct(
        private UserRepository $repository,
        private NotificationService $notification,
        private Logger $logger
    ) {}

    public function register(
        string $name,
        string $email
    ): bool {
        $user = new User($name, $email);

        try {
            $this->repository->save($user);
        } catch (RuntimeException $exception) {
            $this->logger->log(
                'User registration failed: ' . $exception->getMessage()
            );

            return false;
        }

        $this->notification->sendWelcomeMessage($user);

        $this->logger->log(
            "User registered successfully: {$email}"
        );

        return true;
    }

    public function exportUsers(ReportExporter $exporter): string
    {
        $users = $this->repository->all();

        return $exporter->export($users);
    }
}
