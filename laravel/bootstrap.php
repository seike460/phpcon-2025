<?php

require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Container\Container;
use App\Domain\Repository\UserRepositoryInterface;
use Laravel\Infrastructure\Repository\LaravelUserRepository;
use App\Application\UseCase\User\CreateUserUseCase;

// データベース設定
$capsule = new DB;
$capsule->addConnection([
    'driver' => 'sqlite',
    'database' => __DIR__ . '/database/laravel_demo.sqlite',
    'prefix' => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

// SQLiteデータベース作成
$dbPath = __DIR__ . '/database/laravel_demo.sqlite';
if (!file_exists(dirname($dbPath))) {
    mkdir(dirname($dbPath), 0755, true);
}

if (!file_exists($dbPath)) {
    touch($dbPath);
    $sql = file_get_contents(__DIR__ . '/database/migrations.sql');
    $statements = explode(';', $sql);
    foreach ($statements as $statement) {
        if (trim($statement)) {
            DB::statement($statement);
        }
    }
}

// DIコンテナ設定
$container = new Container();
$container->singleton(UserRepositoryInterface::class, LaravelUserRepository::class);
$container->singleton(CreateUserUseCase::class, function($container) {
    return new CreateUserUseCase($container->make(UserRepositoryInterface::class));
});

return $container;