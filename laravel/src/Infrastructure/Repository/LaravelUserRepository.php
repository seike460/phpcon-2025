<?php

namespace Laravel\Infrastructure\Repository;

use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Entity\User;
use App\Domain\ValueObject\UserId;
use App\Domain\ValueObject\UserName;
use App\Domain\ValueObject\Email;
use Laravel\Models\EloquentUser;

class LaravelUserRepository implements UserRepositoryInterface 
{
    public function findById(UserId $id): ?User 
    {
        $eloquentUser = EloquentUser::with('posts')->find($id->getValue());
        
        if (!$eloquentUser) {
            return null;
        }
        
        return new User(
            new UserId($eloquentUser->id),
            new UserName($eloquentUser->name),
            new Email($eloquentUser->email)
        );
    }

    public function save(User $user): void 
    {
        $eloquentUser = EloquentUser::find($user->getId()->getValue());
        
        if ($eloquentUser) {
            $eloquentUser->update([
                'name' => $user->getName()->getValue(),
                'email' => $user->getEmail()->getValue()
            ]);
        } else {
            EloquentUser::create([
                'id' => $user->getId()->getValue(),
                'name' => $user->getName()->getValue(),
                'email' => $user->getEmail()->getValue()
            ]);
        }
    }

    public function nextId(): UserId 
    {
        $maxId = EloquentUser::max('id') ?? 0;
        return new UserId($maxId + 1);
    }
}