<?php

namespace App\Application\UseCase\User;

class UserDto 
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly bool $canCreateNewPost
    ) {}
}