<?php

namespace App\Application\UseCase\User;

use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Entity\User;
use App\Domain\ValueObject\UserName;
use App\Domain\ValueObject\Email;

class CreateUserUseCase 
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function execute(CreateUserRequest $request): UserDto 
    {
        $user = new User(
            $this->userRepository->nextId(),
            new UserName($request->name),      
            new Email($request->email)         
        );
        
        $this->userRepository->save($user);    
        
        return new UserDto(                    
            $user->getId()->getValue(),
            $user->getName()->getValue(),
            $user->getEmail()->getValue(),
            $user->canCreateNewPost()
        );
    }
}