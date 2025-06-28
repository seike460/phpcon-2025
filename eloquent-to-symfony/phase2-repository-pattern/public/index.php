<?php

$container = require_once __DIR__ . '/../bootstrap.php';

use App\Domain\Repository\UserRepositoryInterface;
use App\Application\Service\UserValidationService;
use App\Domain\Entity\User;
use Illuminate\Validation\ValidationException;

// Phase 2: DI コンテナからサービス取得
$userRepository = $container->make(UserRepositoryInterface::class);
$validationService = $container->make(UserValidationService::class);

// Handle form submission
$result = null;
$error = null;

if ($_POST['action'] ?? '' === 'create_user') {
    try {
        $data = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? ''
        ];
        
        // Phase 2: バリデーションサービス経由
        $validationService->validateForCreation($data);
        
        // Phase 2: Domain Entity作成
        $user = new User(0, $data['name'], $data['email']);
        
        // Phase 2: Repository経由で保存
        $result = $userRepository->save($user);
        
    } catch (ValidationException $e) {
        $error = $e->validator->errors()->first();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get all users for display
$users = [];
try {
    // Phase 2: Repository経由で全ユーザー取得
    $domainUsers = $userRepository->findAll();
    
    $users = array_map(function ($user) {
        return [
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'display_name' => $user->getDisplayName(),
            'posts_count' => count($user->getPosts()),
            'can_create_post' => $user->canCreateNewPost(),
            'recent_posts' => $user->getRecentPosts(2)
        ];
    }, $domainUsers);
    
} catch (Exception $e) {
    // Handle database errors
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phase 2: Repository Pattern導入段階</title>
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
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
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
        .framework-badge {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        .problem-badge {
            display: inline-block;
            background: #ffc107;
            color: #000;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-left: 0.5rem;
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
            border-left: 3px solid #007bff;
        }
        .user-card:last-child {
            margin-bottom: 0;
        }
        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            background: #007bff;
            color: white;
            border-radius: 12px;
            font-size: 0.8rem;
        }
        .badge.inactive {
            background: #6c757d;
        }
        .architecture-note {
            background: #fff3cd;
            color: #856404;
            padding: 1rem;
            border-radius: 6px;
            border-left: 3px solid #ffc107;
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
        .problems-list {
            background: #f8d7da;
            padding: 1rem;
            border-radius: 6px;
            border-left: 3px solid #007bff;
            margin: 1rem 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Phase 2: Repository Pattern導入段階</h1>
        <p>PHPカンファレンス2025 - Eloquent依存からSymfony移行: 段階的リファクタリング</p>
        <span class="framework-badge">Repository Pattern + DI</span>
        <span class="problem-badge">データアクセス分離完了</span>
    </div>

    <div class="problems-list">
        <h3>Phase 2での変更点</h3>
        <ul>
            <li><strong>Repository Pattern導入</strong>: データアクセス抽象化を完了</li>
            <li><strong>DI Container設定</strong>: Repository InterfaceのDependency Injection</li>
            <li><strong>バリデーション分離</strong>: ValidationServiceでModel依存を解消</li>
            <li><strong>Controller修正</strong>: Repository Interface経由でのデータ操作</li>
            <li><strong>Laravel依存残存</strong>: まだLaravel Validatorを使用</li>
        </ul>
    </div>

    <div class="demo-section">
        <h2>Phase 2: Repository Patternでのユーザー作成</h2>
        <p>Repository Interface経由でデータアクセスを完全分離した状態</p>
        
        <div class="tech-stack">
            <span class="tech-item">Repository Pattern (NEW)</span>
            <span class="tech-item">DI Container (NEW)</span>
            <span class="tech-item">Domain Entity</span>
            <span class="tech-item">ValidationService (NEW)</span>
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
            
            <button type="submit">Repository Patternでユーザー作成</button>
        </form>

        <?php if ($result): ?>
        <div class="success">
            <strong>Phase 2実装でユーザー作成成功!</strong><br>
            ID: <?= htmlspecialchars($result->id) ?><br>
            名前: <?= htmlspecialchars($result->name) ?><br>
            表示名: <?= htmlspecialchars($result->getDisplayName()) ?><br>
            メール: <?= htmlspecialchars($result->email) ?><br>
            投稿可能: <?= $result->canCreateNewPost() ? '可能' : '不可能' ?>
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
        <h2>Repository Patternで管理されたユーザー</h2>
        
        <?php if (empty($users)): ?>
            <p>まだユーザーが作成されていません。上のフォームから作成してください。</p>
        <?php else: ?>
            <div class="users-list">
                <?php foreach ($users as $user): ?>
                <div class="user-card">
                    <strong>ID: <?= $user['id'] ?></strong> - <?= htmlspecialchars($user['name']) ?><br>
                    <?= htmlspecialchars($user['email']) ?><br>
                    表示名: <?= htmlspecialchars($user['display_name']) ?><br>
                    投稿数: <?= $user['posts_count'] ?>/5<br>
                    <span class="badge <?= $user['can_create_post'] ? '' : 'inactive' ?>">
                        投稿<?= $user['can_create_post'] ? '可能' : '上限達成' ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="architecture-note">
        <h3>Phase 2: Repository Pattern導入の成果</h3>
        <ul>
            <li><strong>データアクセス抽象化</strong>: Repository Interface経由での完全分離</li>
            <li><strong>DI Container実装</strong>: 依存性注入でフレームワーク切り替え準備</li>
            <li><strong>バリデーション分離</strong>: ValidationServiceでModel依存解消</li>
            <li><strong>Controller改善</strong>: Domain Entity直接操作でビジネスロジック実行</li>
            <li><strong>実装交換可能</strong>: Repository実装をEloquent→Doctrineに切り替え可能</li>
        </ul>
        
        <h4>次のPhase 3で実装予定</h4>
        <ul>
            <li><strong>UseCase層分離</strong>: アプリケーションロジックの独立</li>
            <li><strong>Laravel依存除去</strong>: フレームワーク固有コードの完全排除</li>
            <li><strong>Pure PHP化完了</strong>: ビジネスロジック層の完全独立</li>
        </ul>
        
        <p><strong>移行進捗</strong>: Eloquent依存コードのSymfony移行 - Phase 2完了 (50%完了)</p>
    </div>
</body>
</html>