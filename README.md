# 权限管理系统

基于 Slim 4 + SQLite 构建的轻量级权限管理系统

## 最新更新 (2025-01-16)

### 新增功能
1. 用户账号有效期管理
   - 支持设置账号的生效时间和失效时间
   - 永久有效账号支持(留空表示永久有效)
   - 账号状态实时显示(未生效/有效/已过期)

2. 超级管理员(admin)特权
   - admin 账号永久有效,状态永久启用
   - admin 账号不可被删除或禁用
   - 普通用户和编辑无权修改 admin 账号信息

3. 用户权限分级控制
   - 普通用户只能修改自己的基本信息
   - 普通用户不能修改自己的角色和状态
   - 管理员可以管理所有用户(除 admin 外)
   - 新增用户功能仅对管理员开放

4. 密码安全性增强
   - 密码字段不回显原密码
   - 修改时可选是否更新密码
   - 密码输入框添加 autocomplete="new-password" 属性
   - 清晰的密码修改提示

### Bug 修复
1. 修复了普通用户无法保存个人信息的问题
2. 修复了编辑用户时密码字段显示问题
3. 修复了普通用户可以修改自身状态的问题
4. 修复了权限检查逻辑,确保用户只能执行被授权的操作

### 安全性改进
1. 增强了对超级管理员账号的保护
2. 完善了用户权限的检查机制
3. 优化了密码处理流程,提高安全性
4. 添加了更多的操作提示和错误信息

## 系统功能

### 用户管理
- 用户的增删改查
- 用户状态管理(启用/禁用)
- 用户角色分配
- 账号有效期管理

### 权限控制
- 基于角色的权限系统
- 三级用户角色(管理员/编辑/普通用户)
- 细粒度的操作权限控制
- 特权账号保护机制

### 安全特性
- JWT 令牌认证
- 密码加密存储
- 登录状态检查
- 操作日志记录

## 技术栈
- 后端: Slim 4 Framework
- 数据库: SQLite
- 前端: Bootstrap 5 + jQuery
- 认证: JWT

## 开始使用

### 环境要求
- PHP >= 7.4
- SQLite 3
- Composer

### 安装步骤
1. 克隆项目
```bash
git clone [项目地址]
```

2. 安装依赖
```bash
composer install
```

3. 初始化数据库
```bash
php database/init.php
```

4. 启动服务
```bash
php -S 0.0.0.0:8080 -t public
```

### 默认账号
- 超级管理员: admin/admin123
- 测试用户: test123/test123

## 注意事项
1. 请及时修改默认密码
2. 定期备份数据库文件
3. 在生产环境中建议使用 Nginx/Apache

## 未来规划
1. 添加更细粒度的权限控制
2. 支持用户组管理
3. 添加操作审计日志
4. 支持多因素认证
5. 添加自动备份功能

## 贡献指南
欢迎提交 Issue 和 Pull Request

## API 接口说明

所有 API 请求都需要在 header 中携带 token：
```
Authorization: Bearer {token}
```

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

#### 获取单个用户
- 路径: GET /api/users/{id}
- 返回:
  ```json
  {
    "success": true,
    "data": {
      "id": 1,
      "username": "用户名",
      "email": "邮箱",
      "role": "角色",
      "status": 1,
      "valid_from": "有效期开始",
      "valid_until": "有效期结束",
      "last_login_time": "最后登录时间"
    }
  }
  ```

#### 创建用户
- 路径: POST /api/users
- 权限: 仅管理员
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
- 返回:
  ```json
  {
    "success": true,
    "message": "用户创建成功",
    "data": {
      "id": "新创建的用户ID"
    }
  }
  ```

#### 更新用户
- 路径: PUT /api/users/{id}
- 权限: 管理员可更新所有用户，普通用户只能更新自己的基本信息
- 参数:
  ```json
  {
    "email": "邮箱",
    "password": "新密码(可选)",
    "role": "角色(仅管理员可修改)",
    "valid_from": "有效期开始(仅管理员可修改)",
    "valid_until": "有效期结束(仅管理员可修改)"
  }
  ```
- 返回:
  ```json
  {
    "success": true,
    "message": "用户更新成功"
  }
  ```

