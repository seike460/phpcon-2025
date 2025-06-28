<?php

namespace App\Domain\ValueObject;

class Posts 
{
    private array $posts;

    public function __construct(array $posts = []) 
    {
        $this->posts = $posts;
    }

    public function count(): int 
    {
        return count($this->posts);
    }

    public function add(array $post): void 
    {
        $this->posts[] = $post;
    }

    public function toArray(): array 
    {
        return $this->posts;
    }
}