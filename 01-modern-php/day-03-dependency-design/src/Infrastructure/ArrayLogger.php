<?php

final class ArrayLogger implements Logger
{
    /** @var list<string> */
    private array $messages = [];

    public function log(string $message): void
    {
        $this->messages[] = $message;

        echo "[LOG] {$message}\n";
    }

    /** @return list<string> */
    public function all(): array
    {
        return $this->messages;
    }
}
