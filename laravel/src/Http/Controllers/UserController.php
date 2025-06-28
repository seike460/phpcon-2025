<?php

namespace Laravel\Http\Controllers;

use App\Application\UseCase\User\CreateUserUseCase;
use App\Application\UseCase\User\CreateUserRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController 
{
    public function __construct(
        private CreateUserUseCase $createUserUseCase
    ) {}

    public function store(Request $request): JsonResponse 
    {
        try {
            $createRequest = new CreateUserRequest(
                $request->input('name'),
                $request->input('email')
            );
            
            $result = $this->createUserUseCase->execute($createRequest);
            
            return new JsonResponse([
                'success' => true,
                'data' => [
                    'id' => $result->id,
                    'name' => $result->name,
                    'email' => $result->email,
                    'can_create_post' => $result->canCreateNewPost
                ]
            ], 201);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 422);
        }
    }
}