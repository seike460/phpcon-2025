<?php

namespace App\Domain\ValueObject;

class UserId 
{
    private int $value;

    public function __construct(int $id) 
    {
        if ($id <= 0) {
            throw new \InvalidArgumentException('User ID must be positive integer');
        }
        $this->value = $id;
    }

    public function getValue(): int 
    {
        return $this->value;
    }

    public function equals(UserId $other): bool 
    {
        return $this->value === $other->value;
    }
}