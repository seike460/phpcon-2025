<?php

require_once '../vendor/autoload.php';

use App\Application\UseCase\User\CreateUserUseCase;
use App\Application\UseCase\User\CreateUserRequest;
use App\Infrastructure\Repository\InMemoryUserRepository;
use App\Domain\ValueObject\UserId;

// Initialize repository and use case
$repository = new InMemoryUserRepository();
$useCase = new CreateUserUseCase($repository);

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
for ($i = 1; $i <= 10; $i++) {
    $user = $repository->findById(new UserId($i));
    if ($user) {
        $users[] = [
            'id' => $user->getId()->getValue(),
            'name' => $user->getName()->getValue(),
            'email' => $user->getEmail()->getValue(),
            'canCreatePost' => $user->canCreateNewPost()
        ];
    }
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clean Architecture Demo</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 2rem;
            padding: 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
        }
        .demo-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            border-left: 4px solid #007bff;
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
            background: #007bff;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }
        button:hover {
            background: #0056b3;
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
            border-left: 3px solid #28a745;
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
            border-left: 3px solid #007bff;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Clean Architecture Demo</h1>
        <p>PHPカンファレンス2025 スライドコード実行デモ</p>
    </div>

    <div class="demo-section">
        <h2>ユーザー作成デモ</h2>
        <p>スライドに記載されているClean Architectureの実装を実際に動作させています</p>
        
        <form method="POST">
            <input type="hidden" name="action" value="create_user">
            
            <div class="form-group">
                <label for="name">名前（2文字以上）:</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="email">メールアドレス:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <button type="submit">ユーザーを作成</button>
        </form>

        <?php if ($result): ?>
        <div class="success">
            <strong>ユーザー作成成功!</strong><br>
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
        <h2>作成されたユーザー一覧</h2>
        
        <?php if (empty($users)): ?>
            <p>まだユーザーが作成されていません。上のフォームから作成してください。</p>
        <?php else: ?>
            <div class="users-list">
                <?php foreach ($users as $user): ?>
                <div class="user-card">
                    <strong>ID: <?= $user['id'] ?></strong> - <?= htmlspecialchars($user['name']) ?><br>
                    <?= htmlspecialchars($user['email']) ?><br>
                    <span class="badge <?= $user['canCreatePost'] ? '' : 'inactive' ?>">
                        投稿<?= $user['canCreatePost'] ? '可能' : '上限達成' ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="architecture-note">
        <h3>Clean Architecture実装ポイント</h3>
        <ul>
            <li><strong>Domain層</strong>: Entity、Value Object - ビジネスルールを実装</li>
            <li><strong>Application層</strong>: UseCase - アプリケーションの処理フロー</li>
            <li><strong>Infrastructure層</strong>: Repository実装 - データ永続化</li>
            <li><strong>フレームワーク非依存</strong>: Pure PHPで実装されたコア機能</li>
        </ul>
        <p>このデモは、スライドに記載されているコードがそのまま動作することを示しています。</p>
    </div>
</body>
</html>