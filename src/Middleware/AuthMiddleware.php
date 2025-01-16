<?php
namespace App\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use App\Model\User;
use App\Model\TokenBlacklist;
use App\Model\OnlineUser;

class AuthMiddleware implements MiddlewareInterface
{
    private $jwt_secret;

    public function __construct()
    {
        $this->jwt_secret = $_ENV['JWT_SECRET'] ?? 'your-secret-key';
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        $authorization = $request->getHeaderLine('Authorization');
        
        if (empty($authorization)) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => '未授权访问'
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                          ->withStatus(401);
        }

        if (!preg_match('/Bearer\s+(.*)$/i', $authorization, $matches)) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => '无效的授权头'
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                          ->withStatus(401);
        }

        $jwt = $matches[1];
        
        try {
            // 检查 token 是否在黑名单中
            if (TokenBlacklist::where('token', $jwt)->exists()) {
                throw new \Exception('Token已失效，请重新登录');
            }

            // 检查 token 是否是当前有效的登录 token
            $onlineUser = OnlineUser::where('token', $jwt)->first();
            if (!$onlineUser) {
                throw new \Exception('Token已失效，请重新登录');
            }

            $token = JWT::decode($jwt, new Key($this->jwt_secret, 'HS256'));
            
            // 检查用户是否存在
            $user = User::find($token->user_id);
            if (!$user) {
                throw new \Exception('用户不存在');
            }

            // 检查用户状态
            if ($user->status !== 1) {
                throw new \Exception('用户账号已被禁用');
            }

            // 检查账号有效期
            $now = new \DateTime();
            if ($user->valid_from && $now < new \DateTime($user->valid_from)) {
                throw new \Exception('账号尚未生效');
            }
            if ($user->valid_until && $now > new \DateTime($user->valid_until)) {
                throw new \Exception('账号已过期');
            }
            
            // 将用户信息添加到请求属性中
            $request = $request->withAttribute('user_id', $token->user_id)
                             ->withAttribute('username', $token->username)
                             ->withAttribute('role', $token->role)
                             ->withAttribute('permissions', $token->permissions);
            
            return $handler->handle($request);
        } catch (\Exception $e) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => $e->getMessage() ?: 'Token无效或已过期'
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                          ->withStatus(401);
        }
    }
} 