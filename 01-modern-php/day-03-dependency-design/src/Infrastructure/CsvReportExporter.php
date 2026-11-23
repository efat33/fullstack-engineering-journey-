<?php

final class CsvReportExporter implements ReportExporter
{
    public function export(array $users): string
    {
        $output = fopen('php://temp', 'r+');

        fputcsv($output, ['Name', 'Email']);

        foreach ($users as $user) {
            fputcsv($output, [
                $user->name,
                $user->email
            ]);
        }

        rewind($output);

        $csv = stream_get_contents($output);

        fclose($output);

        return $csv;
    }
}
