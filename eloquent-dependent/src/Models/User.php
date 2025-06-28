<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class User extends Model 
{
    protected $fillable = ['name', 'email'];
    public $timestamps = false;
    
    // リレーション定義
    public function posts()
    {
        return $this->hasMany(Post::class);     // Eloquent リレーション
    }
    
    // ビジネスロジックが Model に混在
    public function canCreateNewPost(): bool 
    {
        return $this->posts()->count() < 5;     // Eloquent Query Builder 使用
    }
    
    // バリデーションも Model 内
    public static function createWithValidation(array $data): self
    {
        $validator = Validator::make($data, [    // Laravel Validator 使用
            'email' => 'required|email|unique:users',
            'name' => 'required|min:2'
        ]);
        
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        
        return self::create($data);             // Eloquent の create メソッド
    }
    
    // 追加のビジネスロジック
    public function getDisplayName(): string
    {
        return strtoupper($this->name);
    }
    
    // さらなる Eloquent 依存
    public function getRecentPosts(int $limit = 3)
    {
        return $this->posts()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}