#### 删除用户
- 路径: DELETE /api/users/{id}
- 权限: 仅管理员，且不能删除超级管理员
- 返回:
  ```json
  {
    "success": true,
    "message": "用户删除成功"
  }
  ```

#### 修改用户状态
- 路径: PUT /api/users/{id}
- 权限: 仅管理员，且不能修改超级管理员状态
- 参数:
  ```json
  {
    "status": 0或1  // 0表示禁用，1表示启用
  }
  ```
- 返回:
  ```json
  {
    "success": true,
    "message": "用户状态更新成功"
  }
  ```

### API 调用注意事项

1. 认证相关
   - 所有非登录接口都需要在请求头中携带有效的 JWT token
   - token 格式为 `Bearer {token}`
   - token 过期或无效会返回 401 状态码
   - 超过登录失败次数限制会临时禁止登录

2. 权限控制
   - 普通用户只能查看和修改自己的基本信息
   - 管理员可以管理所有普通用户
   - 任何角色都不能修改超级管理员(admin)的信息
   - 未授权的操作会返回 403 状态码

3. 数据验证
   - 用户名不能重复
   - 邮箱格式必须合法
   - 密码长度至少 6 位
   - 有效期结束时间必须大于开始时间

4. 错误处理
   - 所有接口统一返回格式
   - success 字段表示操作是否成功
   - message 字段包含错误描述
   - 系统错误会返回 500 状态码

5. 安全建议
   - 定期更换密码
   - 及时清理过期会话
   - 使用 HTTPS 传输
   - 不要在前端存储敏感信息

## 许可证
MIT License 

## 项目部署信息

### 访问地址

- 前端页面: https://xxzcqrmtfyhm.sealoshzh.site/login.html
- 后端接口: https://xxzcqrmtfyhm.sealoshzh.site/api

### 测试账号

1. 超级管理员账号
   - 用户名: admin
   - 密码: admin123
   - 权限: 所有权限

2. 普通用户账号
   - 用户名: test123  
   - 密码: test123
   - 权限: 基础查看权限

### 接口文档

#### 认证相关

1. 登录接口
```
POST /api/login
Content-Type: application/json

请求体:
{
    "username": "用户名",
    "password": "密码"
}

响应:
{
    "success": true,
    "data": {
        "token": "JWT令牌",
        "user": {
            "id": 1,
            "username": "用户名",
            "role": "角色",
            "permissions": ["权限1", "权限2"]
        }
    }
}
```

2. 登出接口
```
POST /api/logout
Authorization: Bearer {token}

响应:
{
    "success": true,
    "message": "登出成功"
}
```

#### 用户管理

1. 获取用户列表
```
GET /api/users
Authorization: Bearer {token}

响应:
{
    "success": true,
    "data": [
        {
            "id": 1,
            "username": "用户名",
            "email": "邮箱",
            "role": "角色",
            "status": 1,
            "created_at": "创建时间"
        }
    ]
}
```

2. 获取单个用户
```
GET /api/users/{id}
Authorization: Bearer {token}

响应:
{
    "success": true,
    "data": {
        "id": 1,
        "username": "用户名",
        "email": "邮箱",
        "role": "角色",
        "status": 1
    }
}
```

3. 创建用户
```
POST /api/users
Authorization: Bearer {token}
Content-Type: application/json

请求体:
{
    "username": "用户名",
    "password": "密码",
    "email": "邮箱",
    "role": "角色"
}

响应:
{
    "success": true,
    "message": "用户创建成功"
}
```

4. 更新用户
```
PUT /api/users/{id}
Authorization: Bearer {token}
Content-Type: application/json

请求体:
{
    "username": "用户名",
    "email": "邮箱",
    "role": "角色",
    "status": 1
}

响应:
{
    "success": true,
    "message": "用户更新成功"
}
```

5. 删除用户
```
DELETE /api/users/{id}
Authorization: Bearer {token}

响应:
{
    "success": true,
    "message": "用户删除成功"
}
```

### 开发注意事项

1. 权限控制
   - 所有接口请求需要在 Header 中携带 token
   - 超级管理员拥有所有权限
   - 普通用户只能查看和修改自己的信息

