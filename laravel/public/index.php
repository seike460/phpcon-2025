<?php

$container = require_once '../bootstrap.php';

use App\Application\UseCase\User\CreateUserUseCase;
use App\Application\UseCase\User\CreateUserRequest;
use App\Domain\ValueObject\UserId;
use Laravel\Infrastructure\Repository\LaravelUserRepository;

// Create use case from container
$useCase = $container->make(CreateUserUseCase::class);
$repository = $container->make(\App\Domain\Repository\UserRepositoryInterface::class);

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
    // Get users from database using Eloquent
    $eloquentUsers = \Laravel\Models\EloquentUser::with('posts')->get();
    foreach ($eloquentUsers as $eloquentUser) {
        $users[] = [
            'id' => $eloquentUser->id,
            'name' => $eloquentUser->name,
            'email' => $eloquentUser->email,
            'posts_count' => $eloquentUser->posts->count(),
            'canCreatePost' => $eloquentUser->posts->count() < 5
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
    <title>Laravel Clean Architecture Demo</title>
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
            background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
            color: white;
            border-radius: 10px;
        }
        .demo-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            border-left: 4px solid #f43f5e;
        }
        .framework-badge {
            display: inline-block;
            background: #f43f5e;
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
            background: #f43f5e;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }
        button:hover {
            background: #e11d48;
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
            border-left: 3px solid #f43f5e;
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
            background: #fff3cd;
            padding: 1rem;
            border-radius: 6px;
            border-left: 3px solid #f43f5e;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>Laravel Clean Architecture Demo</h1>
        <p>PHPカンファレンス2025 - Use Case 1: 純粋なドメインロジック</p>
        <span class="framework-badge">Laravel + SQLite + Eloquent</span>
    </div>

    <div class="demo-section">
        <h2>Laravel実装でのユーザー作成</h2>
        <p>スライドのUse Case 1に対応：Repository Interfaceを使用したLaravel実装</p>
        
        <div class="tech-stack">
            <span class="tech-item">Laravel Eloquent</span>
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
            
            <button type="submit">Laravelでユーザーを作成</button>
        </form>

        <?php if ($result): ?>
        <div class="success">
            <strong>Laravel実装でユーザー作成成功!</strong><br>
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
        <h2>Laravel + SQLiteで管理されたユーザー</h2>
        
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
                        投稿<?= $user['canCreatePost'] ? '可能' : '上限達成' ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="architecture-note">
        <h3>Laravel実装のポイント</h3>
        <ul>
            <li><strong>Repository Interface</strong>: Domain層で定義、Infrastructure層で実装</li>
            <li><strong>Eloquent Model</strong>: LaravelUserRepositoryでEloquentUserを使用</li>
            <li><strong>DI Container</strong>: Laravel Container でインターフェースを実装に紐付け</li>
            <li><strong>データ変換</strong>: Eloquent Model → Domain Entity の変換</li>
            <li><strong>SQLite使用</strong>: 軽量データベースでの実装</li>
        </ul>
        <p><strong>次のステップ</strong>: この実装をSymfonyに移行してClean Architectureの効果を実証</p>
    </div>
</body>
</html>