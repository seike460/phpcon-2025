<?php

require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Container\Container;
use Illuminate\Validation\Factory as ValidatorFactory;
use Illuminate\Translation\Translator;
use Illuminate\Translation\ArrayLoader;

// データベース設定
$capsule = new DB;
$capsule->addConnection([
    'driver' => 'sqlite',
    'database' => __DIR__ . '/database/eloquent_demo.sqlite',
    'prefix' => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

// SQLiteデータベース作成
$dbPath = __DIR__ . '/database/eloquent_demo.sqlite';
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

// Phase 2: DI コンテナ設定
$container = new Container();

// Translation setup for validation  
$loader = new ArrayLoader();
$translator = new Translator($loader, 'en');

// Set up validator factory
$validatorFactory = new ValidatorFactory($translator, $container);

// Set up presence verifier for unique validation
$presenceVerifier = new \Illuminate\Validation\DatabasePresenceVerifier($capsule->getDatabaseManager());
$validatorFactory->setPresenceVerifier($presenceVerifier);

// Register validator in container
$container->singleton('validator', function () use ($validatorFactory) {
    return $validatorFactory;
});

// Set global validator facade
\Illuminate\Support\Facades\Validator::swap($validatorFactory);

// Phase 2: Repository Pattern DI 設定
use App\Domain\Repository\UserRepositoryInterface;
use App\Infrastructure\Repository\EloquentUserRepository;
use App\Application\Service\UserValidationService;

// Repository の DI 設定
$container->singleton(UserRepositoryInterface::class, function () {
    return new EloquentUserRepository();
});

// ValidationService の DI 設定
$container->singleton(UserValidationService::class, function ($container) {
    return new UserValidationService($container->make(UserRepositoryInterface::class));
});

return $container;