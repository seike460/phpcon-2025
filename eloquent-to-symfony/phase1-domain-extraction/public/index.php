<?php

$container = require_once __DIR__ . '/../bootstrap.php';

use App\Models\User;
use App\Models\Post;
use Illuminate\Validation\ValidationException;

// Handle form submission
$result = null;
$error = null;

if ($_POST['action'] ?? '' === 'create_user') {
    try {
        $result = User::createWithValidation([
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? ''
        ]);
    } catch (ValidationException $e) {
        $error = $e->validator->errors()->first();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get all users for display
$users = [];
try {
    $users = User::with('posts')->get()->map(function ($user) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'display_name' => $user->getDisplayName(),
            'posts_count' => $user->posts->count(),
            'can_create_post' => $user->canCreateNewPost(),
            'recent_posts' => $user->getRecentPosts(2)->toArray()
        ];
    })->toArray();
} catch (Exception $e) {
    // Handle database errors
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phase 1: Domain Entity抽出段階</title>
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
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border-radius: 10px;
        }
        .demo-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            border-left: 4px solid #28a745;
        }
        .framework-badge {
            display: inline-block;
            background: #28a745;
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
            background: #28a745;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }
        button:hover {
            background: #20c997;
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
            border-left: 3px solid #28a745;
            margin: 1rem 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Phase 1: Domain Entity抽出段階</h1>
        <p>PHPカンファレンス2025 - Eloquent依存からSymfony移行: 段階的リファクタリング</p>
        <span class="framework-badge">Laravel + Domain Entity</span>
        <span class="problem-badge">リファクタリング中</span>
    </div>

    <div class="problems-list">
        <h3>Phase 1での変更点</h3>
        <ul>
            <li><strong>Domain Entity抽出</strong>: Pure PHP Entityクラスを新規作成</li>
            <li><strong>ビジネスロジック移動</strong>: canCreateNewPost(), getDisplayName()をDomain層に移行</li>
            <li><strong>Eloquent残存</strong>: データアクセスは引き続きEloquent使用</li>
            <li><strong>バリデーション未分離</strong>: Laravel Validatorがまだ残存</li>
            <li><strong>Controller-Model結合</strong>: まだControllerがModelを直接呼び出し</li>
        </ul>
    </div>

    <div class="demo-section">
        <h2>Phase 1: Domain Entity併用でのユーザー作成</h2>
        <p>Domain Entityを抽出しつつ、段階的にビジネスロジックを移行中の状態</p>
        
        <div class="tech-stack">
            <span class="tech-item">Domain Entity (NEW)</span>
            <span class="tech-item">Eloquent Model</span>
            <span class="tech-item">Laravel Validation</span>
            <span class="tech-item">SQLite Database</span>
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
            
            <button type="submit">Domain Entity併用でユーザー作成</button>
        </form>

        <?php if ($result): ?>
        <div class="success">
            <strong>Phase 1実装でユーザー作成成功!</strong><br>
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
        <h2>Domain Entity + Eloquentで管理されたユーザー</h2>
        
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
        <h3>Phase 1: Domain Entity抽出の成果</h3>
        <ul>
            <li><strong>ビジネスロジック分離開始</strong>: canCreateNewPost(), getDisplayName()をDomain層に移動</li>
            <li><strong>Pure PHP実装</strong>: フレームワーク非依存のEntityクラス作成</li>
            <li><strong>段階的移行</strong>: 既存Eloquentコードを破壊せずに並行実装</li>
            <li><strong>部分的改善</strong>: データアクセスとバリデーションは未分離</li>
            <li><strong>重複実装</strong>: EloquentとDomain両方でロジック保持</li>
        </ul>
        
        <h4>次のPhase 2で実装予定</h4>
        <ul>
            <li><strong>Repository Pattern導入</strong>: データアクセス抽象化</li>
            <li><strong>Eloquent依存除去</strong>: Repository Interface経由でのデータ操作</li>
            <li><strong>バリデーション分離</strong>: Laravelバリデーション依存の解消</li>
        </ul>
        
        <p><strong>移行進捗</strong>: Eloquent依存コードのSymfony移行 - Phase 1完了 (25%完了)</p>
    </div>
</body>
</html>