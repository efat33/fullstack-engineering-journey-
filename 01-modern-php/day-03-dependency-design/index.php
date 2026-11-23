<?php

require_once __DIR__ . '/bootstrap.php';

// Register users.
$userRegistrationService->register(
    'John Doe',
    'john@example.com'
);

$userRegistrationService->register(
    'Jane Smith',
    'jane@example.com'
);

echo "\n--- Exporting Users ---\n";

// Create the exporter when the export operation is requested.
$csvExporter = new CsvReportExporter();

$csv = $userRegistrationService->exportUsers($csvExporter);

echo $csv;
