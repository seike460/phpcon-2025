<?php

namespace App\Domain\Entity;

use App\Domain\ValueObject\UserId;
use App\Domain\ValueObject\UserName;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\Posts;

class User 
{
    private UserId $id;
    private UserName $name;
    private Email $email;
    private Posts $posts;

    public function __construct(UserId $id, UserName $name, Email $email) 
    {
        $this->id = $id;        
        $this->name = $name;    
        $this->email = $email;  
        $this->posts = new Posts([]);
    }

    public function canCreateNewPost(): bool 
    {
        return $this->posts->count() < 5;
    }

    public function getId(): UserId 
    {
        return $this->id;
    }

    public function getName(): UserName 
    {
        return $this->name;
    }

    public function getEmail(): Email 
    {
        return $this->email;
    }

    public function getPosts(): Posts 
    {
        return $this->posts;
    }
}