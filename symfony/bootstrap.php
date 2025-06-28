<?php

require_once 'vendor/autoload.php';

use App\Application\UseCase\User\CreateUserUseCase;
use App\Domain\Repository\UserRepositoryInterface;
use Symfony\Infrastructure\Repository\SymfonyUserRepository;
use Symfony\Entity\DoctrineUser;
use Symfony\Entity\DoctrinePost;

// Simple SQLite setup
$dbPath = __DIR__ . '/var/symfony_demo.sqlite';
if (!file_exists(dirname($dbPath))) {
    mkdir(dirname($dbPath), 0755, true);
}

// Create SQLite database and tables if not exists
if (!file_exists($dbPath)) {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->exec('
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE
        );
        
        CREATE TABLE IF NOT EXISTS posts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            title VARCHAR(255) NOT NULL,
            content TEXT,
            FOREIGN KEY (user_id) REFERENCES users(id)
        );
    ');
}

// Simple container implementation
class SimpleContainer {
    private array $services = [];
    
    public function set(string $id, $service): void {
        $this->services[$id] = $service;
    }
    
    public function get(string $id) {
        return $this->services[$id] ?? null;
    }
}

$container = new SimpleContainer();

// Simple Entity Manager mock for basic operations
class SimpleEntityManager {
    private PDO $pdo;
    
    public function __construct(string $dbPath) {
        $this->pdo = new PDO('sqlite:' . $dbPath);
    }
    
    public function find(string $class, $id) {
        if ($class === DoctrineUser::class) {
            $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = ?');
            $stmt->execute([$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($data) {
                $user = new DoctrineUser();
                $user->setId($data['id']);
                $user->setName($data['name']);
                $user->setEmail($data['email']);
                return $user;
            }
        }
        return null;
    }
    
    public function persist($entity): void {
        if ($entity instanceof DoctrineUser) {
            $stmt = $this->pdo->prepare('INSERT OR REPLACE INTO users (id, name, email) VALUES (?, ?, ?)');
            $stmt->execute([$entity->getId(), $entity->getName(), $entity->getEmail()]);
        }
    }
    
    public function flush(): void {
        // Already persisted in persist method
    }
    
    public function createQuery(string $dql) {
        return new class($this->pdo) {
            private PDO $pdo;
            public function __construct(PDO $pdo) { $this->pdo = $pdo; }
            public function getSingleScalarResult() {
                $stmt = $this->pdo->query('SELECT MAX(id) FROM users');
                return $stmt->fetchColumn();
            }
        };
    }
    
    public function getRepository(string $class) {
        return new class($this->pdo, $class) {
            private PDO $pdo;
            private string $class;
            public function __construct(PDO $pdo, string $class) { 
                $this->pdo = $pdo; 
                $this->class = $class;
            }
            public function findAll() {
                $stmt = $this->pdo->query('SELECT * FROM users');
                $users = [];
                while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $user = new DoctrineUser();
                    $user->setId($data['id']);
                    $user->setName($data['name']);
                    $user->setEmail($data['email']);
                    $users[] = $user;
                }
                return $users;
            }
        };
    }
}

$entityManager = new SimpleEntityManager($dbPath);
$container->set('doctrine.orm.entity_manager', $entityManager);

// Setup services
$userRepository = new SymfonyUserRepository($entityManager);
$createUserUseCase = new CreateUserUseCase($userRepository);

$container->set('App\Domain\Repository\UserRepositoryInterface', $userRepository);
$container->set('App\Application\UseCase\User\CreateUserUseCase', $createUserUseCase);

return $container;