<?php

$container = require_once __DIR__ . '/../bootstrap.php';

use App\Application\UseCase\User\CreateUserUseCase;
use App\Application\UseCase\User\CreateUserRequest;
use App\Domain\ValueObject\UserId;
use Symfony\Entity\DoctrineUser;

// Create use case from container
$useCase = $container->get('App\Application\UseCase\User\CreateUserUseCase');
$entityManager = $container->get('doctrine.orm.entity_manager');

// Handle form submission
$result = null;
$error = null;

if ($_POST['action'] ?? '' === 'create_user') {
    try {
        $request = new CreateUserRequest(
            $_POST['name'] ?? '',
            $_POST['email'] ?? ''
        );
        $result = $useCase->execute($request);
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get all users for display
$users = [];
try {
    $doctrineUsers = $entityManager->getRepository(DoctrineUser::class)->findAll();
    foreach ($doctrineUsers as $doctrineUser) {
        $users[] = [
            'id' => $doctrineUser->getId(),
            'name' => $doctrineUser->getName(),
            'email' => $doctrineUser->getEmail(),
            'posts_count' => $doctrineUser->getPosts()->count(),
            'canCreatePost' => $doctrineUser->getPosts()->count() < 5
        ];
    }
} catch (Exception $e) {
    // Handle database errors
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Symfony Clean Architecture Demo</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 900px;
            margin: 2rem auto;
            padding: 0 1rem;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 2rem;
            padding: 2rem;
            background: linear-gradient(135deg, #000000 0%, #434343 100%);
            color: white;
            border-radius: 10px;
        }
        .demo-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            border-left: 4px solid #000000;
        }
        .framework-badge {
            display: inline-block;
            background: #000000;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        input[type="text"], input[type="email"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background: #000000;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }
        button:hover {
            background: #434343;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 4px;
            margin: 1rem 0;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 4px;
            margin: 1rem 0;
            border: 1px solid #f5c6cb;
        }
        .users-list {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        .user-card {
            background: #f8f9fa;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 6px;
            border-left: 3px solid #000000;
        }
        .user-card:last-child {
            margin-bottom: 0;
        }
        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            background: #28a745;
            color: white;
            border-radius: 12px;
            font-size: 0.8rem;
        }
        .badge.inactive {
            background: #6c757d;
        }
        .architecture-note {
            background: #e7f3ff;
            padding: 1rem;
            border-radius: 6px;
            border-left: 3px solid #000000;
            margin-top: 1rem;
        }
        .tech-stack {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        .tech-item {
            background: #e9ecef;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
        }
        .migration-success {
            background: #d1ecf1;
            color: #0c5460;
            padding: 1rem;
            border-radius: 6px;
            border-left: 3px solid #bee5eb;
            margin: 1rem 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Symfony Clean Architecture Demo</h1>
        <p>PHPカンファレンス2025 - Laravel → Symfony 移行完了！</p>
        <span class="framework-badge">Symfony + Doctrine + SQLite</span>
    </div>

    <div class="migration-success">
        <h3>フレームワーク移行成功!</h3>
        <p><strong>同じDomain層・Application層のコード</strong>がLaravelからSymfonyへ移行完了</p>
        <ul>
            <li><strong>User Entity</strong>: 変更なし</li>
            <li><strong>Value Objects</strong>: 変更なし</li>
            <li><strong>CreateUserUseCase</strong>: 変更なし</li>
            <li><strong>Repository Interface</strong>: 変更なし</li>
            <li><strong>Repository実装</strong>: Eloquent → Doctrine に置換</li>
            <li><strong>DI設定</strong>: Laravel Container → Symfony DI に置換</li>
        </ul>
    </div>

    <div class="demo-section">
        <h2>Symfony実装でのユーザー作成</h2>
        <p>スライドのUse Case 1移行後：Symfonyでもまったく同じビジネスロジックが動作</p>
        
        <div class="tech-stack">
            <span class="tech-item">Symfony Framework</span>
            <span class="tech-item">Doctrine ORM</span>
            <span class="tech-item">SQLite Database</span>
            <span class="tech-item">Repository Pattern</span>
            <span class="tech-item">Clean Architecture</span>
        </div>
        
        <form method="POST" style="margin-top: 1rem;">
            <input type="hidden" name="action" value="create_user">
            
            <div class="form-group">
                <label for="name">名前（2文字以上）:</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="email">メールアドレス:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <button type="submit">Symfonyでユーザーを作成</button>
        </form>

        <?php if ($result): ?>
        <div class="success">
            <strong>Symfony実装でユーザー作成成功!</strong><br>
            ID: <?= htmlspecialchars($result->id) ?><br>
            名前: <?= htmlspecialchars($result->name) ?><br>
            メール: <?= htmlspecialchars($result->email) ?><br>
            投稿可能: <?= $result->canCreateNewPost ? '可能' : '不可能' ?>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="error">
            <strong>エラー:</strong><br>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="demo-section">
        <h2>Symfony + Doctrineで管理されたユーザー</h2>
        
        <?php if (empty($users)): ?>
            <p>まだユーザーが作成されていません。上のフォームから作成してください。</p>
        <?php else: ?>
            <div class="users-list">
                <?php foreach ($users as $user): ?>
                <div class="user-card">
                    <strong>ID: <?= $user['id'] ?></strong> - <?= htmlspecialchars($user['name']) ?><br>
                    <?= htmlspecialchars($user['email']) ?><br>
                    投稿数: <?= $user['posts_count'] ?>/5<br>
                    <span class="badge <?= $user['canCreatePost'] ? '' : 'inactive' ?>">
                        投稿<?= $user['canCreatePost'] ? '可能' : '不可能' ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="architecture-note">
        <h3>Symfony移行のポイント</h3>
        <ul>
            <li><strong>Domain層</strong>: まったく変更なし（Pure PHP）</li>
            <li><strong>Application層</strong>: UseCase完全に同じコード</li>
            <li><strong>Infrastructure層</strong>: Doctrine Entity + SymfonyUserRepository</li>
            <li><strong>DI設定</strong>: services.yaml でインターフェース解決</li>
            <li><strong>移行工数</strong>: Repository実装とDI設定のみ</li>
        </ul>
        <p><strong>Clean Architectureの効果</strong>: ビジネスロジックを技術変更から完全に保護</p>
        
        <h4>移行で変更されたファイル</h4>
        <ul>
            <li>Repository実装: LaravelUserRepository → SymfonyUserRepository</li>
            <li>DI設定: Laravel Container → Symfony services.yaml</li>
            <li>ORM Entity: Eloquent Model → Doctrine Entity</li>
        </ul>
        
        <h4>移行で変更されなかったファイル</h4>
        <ul>
            <li>User Entity（ビジネスルール）</li>
            <li>Value Objects（バリデーション）</li>
            <li>CreateUserUseCase（処理フロー）</li>
            <li>Repository Interface（契約）</li>
        </ul>
    </div>
</body>
</html>