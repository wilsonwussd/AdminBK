<?php
require __DIR__ . '/../../../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// 初始化数据库连接
$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'sqlite',
    'database'  => __DIR__ . '/../../../database/database.sqlite',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

// 创建 token 黑名单表
if (!Capsule::schema()->hasTable('token_blacklist')) {
    Capsule::schema()->create('token_blacklist', function ($table) {
        $table->id();
        $table->string('token', 500)->unique();  // JWT token
        $table->timestamp('expired_at');         // token 过期时间
        $table->timestamp('created_at')->useCurrent();
    });
    echo "Token blacklist table created successfully!\n";
} else {
    echo "Token blacklist table already exists.\n";
} 