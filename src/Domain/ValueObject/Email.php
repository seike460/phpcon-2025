<?php

namespace App\Domain\ValueObject;

class Email 
{
    private string $value;

    public function __construct(string $email) 
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format');
        }
        $this->value = $email;
    }

    public function getValue(): string 
    {
        return $this->value;
    }

    public function equals(Email $other): bool 
    {
        return $this->value === $other->value;
    }
}