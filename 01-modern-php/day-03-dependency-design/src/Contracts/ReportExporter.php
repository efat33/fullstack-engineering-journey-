<?php

interface ReportExporter
{
    /** @param list<User> $users */
    public function export(array $users): string;
}
