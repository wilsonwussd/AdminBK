<?php
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/database.php';

use App\Model\Permission;
use App\Model\Role;
use App\Model\User;

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
        'role_id' => $adminRole->id,
        'status' => 1
    ]
);

echo "管理员用户创建完成\n";
echo "管理员账号：admin\n";
echo "管理员密码：admin123\n"; 