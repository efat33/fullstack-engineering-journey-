<?php

final class User
{
    public function __construct(
        public readonly string $name,
        public readonly string $email
    ) {
        if (trim($name) === '') {
            throw new InvalidArgumentException(
                'User name cannot be empty.'
            );
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(
                'Invalid email address.'
            );
        }
    }
}
