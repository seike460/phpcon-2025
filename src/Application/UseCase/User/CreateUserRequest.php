<?php

namespace App\Application\UseCase\User;

class CreateUserRequest 
{
    public function __construct(
        public readonly string $name,
        public readonly string $email
    ) {}
}