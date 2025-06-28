<?php

namespace App\Domain\Repository;

use App\Domain\Entity\User;

/**
 * Phase 2: Repository Pattern導入
 * 
 * データアクセスを抽象化し、Domain層がInfrastructure層の
 * 具体的な実装（EloquentやDoctrine）に依存しないようにする
 */
interface UserRepositoryInterface 
{
    /**
     * IDでユーザーを検索
     * 
     * @param int $id
     * @return User|null Domain Entity を返却
     */
    public function findById(int $id): ?User;
    
    /**
     * ユーザーを永続化
     * 
     * @param User $user Domain Entity を受け取り
     * @return User 保存されたユーザー（IDが設定される）
     */
    public function save(User $user): User;
    
    /**
     * 全ユーザーを取得
     * 
     * @return User[] Domain Entity の配列
     */
    public function findAll(): array;
    
    /**
     * メールアドレスでユーザーを検索
     * 
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User;
    
    /**
     * 新しいユーザーIDを生成
     * 
     * @return int
     */
    public function nextId(): int;
}