<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use App\Models\User as EloquentUser;

/**
 * Phase 2: Eloquent実装のRepository
 * 
 * Domain Repository InterfaceをEloquentで実装
 * EloquentモデルとDomain Entityの相互変換を担当
 */
class EloquentUserRepository implements UserRepositoryInterface
{
    public function findById(int $id): ?User
    {
        $eloquentUser = EloquentUser::with('posts')->find($id);
        
        if (!$eloquentUser) {
            return null;
        }
        
        return $this->toDomainEntity($eloquentUser);
    }
    
    public function save(User $user): User
    {
        $data = [
            'name' => $user->getName(),
            'email' => $user->getEmail(),
        ];
        
        if ($user->getId() === 0) {
            // 新規作成
            $eloquentUser = EloquentUser::create($data);
        } else {
            // 更新
            $eloquentUser = EloquentUser::find($user->getId());
            $eloquentUser->update($data);
        }
        
        return $this->toDomainEntity($eloquentUser);
    }
    
    public function findAll(): array
    {
        $eloquentUsers = EloquentUser::with('posts')->get();
        
        return $eloquentUsers->map(function($eloquentUser) {
            return $this->toDomainEntity($eloquentUser);
        })->toArray();
    }
    
    public function findByEmail(string $email): ?User
    {
        $eloquentUser = EloquentUser::with('posts')->where('email', $email)->first();
        
        if (!$eloquentUser) {
            return null;
        }
        
        return $this->toDomainEntity($eloquentUser);
    }
    
    public function nextId(): int
    {
        return EloquentUser::max('id') + 1;
    }
    
    /**
     * EloquentモデルからDomain Entityに変換
     */
    private function toDomainEntity(EloquentUser $eloquentUser): User
    {
        $posts = $eloquentUser->posts->map(function($post) {
            return [
                'id' => $post->id,
                'title' => $post->title,
                'content' => $post->content,
                'created_at' => $post->created_at
            ];
        })->toArray();
        
        return new User(
            $eloquentUser->id,
            $eloquentUser->name,
            $eloquentUser->email,
            $posts
        );
    }
}