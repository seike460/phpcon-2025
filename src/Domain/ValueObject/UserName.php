<?php

namespace App\Domain\ValueObject;

class UserName 
{
    private string $value;

    public function __construct(string $name) 
    {
        if (empty(trim($name))) {
            throw new \InvalidArgumentException('User name cannot be empty');
        }
        if (strlen($name) < 2) {
            throw new \InvalidArgumentException('User name must be at least 2 characters');
        }
        $this->value = $name;
    }

    public function getValue(): string 
    {
        return $this->value;
    }

    public function equals(UserName $other): bool 
    {
        return $this->value === $other->value;
    }
}