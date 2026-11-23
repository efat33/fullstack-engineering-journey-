<?php

require_once __DIR__ . '/../src/Contracts/UserRepository.php';
require_once __DIR__ . '/../src/Contracts/NotificationService.php';
require_once __DIR__ . '/../src/Contracts/Logger.php';
require_once __DIR__ . '/../src/Contracts/ReportExporter.php';

require_once __DIR__ . '/../src/Models/User.php';
require_once __DIR__ . '/../src/Services/UserRegistrationService.php';

final class FakeUserRepository implements UserRepository
{
    public array $users = [];

    public function __construct(
        private bool $shouldFail = false
    ) {}

    public function save(User $user): void
    {
        if ($this->shouldFail) {
            throw new RuntimeException('Database unavailable.');
        }

        $this->users[] = $user;
    }

    public function all(): array
    {
        return $this->users;
    }
}

final class FakeNotificationService implements NotificationService
{
    public array $notifications = [];

    public function sendWelcomeMessage(User $user): void
    {
        $this->notifications[] = $user->email;
    }
}

final class FakeLogger implements Logger
{
    public array $messages = [];

    public function log(string $message): void
    {
        $this->messages[] = $message;
    }
}

function assertSameValue(
    mixed $expected,
    mixed $actual,
    string $message
): void {
    if ($expected !== $actual) {
        throw new RuntimeException($message);
    }

    echo "PASS: {$message}\n";
}

// Test 1: Successful registration.

$repository = new FakeUserRepository();
$notification = new FakeNotificationService();
$logger = new FakeLogger();

$service = new UserRegistrationService(
    $repository,
    $notification,
    $logger
);

$result = $service->register('John', 'john@example.com');

assertSameValue(true, $result, 'Registration succeeds.');
assertSameValue(1, count($repository->users), 'User is saved.');
assertSameValue(1, count($notification->notifications), 'Welcome notification is sent.');
assertSameValue(1, count($logger->messages), 'Registration is logged.');

// Test 2: Failed database save.

$repository = new FakeUserRepository(shouldFail: true);
$notification = new FakeNotificationService();
$logger = new FakeLogger();

$service = new UserRegistrationService(
    $repository,
    $notification,
    $logger
);

$result = $service->register('Jane', 'jane@example.com');

assertSameValue(false, $result, 'Registration fails when saving fails.');
assertSameValue(0, count($repository->users), 'No user is saved.');
assertSameValue(0, count($notification->notifications), 'No welcome notification is sent.');
assertSameValue(1, count($logger->messages), 'Failure is logged.');

echo "\nAll tests completed successfully.\n";
