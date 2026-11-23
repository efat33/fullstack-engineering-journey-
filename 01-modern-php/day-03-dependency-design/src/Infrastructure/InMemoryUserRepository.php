<?php

final class InMemoryUserRepository implements UserRepository
{
    /** @var list<User> */
    private array $users = [];

    public function save(User $user): void
    {
        $this->users[] = $user;
    }

    /** @return list<User> */
    public function all(): array
    {
        return $this->users;
    }
}
