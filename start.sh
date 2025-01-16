#!/bin/bash

echo "开始初始化系统..."

# 安装依赖
echo "正在安装依赖..."
composer install

# 创建必要的目录
echo "创建必要的目录..."
mkdir -p database
mkdir -p public/uploads/avatars

# 设置权限
echo "设置目录权限..."
chmod -R 777 public/uploads database

# 创建数据库文件（如果不存在）
echo "初始化数据库..."
touch database/database.sqlite
chmod 777 database/database.sqlite

# 初始化数据库和创建管理员账户
echo "初始化数据库和创建管理员账户..."
php src/Config/init.php

# 启动服务器
echo "启动服务器..."
php -S 0.0.0.0:8080 -t public 