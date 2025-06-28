<?php

namespace App\Domain\Repository;

use App\Domain\Entity\User;
use App\Domain\ValueObject\UserId;

interface UserRepositoryInterface 
{
    public function findById(UserId $id): ?User;
    public function save(User $user): void;
    public function nextId(): UserId;
}