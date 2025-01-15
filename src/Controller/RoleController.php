<?php
namespace App\Controller;

use App\Model\Role;
use App\Model\Permission;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RoleController
{
    public function list(Request $request, Response $response)
    {
        $roles = Role::with('permissions')->get();

        $response->getBody()->write(json_encode([
            'success' => true,
            'data' => $roles
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function create(Request $request, Response $response)
    {
        $data = json_decode($request->getBody()->getContents(), true);
        
        // 验证角色名是否已存在
        if (Role::where('name', $data['name'])->exists()) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => '角色名已存在'
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                          ->withStatus(400);
        }

        // 创建新角色
        $role = new Role();
        $role->name = $data['name'];
        $role->description = $data['description'] ?? null;
        $role->save();

        // 分配权限
        if (isset($data['permissions'])) {
            $permissions = Permission::whereIn('name', $data['permissions'])->get();
            $role->permissions()->attach($permissions);
        }

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => '角色创建成功',
            'data' => $role->load('permissions')
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function update(Request $request, Response $response, array $args)
    {
        $role = Role::find($args['id']);
        if (!$role) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => '角色不存在'
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                          ->withStatus(404);
        }

        $data = json_decode($request->getBody()->getContents(), true);

        // 如果要更新角色名，检查是否已存在
        if (isset($data['name']) && $data['name'] !== $role->name) {
            if (Role::where('name', $data['name'])->exists()) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => '角色名已存在'
                ]));
                return $response->withHeader('Content-Type', 'application/json')
                              ->withStatus(400);
            }
            $role->name = $data['name'];
        }

        // 更新描述
        if (isset($data['description'])) {
            $role->description = $data['description'];
        }

        // 更新权限
        if (isset($data['permissions'])) {
            $permissions = Permission::whereIn('name', $data['permissions'])->get();
            $role->permissions()->sync($permissions);
        }

        $role->save();

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => '角色更新成功',
            'data' => $role->load('permissions')
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function delete(Request $request, Response $response, array $args)
    {
        $role = Role::find($args['id']);
        if (!$role) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => '角色不存在'
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                          ->withStatus(404);
        }

        // 删除角色及其关联的权限
        $role->permissions()->detach();
        $role->delete();

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => '角色删除成功'
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }
} 