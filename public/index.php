<?php
use DI\Container;
use Slim\Factory\AppFactory;
use App\Controller\UserController;
use App\Controller\LogController;
use App\Controller\RoleController;
use App\Controller\PermissionController;
use App\Controller\AvatarController;
use App\Middleware\LogMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

// 加载环境变量
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

require __DIR__ . '/../src/Config/database.php';

// 创建DI容器
$container = new Container();

// 设置应用实例
AppFactory::setContainer($container);
$app = AppFactory::create();

// 添加错误中间件
$app->addErrorMiddleware(true, true, true);

// 添加全局中间件
$app->add(new LogMiddleware());

// 添加路由
$app->post('/api/login', [UserController::class, 'login']);
$app->post('/api/logout', [UserController::class, 'logout'])->add(new AuthMiddleware());

// 用户管理路由
$app->get('/api/users', [UserController::class, 'list'])
    ->add(new PermissionMiddleware('view_users'))
    ->add(new AuthMiddleware());

$app->post('/api/users', [UserController::class, 'create'])
    ->add(new PermissionMiddleware('create_users'))
    ->add(new AuthMiddleware());

$app->put('/api/users/{id}', [UserController::class, 'update'])
    ->add(new PermissionMiddleware('update_users'))
    ->add(new AuthMiddleware());

$app->delete('/api/users/{id}', [UserController::class, 'delete'])
    ->add(new PermissionMiddleware('delete_users'))
    ->add(new AuthMiddleware());

// 角色管理路由
$app->get('/api/roles', [RoleController::class, 'list'])
    ->add(new PermissionMiddleware('view_roles'))
    ->add(new AuthMiddleware());

$app->post('/api/roles', [RoleController::class, 'create'])
    ->add(new PermissionMiddleware('create_roles'))
    ->add(new AuthMiddleware());

$app->put('/api/roles/{id}', [RoleController::class, 'update'])
    ->add(new PermissionMiddleware('update_roles'))
    ->add(new AuthMiddleware());

$app->delete('/api/roles/{id}', [RoleController::class, 'delete'])
    ->add(new PermissionMiddleware('delete_roles'))
    ->add(new AuthMiddleware());

// 权限管理路由
$app->get('/api/permissions', [PermissionController::class, 'list'])
    ->add(new PermissionMiddleware('view_permissions'))
    ->add(new AuthMiddleware());

$app->post('/api/permissions', [PermissionController::class, 'create'])
    ->add(new PermissionMiddleware('create_permissions'))
    ->add(new AuthMiddleware());

$app->put('/api/permissions/{id}', [PermissionController::class, 'update'])
    ->add(new PermissionMiddleware('update_permissions'))
    ->add(new AuthMiddleware());

$app->delete('/api/permissions/{id}', [PermissionController::class, 'delete'])
    ->add(new PermissionMiddleware('delete_permissions'))
    ->add(new AuthMiddleware());

// 日志管理路由
$app->get('/api/logs', [LogController::class, 'list'])
    ->add(new PermissionMiddleware('view_logs'))
    ->add(new AuthMiddleware());

// 头像上传路由
$app->post('/api/avatar', [AvatarController::class, 'upload'])
    ->add(new AuthMiddleware());

// 运行应用
$app->run(); 