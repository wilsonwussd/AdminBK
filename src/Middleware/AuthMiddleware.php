<?php
namespace App\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

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
            $token = JWT::decode($jwt, new Key($this->jwt_secret, 'HS256'));
            
            // 将用户ID和角色信息添加到请求属性中
            $request = $request->withAttribute('user_id', $token->user_id)
                             ->withAttribute('role', $token->role);
            
            return $handler->handle($request);
        } catch (\Exception $e) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Token无效或已过期'
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                          ->withStatus(401);
        }
    }
} 