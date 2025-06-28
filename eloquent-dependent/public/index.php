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
    <title>Eloquent依存実装デモ (Use Case 2)</title>
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
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            border-radius: 10px;
        }
        .demo-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            border-left: 4px solid #dc3545;
        }
        .framework-badge {
            display: inline-block;
            background: #dc3545;
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
            background: #dc3545;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }
        button:hover {
            background: #c82333;
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
            border-left: 3px solid #dc3545;
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
            border-left: 3px solid #dc3545;
            margin: 1rem 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Eloquent依存実装デモ</h1>
        <p>PHPカンファレンス2025 - Use Case 2: Eloquent依存が進んだロジック</p>
        <span class="framework-badge">Laravel + Eloquent + SQLite</span>
        <span class="problem-badge">移行困難</span>
    </div>

    <div class="problems-list">
        <h3>この実装の問題点</h3>
        <ul>
            <li><strong>Model内にビジネスロジック混在</strong>: canCreateNewPost(), getDisplayName()</li>
            <li><strong>Eloquentに強依存</strong>: hasMany(), posts()->count() などORM固有機能</li>
            <li><strong>Laravelバリデーション依存</strong>: Validator::make() 直接使用</li>
            <li><strong>Controller-Model密結合</strong>: Domain層が存在しない</li>
            <li><strong>フレームワーク移行時</strong>: 全面的なリファクタリングが必要</li>
        </ul>
    </div>

    <div class="demo-section">
        <h2>Eloquent依存でのユーザー作成</h2>
        <p>スライドのUse Case 2に対応：Eloquent Model内にビジネスロジックが混在した典型的な実装</p>
        
        <div class="tech-stack">
            <span class="tech-item">Eloquent Model</span>
            <span class="tech-item">Laravel Validation</span>
            <span class="tech-item">SQLite Database</span>
            <span class="tech-item">密結合設計</span>
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
            
            <button type="submit">Eloquentでユーザーを作成</button>
        </form>

        <?php if ($result): ?>
        <div class="success">
            <strong>Eloquent実装でユーザー作成成功!</strong><br>
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
        <h2>Eloquent + SQLiteで管理されたユーザー</h2>
        
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
        <h3>Eloquent依存実装の問題</h3>
        <ul>
            <li><strong>フレームワーク密結合</strong>: Eloquentモデルがビジネスロジックを含む</li>
            <li><strong>テスト困難</strong>: ORMに依存したロジックのユニットテスト</li>
            <li><strong>移行リスク</strong>: Symfony移行時に全面リファクタリング必要</li>
            <li><strong>責任の混在</strong>: データアクセス・バリデーション・ビジネスルールが混在</li>
            <li><strong>技術負債</strong>: フレームワーク依存度が高く変更困難</li>
        </ul>
        
        <h4>Symfony移行に必要な作業</h4>
        <ul>
            <li>Domain Entity の抽出（ビジネスルール分離）</li>
            <li>Value Object の導入（バリデーション分離）</li>
            <li>Repository Pattern の実装（データアクセス抽象化）</li>
            <li>UseCase 層の分離（処理フロー分離）</li>
            <li>DI 設定の変更（Symfony Container対応）</li>
        </ul>
        
        <p><strong>Clean Architectureとの比較</strong>: Use Case 1（純粋なドメイン）と比べて、移行工数が<strong>10倍以上</strong>必要になる典型例です。</p>
    </div>
</body>
</html>