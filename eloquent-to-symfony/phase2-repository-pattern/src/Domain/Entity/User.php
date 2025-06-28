<?php

namespace App\Domain\Entity;

/**
 * Phase 1: EloquentモデルからDomain Entityを抽出
 * 
 * 変更点:
 * - ビジネスロジックをPure PHPクラスに移動
 * - Eloquent依存を除去
 * - フレームワーク非依存のEntityとして実装
 */
class User 
{
    private int $id;
    private string $name;
    private string $email;
    private array $posts;

    public function __construct(int $id, string $name, string $email, array $posts = []) 
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->posts = $posts;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPosts(): array
    {
        return $this->posts;
    }

    // ビジネスルール: Pure PHPで実装（Eloquent Query Builder依存を除去）
    public function canCreateNewPost(): bool 
    {
        return count($this->posts) < 5;  // Eloquentの posts()->count() から変更
    }
    
    // ビジネスロジック: フレームワーク非依存
    public function getDisplayName(): string
    {
        return strtoupper($this->name);
    }

    // データ整合性: Domain内で管理
    public function addPost(array $post): void
    {
        if (!$this->canCreateNewPost()) {
            throw new \DomainException('投稿数が上限に達しています');
        }
        
        $this->posts[] = $post;
    }

    public function getRecentPosts(int $limit = 3): array
    {
        // Eloquentの orderBy()->limit()->get() から変更
        $sortedPosts = $this->posts;
        usort($sortedPosts, function($a, $b) {
            return ($b['created_at'] ?? 0) <=> ($a['created_at'] ?? 0);
        });
        
        return array_slice($sortedPosts, 0, $limit);
    }
}