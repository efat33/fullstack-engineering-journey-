<?php

require_once __DIR__ . '/src/Contracts/UserRepository.php';
require_once __DIR__ . '/src/Contracts/NotificationService.php';
require_once __DIR__ . '/src/Contracts/Logger.php';
require_once __DIR__ . '/src/Contracts/ReportExporter.php';

require_once __DIR__ . '/src/Models/User.php';

require_once __DIR__ . '/src/Infrastructure/InMemoryUserRepository.php';
require_once __DIR__ . '/src/Infrastructure/ConsoleNotificationService.php';
require_once __DIR__ . '/src/Infrastructure/ArrayLogger.php';
require_once __DIR__ . '/src/Infrastructure/CsvReportExporter.php';

require_once __DIR__ . '/src/Services/UserRegistrationService.php';

// Create infrastructure dependencies.
$repository = new InMemoryUserRepository();
$notification = new ConsoleNotificationService();
$logger = new ArrayLogger();

// Create the application service.
$userRegistrationService = new UserRegistrationService(
    $repository,
    $notification,
    $logger
);
