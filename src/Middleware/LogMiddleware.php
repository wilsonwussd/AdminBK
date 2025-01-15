<?php
namespace App\Middleware;

use App\Model\OperationLog;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class LogMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        $response = $handler->handle($request);
        
        // 获取当前用户ID
        $userId = $request->getAttribute('user_id');
        if (!$userId) {
            return $response;
        }

        // 获取请求信息
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();
        
        // 定义需要记录日志的操作
        $logActions = [
            'POST /api/login' => '用户登录',
            'POST /api/logout' => '用户登出',
            'POST /api/users' => '创建用户',
            'PUT /api/users' => '更新用户',
            'DELETE /api/users' => '删除用户'
        ];

        $key = $method . ' ' . preg_replace('/\/\d+/', '', $path);
        if (isset($logActions[$key])) {
            // 记录操作日志
            OperationLog::create([
                'user_id' => $userId,
                'action' => $logActions[$key],
                'description' => json_encode([
                    'method' => $method,
                    'path' => $path,
                    'ip_address' => $_SERVER['REMOTE_ADDR'],
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
                ]),
                'ip_address' => $_SERVER['REMOTE_ADDR']
            ]);
        }

        return $response;
    }
} 