2. 错误处理
   - 接口统一返回格式:
     ```json
     {
         "success": false,
         "message": "错误信息"
     }
     ```
   - HTTP 状态码说明:
     - 200: 请求成功
     - 400: 请求参数错误
     - 401: 未登录或 token 失效
     - 403: 权限不足
     - 404: 资源不存在
     - 500: 服务器内部错误

3. 安全建议
   - 定期更换密码
   - 使用 HTTPS 进行通信
   - 妥善保管 token
   - 及时登出未使用的会话 

## 在线部署信息

### 系统访问地址

1. 前端页面
   - 登录页面: https://xxzcqrmtfyhm.sealoshzh.site/login.html
   - 管理页面: https://xxzcqrmtfyhm.sealoshzh.site/admin.html

2. 后端接口
   - 基础地址: https://xxzcqrmtfyhm.sealoshzh.site/api
   - 接口文档: https://xxzcqrmtfyhm.sealoshzh.site/api/docs (如果启用了 Swagger)

### 开发测试账号

1. 超级管理员账号 (用于系统管理)
   - 用户名: admin
   - 密码: admin123
   - 权限: 所有权限（用户管理、角色管理、权限分配等）
   - 特点: 
     * 账号永久有效
     * 不可被删除或禁用
     * 可以管理其他所有用户

2. 普通测试账号 (用于功能测试)
   - 用户名: test123
   - 密码: test123
   - 权限: 基础查看权限
   - 特点:
     * 只能查看和修改自己的信息
     * 不能修改角色和状态
     * 适合测试普通用户权限

### 前端开发指南

1. 接口认证
   ```javascript
   // 登录后获取 token
   const token = response.data.token;
   
   // 设置请求头
   const headers = {
     'Authorization': `Bearer ${token}`,
     'Content-Type': 'application/json'
   };
   
   // 发起请求示例
   fetch('/api/users', {
     headers: headers
   })
   .then(response => response.json())
   .then(data => console.log(data));
   ```

2. 错误处理
   ```javascript
   // 统一错误处理
   function handleApiError(error) {
     if (error.response) {
       switch (error.response.status) {
         case 401:
           // 未登录或 token 过期
           redirectToLogin();
           break;
         case 403:
           // 权限不足
           showError('您没有权限执行此操作');
           break;
         default:
           showError(error.response.data.message || '操作失败');
       }
     }
   }
   ```

3. 用户权限判断
   ```javascript
   // 判断是否为管理员
   function isAdmin(user) {
     return user.role === 'admin';
   }
   
   // 判断是否可以编辑用户
   function canEditUser(currentUser, targetUser) {
     return isAdmin(currentUser) || currentUser.id === targetUser.id;
   }
   ```

### 开发环境配置

1. 本地开发
   ```bash
   # 克隆项目
   git clone https://github.com/wilsonwussd/AdminBK.git
   
   # 安装依赖
   composer install
   
   # 初始化数据库
   php database/init.php
   
   # 启动开发服务器
   php -S 0.0.0.0:8080 -t public
   ```

2. 接口测试
   ```bash
   # 登录测试
   curl -X POST https://xxzcqrmtfyhm.sealoshzh.site/api/login \
     -H "Content-Type: application/json" \
     -d '{"username":"test123","password":"test123"}'
   
   # 获取用户列表 (需要 token)
   curl https://xxzcqrmtfyhm.sealoshzh.site/api/users \
     -H "Authorization: Bearer {your_token}"
   ```

### 注意事项

1. 安全性
   - 所有请求必须使用 HTTPS
   - 不要在前端存储敏感信息
   - 及时清理过期的 token
   - 使用 try-catch 处理所有 API 请求

2. 性能优化
   - 合理使用缓存
   - 避免频繁请求
   - 批量操作使用事务
   - 大量数据使用分页

3. 开发建议
   - 遵循 RESTful API 规范
   - 使用 ESLint 保持代码风格
   - 编写详细的注释
   - 保持代码简洁可维护

4. 常见问题
   - token 过期处理
   - 跨域请求配置
   - 并发请求处理
   - 异常状态恢复 