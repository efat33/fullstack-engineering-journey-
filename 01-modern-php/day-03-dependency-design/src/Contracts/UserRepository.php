<?php

interface UserRepository
{
    public function save(User $user): void;

    /** @return list<User> */
    public function all(): array;
}