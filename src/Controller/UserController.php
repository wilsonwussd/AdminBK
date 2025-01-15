<?php
namespace App\Controller;

use App\Model\User;
use App\Model\OnlineUser;
use Firebase\JWT\JWT;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UserController
{
    private $jwt_secret;
    private $max_login_attempts = 5; // 最大登录尝试次数
    private $lockout_time = 1800;    // 锁定时间（秒）

    public function __construct()
    {
        $this->jwt_secret = $_ENV['JWT_SECRET'] ?? 'your-secret-key';
    }

    public function login(Request $request, Response $response)
    {
        $data = json_decode($request->getBody()->getContents(), true);
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        $user = User::where('username', $username)->first();
        
        if (!$user) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => '用户名或密码错误'
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                          ->withStatus(401);
        }

        // 检查账户是否被锁定
        if ($user->status === 0) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => '账户已被禁用'
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                          ->withStatus(401);
        }

        // 检查是否被锁定
        if ($user->locked_until && $user->locked_until > time()) {
            $wait_time = $user->locked_until - time();
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => "账户已被锁定，请在 {$wait_time} 秒后重试"
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                          ->withStatus(401);
        }

        // 验证密码
        if (!password_verify($password, $user->password)) {
            // 增加登录失败次数
            $user->login_attempts = ($user->login_attempts ?? 0) + 1;
            
            // 如果超过最大尝试次数，锁定账户
            if ($user->login_attempts >= $this->max_login_attempts) {
                $user->locked_until = time() + $this->lockout_time;
                $user->login_attempts = 0;
                $user->save();
                
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => "登录失败次数过多，账户已被锁定 {$this->lockout_time} 秒"
                ]));
                return $response->withHeader('Content-Type', 'application/json')
                              ->withStatus(401);
            }
            
            $user->save();
            
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => '用户名或密码错误'
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                          ->withStatus(401);
        }

        // 登录成功，重置登录失败次数
        $user->login_attempts = 0;
        $user->locked_until = null;

        // 生成JWT token
        $token = JWT::encode([
            'user_id' => $user->id,
            'username' => $user->username,
            'role' => $user->role,
            'exp' => time() + 3600
        ], $this->jwt_secret, 'HS256');

        // 更新用户登录信息
        $user->last_login_ip = $_SERVER['REMOTE_ADDR'];
        $user->last_login_time = date('Y-m-d H:i:s');
        $user->save();

        // 记录在线用户
        OnlineUser::updateOrCreate(
            ['user_id' => $user->id],
            [
                'token' => $token,
                'ip_address' => $_SERVER['REMOTE_ADDR'],
                'login_time' => date('Y-m-d H:i:s')
            ]
        );

        $response->getBody()->write(json_encode([
            'success' => true,
            'token' => $token,
            'user' => $user
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function logout(Request $request, Response $response)
    {
        $user_id = $request->getAttribute('user_id');
        OnlineUser::where('user_id', $user_id)->delete();

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => '退出登录成功'
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function list(Request $request, Response $response)
    {
        $users = User::all()->map(function ($user) {
            $user->is_online = $user->isOnline();
            return $user;
        });

        $response->getBody()->write(json_encode([
            'success' => true,
            'data' => $users
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function create(Request $request, Response $response)
    {
        $data = json_decode($request->getBody()->getContents(), true);
        
        // 验证用户名是否已存在
        if (User::where('username', $data['username'])->exists()) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => '用户名已存在'
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                          ->withStatus(400);
        }

        // 创建新用户
        $user = new User();
        $user->username = $data['username'];
        $user->password = password_hash($data['password'], PASSWORD_DEFAULT);
        $user->email = $data['email'] ?? null;
        $user->role = $data['role'] ?? 'user';
        $user->status = 1;
        $user->save();

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => '用户创建成功',
            'data' => $user
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function update(Request $request, Response $response, array $args)
    {
        $user = User::find($args['id']);
        if (!$user) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => '用户不存在'
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                          ->withStatus(404);
        }

        $data = json_decode($request->getBody()->getContents(), true);

        // 如果要更新用户名，检查是否已存在
        if (isset($data['username']) && $data['username'] !== $user->username) {
            if (User::where('username', $data['username'])->exists()) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => '用户名已存在'
                ]));
                return $response->withHeader('Content-Type', 'application/json')
                              ->withStatus(400);
            }
            $user->username = $data['username'];
        }

        // 更新其他字段
        if (isset($data['password'])) {
            $user->password = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        if (isset($data['email'])) {
            $user->email = $data['email'];
        }
        if (isset($data['role'])) {
            $user->role = $data['role'];
        }
        if (isset($data['status'])) {
            $user->status = $data['status'];
        }

        $user->save();

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => '用户更新成功',
            'data' => $user
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function delete(Request $request, Response $response, array $args)
    {
        $user = User::find($args['id']);
        if (!$user) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => '用户不存在'
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                          ->withStatus(404);
        }

        // 删除用户的在线记录
        OnlineUser::where('user_id', $user->id)->delete();
        
        // 删除用户
        $user->delete();

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => '用户删除成功'
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }
} 