# 权限管理系统

基于 Slim 4 + SQLite 构建的轻量级权限管理系统。

## 功能特性

- 用户管理
  - 创建、编辑、删除用户
  - 设置用户角色和权限
  - 设置账号有效期
  - 启用/禁用账号
  - 查看用户登录记录
- 角色权限管理
  - 预设角色:超级管理员(admin)、管理员、普通用户
  - 自定义角色权限
- 安全特性
  - JWT 令牌认证
  - 密码加密存储
  - 登录失败次数限制
  - 会话超时控制
  - 操作日志记录

## 系统要求

- PHP >= 8.0
- SQLite 3
- Composer

## 安装部署

1. 克隆代码:
```bash
git clone https://github.com/yourusername/auth-system.git
cd auth-system
```

2. 安装依赖:
```bash
composer install
```

3. 配置环境变量:
```bash
cp .env.example .env
# 修改 .env 中的配置
```

4. 初始化数据库:
```bash
php database/init.php
```

5. 启动服务:
```bash
php -S 0.0.0.0:8080 -t public
```

## API 接口说明

### 认证相关

#### 登录
- 路径: POST /api/login
- 参数:
  ```json
  {
    "username": "用户名",
    "password": "密码"
  }
  ```
- 返回:
  ```json
  {
    "success": true,
    "data": {
      "token": "JWT令牌",
      "user": {
        "id": 1,
        "username": "用户名",
        "role": "角色"
      }
    }
  }
  ```

#### 退出登录
- 路径: POST /api/logout
- 头部: Authorization: Bearer {token}
- 返回:
  ```json
  {
    "success": true,
    "message": "退出成功"
  }
  ```

### 用户管理

#### 获取用户列表
- 路径: GET /api/users
- 头部: Authorization: Bearer {token}
- 返回:
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 1,
        "username": "用户名",
        "email": "邮箱",
        "role": "角色",
        "status": 1,
        "valid_from": "有效期开始",
        "valid_until": "有效期结束",
        "last_login_time": "最后登录时间"
      }
    ]
  }
  ```

#### 创建用户
- 路径: POST /api/users
- 头部: Authorization: Bearer {token}
- 参数:
  ```json
  {
    "username": "用户名",
    "password": "密码",
    "email": "邮箱",
    "role": "角色",
    "valid_from": "有效期开始(可选)",
    "valid_until": "有效期结束(可选)"
  }
  ```

#### 更新用户
- 路径: PUT /api/users/{id}
- 头部: Authorization: Bearer {token}
- 参数: 同创建用户

#### 删除用户
- 路径: DELETE /api/users/{id}
- 头部: Authorization: Bearer {token}

## 重要更新说明

### 2024-01-16 更新
1. 超级管理员(admin)账号保护:
   - admin 账号永久有效,不受有效期限制
   - admin 账号不能被禁用
   - admin 账号不能被删除
   - admin 账号角色不能被修改

2. Bug修复:
   - 修复了用户编辑时状态显示错误的问题
   - 修复了 token_blacklist 表缺失导致登出失败的问题
   - 修复了编辑用户时未正确处理密码的问题

## 注意事项

1. 超级管理员账号
   - 系统预设超级管理员账号为 admin
   - admin 账号具有特殊保护机制,确保系统稳定性
   - 请妥善保管 admin 账号密码

2. 安全建议
   - 及时修改默认密码
   - 定期更换密码
   - 适时清理过期会话
   - 定期备份数据库

3. 开发注意
   - API 调用需要携带有效的 JWT token
   - 所有请求使用 JSON 格式
   - 密码长度至少 6 位
   - 注意处理 API 返回的错误信息

## 常见问题

1. 登录失败
   - 检查用户名密码是否正确
   - 检查账号是否被禁用
   - 检查账号是否在有效期内
   - 检查是否超过登录失败次数限制

2. 无法访问API
   - 检查 token 是否已过期
   - 检查 token 格式是否正确
   - 检查用户权限是否足够

3. 数据库问题
   - 检查数据库文件权限
   - 确保数据库表结构完整
   - 定期维护数据库文件

## 贡献指南

欢迎提交 Issue 和 Pull Request 来帮助改进项目。

## 开源协议

MIT License 