<?php

namespace Symfony\Controller;

use App\Application\UseCase\User\CreateUserUseCase;
use App\Application\UseCase\User\CreateUserRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    public function __construct(
        private CreateUserUseCase $createUserUseCase
    ) {}

    #[Route('/api/users', name: 'create_user', methods: ['POST'])]
    public function store(Request $request): JsonResponse 
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            $createRequest = new CreateUserRequest(
                $data['name'] ?? '',
                $data['email'] ?? ''
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