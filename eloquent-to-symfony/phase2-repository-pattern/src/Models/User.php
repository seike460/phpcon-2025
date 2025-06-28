<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Domain\Entity\User as DomainUser;

/**
 * Phase 1: Domain Entityを導入しつつEloquentも残存
 * 
 * 変更点:
 * - Domain Entityに変換するメソッドを追加
 * - ビジネスロジックは Domain Entity に委譲開始
 * - 段階的移行のためEloquent機能は残存
 */
class User extends Model 
{
    protected $fillable = ['name', 'email'];
    public $timestamps = false;
    
    // リレーション定義（まだEloquent依存）
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
    
    // Phase 1: Domain Entityへの変換メソッド追加
    public function toDomainEntity(): DomainUser
    {
        $posts = $this->posts->map(function($post) {
            return [
                'id' => $post->id,
                'title' => $post->title,
                'content' => $post->content,
                'created_at' => $post->created_at
            ];
        })->toArray();
        
        return new DomainUser(
            $this->id,
            $this->name,
            $this->email,
            $posts
        );
    }
    
    // ビジネスロジック: Domain Entity に委譲開始
    public function canCreateNewPost(): bool 
    {
        // 段階的移行: 一部をDomain Entityに委譲
        return $this->toDomainEntity()->canCreateNewPost();
    }
    
    // バリデーションも Model 内（まだLaravel依存）
    public static function createWithValidation(array $data): self
    {
        $validator = Validator::make($data, [
            'email' => 'required|email|unique:users',
            'name' => 'required|min:2'
        ]);
        
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        
        return self::create($data);
    }
    
    // ビジネスロジック: Domain Entity に委譲
    public function getDisplayName(): string
    {
        return $this->toDomainEntity()->getDisplayName();
    }
    
    // まだEloquent依存（後のPhaseで修正予定）
    public function getRecentPosts(int $limit = 3)
    {
        return $this->posts()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}