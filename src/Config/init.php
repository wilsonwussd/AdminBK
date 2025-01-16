<?php
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/database.php';

use App\Model\Permission;
use App\Model\Role;
use App\Model\User;
use Illuminate\Database\Capsule\Manager as Capsule;

// 创建数据表
try {
    // 用户表
    if (!Capsule::schema()->hasTable('users')) {
        Capsule::schema()->create('users', function ($table) {
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
            $table->string('avatar')->nullable();
            $table->timestamps();
        });
        echo "用户表创建成功\n";
    }

    // 在线用户表
    if (!Capsule::schema()->hasTable('online_users')) {
        Capsule::schema()->create('online_users', function ($table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('token');
            $table->string('ip_address')->nullable();
            $table->timestamp('login_time')->nullable();
        });
        echo "在线用户表创建成功\n";
    }

    // 操作日志表
    if (!Capsule::schema()->hasTable('operation_logs')) {
        Capsule::schema()->create('operation_logs', function ($table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action');
            $table->text('description')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamp('created_at')->nullable();
        });
        echo "操作日志表创建成功\n";
    }

    // 角色表
    if (!Capsule::schema()->hasTable('roles')) {
        Capsule::schema()->create('roles', function ($table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });
        echo "角色表创建成功\n";
    }

    // 权限表
    if (!Capsule::schema()->hasTable('permissions')) {
        Capsule::schema()->create('permissions', function ($table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });
        echo "权限表创建成功\n";
    }

    // 角色权限关联表
    if (!Capsule::schema()->hasTable('role_permissions')) {
        Capsule::schema()->create('role_permissions', function ($table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['role_id', 'permission_id']);
        });
        echo "角色权限关联表创建成功\n";
    }
} catch (\Exception $e) {
    echo "数据库初始化错误: " . $e->getMessage() . "\n";
    exit(1);
}

// 定义基础权限
$permissions = [
    // 用户管理权限
    ['name' => 'view_users', 'description' => '查看用户列表'],
    ['name' => 'create_users', 'description' => '创建新用户'],
    ['name' => 'update_users', 'description' => '更新用户信息'],
    ['name' => 'delete_users', 'description' => '删除用户'],
    
    // 角色管理权限
    ['name' => 'view_roles', 'description' => '查看角色列表'],
    ['name' => 'create_roles', 'description' => '创建新角色'],
    ['name' => 'update_roles', 'description' => '更新角色信息'],
    ['name' => 'delete_roles', 'description' => '删除角色'],
    
    // 权限管理权限
    ['name' => 'view_permissions', 'description' => '查看权限列表'],
    ['name' => 'create_permissions', 'description' => '创建新权限'],
    ['name' => 'update_permissions', 'description' => '更新权限信息'],
    ['name' => 'delete_permissions', 'description' => '删除权限'],
    
    // 日志管理权限
    ['name' => 'view_logs', 'description' => '查看操作日志'],
];

try {
    // 创建权限
    foreach ($permissions as $permission) {
        Permission::updateOrCreate(
            ['name' => $permission['name']],
            ['description' => $permission['description']]
        );
    }
    echo "权限初始化完成\n";

    // 创建管理员角色
    $adminRole = Role::updateOrCreate(
        ['name' => 'admin'],
        ['description' => '系统管理员']
    );

    // 为管理员角色分配所有权限
    $allPermissions = Permission::all();
    $adminRole->permissions()->sync($allPermissions->pluck('id'));
    echo "管理员角色创建完成\n";

    // 创建管理员用户
    $adminUser = User::updateOrCreate(
        ['username' => 'admin'],
        [
            'email' => 'admin@example.com',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'role' => 'admin',
            'status' => 1
        ]
    );

    echo "管理员用户创建完成\n";
    echo "管理员账号：admin\n";
    echo "管理员密码：admin123\n";
} catch (\Exception $e) {
    echo "初始化错误: " . $e->getMessage() . "\n";
    exit(1);
} 