# 权限后台管理系统

基于 Slim Framework 构建的权限后台管理系统，提供用户管理、角色权限控制、在线状态管理和操作日志记录等功能。

## 功能特性

### 1. 用户管理
- 用户注册和登录
- 用户信息管理（CRUD）
- 用户头像上传
- 用户在线状态管理
- 账户锁定机制

### 2. 权限管理
- 基于角色的权限控制（RBAC）
- 灵活的权限分配
- 权限验证中间件

### 3. 日志管理
- 用户操作日志记录
- 登录日志记录
- 日志查询功能

### 4. 安全特性
- JWT 认证
- 密码加密存储
- 防止重复登录
- 请求日志记录

## 安装步骤

1. 克隆项目
```bash
git clone [项目地址]
cd project
```

2. 安装依赖
```bash
composer install
```

3. 配置环境变量
```bash
cp .env.example .env
# 编辑 .env 文件，配置数据库等信息
```

4. 初始化数据库
```bash
php src/Config/init.php
```

## API 文档

### 认证相关

#### 登录
- 请求：`POST /api/login`
- 参数：
  ```json
  {
    "username": "admin",
    "password": "admin123"
  }
  ```
- 响应：
  ```json
  {
    "success": true,
    "token": "xxx.xxx.xxx",
    "user": {
      "username": "admin",
      "email": "admin@example.com",
      "role": "admin"
    }
  }
  ```

#### 登出
- 请求：`POST /api/logout`
- 头部：`Authorization: Bearer <token>`
- 响应：
  ```json
  {
    "success": true,
    "message": "登出成功"
  }
  ```

### 用户管理

#### 获取用户列表
- 请求：`GET /api/users`
- 头部：`Authorization: Bearer <token>`
- 响应：
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 1,
        "username": "admin",
        "email": "admin@example.com",
        "role": "admin",
        "status": 1,
        "last_login_time": "2024-01-15 10:00:00"
      }
    ]
  }
  ```

#### 创建用户
- 请求：`POST /api/users`
- 头部：`Authorization: Bearer <token>`
- 参数：
  ```json
  {
    "username": "test_user",
    "email": "test@example.com",
    "password": "password123",
    "role": "user"
  }
  ```

#### 更新用户
- 请求：`PUT /api/users/{id}`
- 头部：`Authorization: Bearer <token>`
- 参数：
  ```json
  {
    "email": "new_email@example.com",
    "role": "editor"
  }
  ```

#### 删除用户
- 请求：`DELETE /api/users/{id}`
- 头部：`Authorization: Bearer <token>`

### 头像管理

#### 上传头像
- 请求：`POST /api/avatar`
- 头部：`Authorization: Bearer <token>`
- 参数：
  - 类型：`multipart/form-data`
  - 字段：`avatar`（文件）
- 响应：
  ```json
  {
    "success": true,
    "message": "头像上传成功",
    "data": {
      "avatar": "/uploads/avatars/filename.jpg"
    }
  }
  ```

### 日志管理

#### 获取操作日志
- 请求：`GET /api/logs`
- 头部：`Authorization: Bearer <token>`
- 参数：
  - `page`：页码
  - `per_page`：每页数量
  - `user_id`：用户ID（可选）
  - `action`：操作类型（可选）
  - `start_date`：开始日期（可选）
  - `end_date`：结束日期（可选）

## 权限说明

系统预设以下权限：
- `view_users`: 查看用户列表
- `create_users`: 创建用户
- `update_users`: 更新用户信息
- `delete_users`: 删除用户
- `view_logs`: 查看日志
- `view_roles`: 查看角色
- `create_roles`: 创建角色
- `update_roles`: 更新角色
- `delete_roles`: 删除角色

## 默认账户

- 用户名：`admin`
- 密码：`admin123`
- 角色：`admin`

## 注意事项

1. 文件上传
   - 支持的图片格式：jpg、jpeg、png、gif
   - 建议图片大小不超过 2MB

2. 安全建议
   - 定期更改管理员密码
   - 及时更新系统和依赖包
   - 定期检查日志记录

## 技术栈

- PHP 8.3
- Slim Framework 4.x
- SQLite 数据库
- JWT 认证
- Composer 包管理

## 开发计划

- [ ] 添加用户组功能
- [ ] 实现批量导入用户
- [ ] 添加系统配置管理
- [ ] 优化头像处理（裁剪、压缩）
- [ ] 添加 API 限流功能 