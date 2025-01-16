# 权限后台管理系统

基于 Slim Framework 构建的权限后台管理系统，提供用户管理、角色权限控制、在线状态管理和操作日志记录等功能。

## 最新更新 (2025-01-16)

### 新增功能
- 添加用户账号有效期管理功能
  - 支持设置账号生效时间和过期时间
  - 账号有效期状态显示（未生效、有效、已过期）
  - 永久有效账号支持
- 添加用户登录失败次数限制和账户锁定机制
- 完善用户在线状态管理
- 添加邮箱唯一性验证
- 优化 JSON 响应，支持中文编码
- 添加用户权限即时验证
- 完善日志记录功能
- 优化前端界面，使用 Bootstrap 5 和 jQuery
- 添加角色选择功能（普通用户、管理员、编辑）
- 添加 CORS 跨域支持，支持多域名配置

### 问题修复
- 修复用户创建时的角色分配问题
- 修复用户更新时邮箱重复检查问题
- 修复权限验证中的角色判断逻辑
- 修复 Token 过期处理机制
- 优化错误响应格式
- 修复角色显示为 undefined 的问题
- 修复编辑用户时角色不正确的问题
- 修复前端 jQuery 依赖缺失问题

## 功能特性

### 1. 用户管理
- 用户注册和登录
- 用户信息管理（CRUD）
- 用户头像上传
- 用户在线状态管理
- 账户有效期管理
  - 设置账号生效时间
  - 设置账号过期时间
  - 永久有效账号配置
- 账户锁定机制
  - 登录失败次数限制（默认 5 次）
  - 自动锁定时间（默认 30 分钟）
  - 登录成功后重置失败次数

### 2. 权限管理
- 基于角色的权限控制（RBAC）
- 灵活的权限分配
- 权限验证中间件
- 即时权限验证
- 管理员角色特权

### 3. 日志管理
- 用户操作日志记录
- 登录日志记录
- 日志查询功能
- IP 地址记录
- 详细操作描述

### 4. 安全特性
- JWT 认证
- 密码加密存储
- 防止重复登录
- 请求日志记录
- 账户锁定保护
- 邮箱唯一性验证

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

5. 启动服务
```bash
./start.sh
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
      "role": "admin",
      "permissions": ["view_users", "create_users", ...]
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
    "message": "退出登录成功"
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
        "is_online": true,
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

### 错误处理

系统会返回统一格式的错误响应：
```json
{
  "success": false,
  "message": "错误信息",
  "details": "详细错误说明（如果有）"
}
```

常见错误码：
- 400：请求参数错误
- 401：未授权访问
- 403：权限不足
- 404：资源不存在
- 500：服务器内部错误

## 注意事项

1. 安全建议
   - 修改默认管理员密码
   - 定期检查日志记录
   - 配置适当的登录失败限制
   - 使用强密码策略

2. 性能优化
   - 添加缓存层
   - 优化数据库查询
   - 使用合适的索引

3. 开发建议
   - 遵循 API 响应格式
   - 处理所有异常情况
   - 添加适当的日志记录
   - 注意数据验证

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
- [ ] 添加多因素认证
- [ ] 实现密码重置功能
- [ ] 添加操作审计功能 