<?php
namespace App\Middleware;

use App\Model\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class PermissionMiddleware implements MiddlewareInterface
{
    private $permission;

    public function __construct(string $permission)
    {
        $this->permission = $permission;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        $user_id = $request->getAttribute('user_id');
        $role = $request->getAttribute('role');

        if (!$user_id || !$role) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => '未授权访问'
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                          ->withStatus(401);
        }

        // 检查用户角色是否有对应权限
        $user = User::with('role.permissions')->find($user_id);
        if (!$user || !$user->hasPermission($this->permission)) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => '没有访问权限'
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                          ->withStatus(403);
        }

        return $handler->handle($request);
    }
} 