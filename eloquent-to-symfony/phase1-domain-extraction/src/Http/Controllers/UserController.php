<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class UserController 
{
    public function store(Request $request): JsonResponse 
    {
        try {
            // Model のスタティックメソッドを直接呼び出し
            $user = User::createWithValidation($request->all());
            
            return response()->json([
                'id' => $user->id,                    // Model の属性に直接アクセス
                'name' => $user->name,
                'email' => $user->email,
                'display_name' => $user->getDisplayName(),  // Model のメソッド呼び出し
                'can_create_post' => $user->canCreateNewPost(),  // Model のメソッド呼び出し
                'recent_posts' => $user->getRecentPosts()
            ], 201);
            
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->validator->errors()    // Laravel 固有の例外処理
            ], 422);
        }
    }
    
    public function index(): JsonResponse
    {
        // Eloquent Query Builder を直接使用
        $users = User::with('posts')
            ->select(['id', 'name', 'email'])
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'display_name' => $user->getDisplayName(),
                    'posts_count' => $user->posts->count(),
                    'can_create_post' => $user->canCreateNewPost(),
                    'recent_posts' => $user->getRecentPosts(2)
                ];
            });
            
        return response()->json($users);
    }
}