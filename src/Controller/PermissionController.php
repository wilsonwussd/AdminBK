<?php
namespace App\Controller;

use App\Model\Permission;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PermissionController
{
    public function list(Request $request, Response $response)
    {
        $permissions = Permission::with('roles')->get();

        $response->getBody()->write(json_encode([
            'success' => true,
            'data' => $permissions
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function create(Request $request, Response $response)
    {
        $data = json_decode($request->getBody()->getContents(), true);
        
        // 验证权限名是否已存在
        if (Permission::where('name', $data['name'])->exists()) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => '权限名已存在'
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                          ->withStatus(400);
        }

        // 创建新权限
        $permission = new Permission();
        $permission->name = $data['name'];
        $permission->description = $data['description'] ?? null;
        $permission->save();

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => '权限创建成功',
            'data' => $permission
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function update(Request $request, Response $response, array $args)
    {
        $permission = Permission::find($args['id']);
        if (!$permission) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => '权限不存在'
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                          ->withStatus(404);
        }

        $data = json_decode($request->getBody()->getContents(), true);

        // 如果要更新权限名，检查是否已存在
        if (isset($data['name']) && $data['name'] !== $permission->name) {
            if (Permission::where('name', $data['name'])->exists()) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => '权限名已存在'
                ]));
                return $response->withHeader('Content-Type', 'application/json')
                              ->withStatus(400);
            }
            $permission->name = $data['name'];
        }

        // 更新描述
        if (isset($data['description'])) {
            $permission->description = $data['description'];
        }

        $permission->save();

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => '权限更新成功',
            'data' => $permission
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function delete(Request $request, Response $response, array $args)
    {
        $permission = Permission::find($args['id']);
        if (!$permission) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => '权限不存在'
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                          ->withStatus(404);
        }

        // 删除权限及其关联
        $permission->roles()->detach();
        $permission->delete();

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => '权限删除成功'
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }
} 