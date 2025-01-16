<?php

require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Dotenv\Dotenv;

// 加载环境变量
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// 配置数据库连接
$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'sqlite',
    'database' => __DIR__ . '/database.sqlite',
    'prefix' => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

// 删除旧表
$capsule::schema()->dropIfExists('online_users');
$capsule::schema()->dropIfExists('token_blacklist');
$capsule::schema()->dropIfExists('users');
$capsule::schema()->dropIfExists('roles');
$capsule::schema()->dropIfExists('permissions');

// 创建权限表
$capsule::schema()->create('permissions', function ($table) {
    $table->id();
    $table->string('name')->unique();
    $table->string('description')->nullable();
    $table->timestamps();
});
echo "权限表创建成功\n";

// 创建角色表
$capsule::schema()->create('roles', function ($table) {
    $table->id();
    $table->string('name')->unique();
    $table->string('description')->nullable();
    $table->timestamps();
});
echo "角色表创建成功\n";

// 创建角色权限关联表
$capsule::schema()->create('role_permissions', function ($table) {
    $table->id();
    $table->unsignedBigInteger('role_id');
    $table->unsignedBigInteger('permission_id');
    $table->timestamps();
    
    $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
    $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
    
    $table->unique(['role_id', 'permission_id']);
});
echo "角色权限关联表创建成功\n";

// 创建用户表
$capsule::schema()->create('users', function ($table) {
    $table->id();
    $table->string('username')->unique();
    $table->string('email')->unique();
    $table->string('password');
    $table->string('role')->default('user');
    $table->boolean('status')->default(1);
    $table->string('last_login_ip')->nullable();
    $table->timestamp('last_login_time')->nullable();
    $table->integer('login_attempts')->default(0);
    $table->timestamp('locked_until')->nullable();
    $table->timestamp('valid_from')->nullable();
    $table->timestamp('valid_until')->nullable();
    $table->timestamps();
});
echo "用户表创建成功\n";

// 创建在线用户表
$capsule::schema()->create('online_users', function ($table) {
    $table->id();
    $table->unsignedBigInteger('user_id');
    $table->string('token');
    $table->string('ip_address');
    $table->timestamp('login_time');
    $table->timestamps();
});
echo "在线用户表创建成功\n";

// 创建 token 黑名单表
$capsule::schema()->create('token_blacklist', function ($table) {
    $table->id();
    $table->string('token');
    $table->timestamp('expired_at');
    $table->timestamps();
});
echo "Token 黑名单表创建成功\n";

// 创建基本权限
$permissions = [
    ['name' => 'view_users', 'description' => '查看用户列表'],
    ['name' => 'create_users', 'description' => '创建用户'],
    ['name' => 'update_users', 'description' => '更新用户'],
    ['name' => 'delete_users', 'description' => '删除用户'],
    ['name' => 'view_roles', 'description' => '查看角色'],
    ['name' => 'create_roles', 'description' => '创建角色'],
    ['name' => 'update_roles', 'description' => '更新角色'],
    ['name' => 'delete_roles', 'description' => '删除角色'],
    ['name' => 'view_logs', 'description' => '查看日志'],
];

foreach ($permissions as $permission) {
    $capsule::table('permissions')->insert([
        'name' => $permission['name'],
        'description' => $permission['description'],
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);
}
echo "基本权限创建成功\n";

// 创建基本角色
$roles = [
    [
        'name' => 'admin',
        'description' => '超级管理员'
    ],
    [
        'name' => 'user',
        'description' => '普通用户'
    ]
];

foreach ($roles as $role) {
    $capsule::table('roles')->insert([
        'name' => $role['name'],
        'description' => $role['description'],
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);
}
echo "基本角色创建成功\n";

// 为管理员角色分配所有权限
$adminRole = $capsule::table('roles')->where('name', 'admin')->first();
$allPermissions = $capsule::table('permissions')->get();

foreach ($allPermissions as $permission) {
    $capsule::table('role_permissions')->insert([
        'role_id' => $adminRole->id,
        'permission_id' => $permission->id,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);
}

// 为普通用户角色分配基本权限
$userRole = $capsule::table('roles')->where('name', 'user')->first();
$basicPermissions = $capsule::table('permissions')
    ->whereIn('name', ['view_users'])
    ->get();

foreach ($basicPermissions as $permission) {
    $capsule::table('role_permissions')->insert([
        'role_id' => $userRole->id,
        'permission_id' => $permission->id,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);
}

echo "角色权限关联创建成功\n";

// 创建初始用户
$capsule::table('users')->insert([
    'username' => 'admin',
    'email' => 'admin@example.com',
    'password' => password_hash('admin123', PASSWORD_DEFAULT),
    'role' => 'admin',
    'status' => 1,
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s')
]);

$capsule::table('users')->insert([
    'username' => 'test123',
    'email' => 'test123@example.com',
    'password' => password_hash('test123', PASSWORD_DEFAULT),
    'role' => 'user',
    'status' => 1,
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s')
]);

echo "初始用户创建成功\n";
echo "数据库初始化完成！\n"; 