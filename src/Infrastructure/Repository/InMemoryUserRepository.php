<?php

namespace App\Infrastructure\Repository;

use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Entity\User;
use App\Domain\ValueObject\UserId;

class InMemoryUserRepository implements UserRepositoryInterface 
{
    private array $users = [];
    private int $nextId = 1;

    public function findById(UserId $id): ?User 
    {
        return $this->users[$id->getValue()] ?? null;
    }

    public function save(User $user): void 
    {
        $this->users[$user->getId()->getValue()] = $user;
    }

    public function nextId(): UserId 
    {
        return new UserId($this->nextId++);
    }
}