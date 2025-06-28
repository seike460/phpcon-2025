<?php

namespace App\Http\Controllers;

use App\Domain\Repository\UserRepositoryInterface;
use App\Application\Service\UserValidationService;
use App\Domain\Entity\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * Phase 2: Repository Pattern導入でController修正
 * 
 * 変更点:
 * - Eloquent Modelの直接使用を中止
 * - Repository Interface経由でデータアクセス
 * - ValidationServiceでバリデーション分離
 * - Domain Entityを使用したロジック実行
 */
class UserController 
{
    private UserRepositoryInterface $userRepository;
    private UserValidationService $validationService;
    
    public function __construct(
        UserRepositoryInterface $userRepository,
        UserValidationService $validationService
    ) {
        $this->userRepository = $userRepository;
        $this->validationService = $validationService;
    }
    
    public function store(Request $request): JsonResponse 
    {
        try {
            $data = $request->all();
            
            // Phase 2: バリデーションサービス経由
            $this->validationService->validateForCreation($data);
            
            // Phase 2: Domain Entity作成
            $user = new User(
                0, // Repository で ID を生成
                $data['name'],
                $data['email']
            );
            
            // Phase 2: Repository経由で永続化
            $savedUser = $this->userRepository->save($user);
            
            return response()->json([
                'id' => $savedUser->getId(),
                'name' => $savedUser->getName(),
                'email' => $savedUser->getEmail(),
                'display_name' => $savedUser->getDisplayName(),      // Domain Entity のメソッド
                'can_create_post' => $savedUser->canCreateNewPost(), // Domain Entity のメソッド
                'recent_posts' => $savedUser->getRecentPosts(2)
            ], 201);
            
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->validator->errors()
            ], 422);
        }
    }
    
    public function index(): JsonResponse
    {
        // Phase 2: Repository経由で全ユーザー取得
        $users = $this->userRepository->findAll();
        
        $userData = array_map(function ($user) {
            return [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'display_name' => $user->getDisplayName(),
                'posts_count' => count($user->getPosts()),
                'can_create_post' => $user->canCreateNewPost(),
                'recent_posts' => $user->getRecentPosts(2)
            ];
        }, $users);
        
        return response()->json($userData);
    }
}