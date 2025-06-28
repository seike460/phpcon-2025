<?php

namespace App\Application\Service;

use App\Domain\Repository\UserRepositoryInterface;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Phase 2: バリデーションサービス分離
 * 
 * Modelから分離されたバリデーション専用サービス
 * まだLaravel Validatorを使用しているが、後のPhaseで置換可能
 */
class UserValidationService
{
    private UserRepositoryInterface $userRepository;
    
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }
    
    /**
     * ユーザー作成データのバリデーション
     * 
     * @param array $data
     * @throws ValidationException
     */
    public function validateForCreation(array $data): void
    {
        $validator = Validator::make($data, [
            'email' => 'required|email',
            'name' => 'required|min:2'
        ]);
        
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        
        // カスタムバリデーション: メールアドレス重複チェック
        if ($this->userRepository->findByEmail($data['email'])) {
            $validator->errors()->add('email', 'このメールアドレスは既に使用されています');
            throw new ValidationException($validator);
        }
    }
    
    /**
     * ユーザー更新データのバリデーション
     * 
     * @param array $data
     * @param int $userId 更新対象のユーザーID
     * @throws ValidationException
     */
    public function validateForUpdate(array $data, int $userId): void
    {
        $validator = Validator::make($data, [
            'email' => 'required|email',
            'name' => 'required|min:2'
        ]);
        
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        
        // 自分以外の重複チェック
        $existingUser = $this->userRepository->findByEmail($data['email']);
        if ($existingUser && $existingUser->getId() !== $userId) {
            $validator->errors()->add('email', 'このメールアドレスは既に使用されています');
            throw new ValidationException($validator);
        }
    }
}