<?php

namespace Symfony\Infrastructure\Repository;

use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Entity\User;
use App\Domain\ValueObject\UserId;
use App\Domain\ValueObject\UserName;
use App\Domain\ValueObject\Email;
use Symfony\Entity\DoctrineUser;
use Doctrine\ORM\EntityManagerInterface;

class SymfonyUserRepository implements UserRepositoryInterface 
{
    private $entityManager;
    
    public function __construct($entityManager) {
        $this->entityManager = $entityManager;
    }

    public function findById(UserId $id): ?User 
    {
        $doctrineUser = $this->entityManager->find(DoctrineUser::class, $id->getValue());
        
        if (!$doctrineUser) {
            return null;
        }
        
        return new User(
            new UserId($doctrineUser->getId()),
            new UserName($doctrineUser->getName()),
            new Email($doctrineUser->getEmail())
        );
    }

    public function save(User $user): void 
    {
        $doctrineUser = $this->entityManager->find(DoctrineUser::class, $user->getId()->getValue());
        
        if ($doctrineUser) {
            $doctrineUser->setName($user->getName()->getValue());
            $doctrineUser->setEmail($user->getEmail()->getValue());
        } else {
            $doctrineUser = new DoctrineUser();
            $doctrineUser->setId($user->getId()->getValue());
            $doctrineUser->setName($user->getName()->getValue());
            $doctrineUser->setEmail($user->getEmail()->getValue());
        }
        
        $this->entityManager->persist($doctrineUser);
        $this->entityManager->flush();
    }

    public function nextId(): UserId 
    {
        $maxId = $this->entityManager->createQuery('SELECT MAX(u.id) FROM users u')->getSingleScalarResult();
        return new UserId(($maxId ?? 0) + 1);
    }
}