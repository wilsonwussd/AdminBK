<?php
namespace App\Controller;

use App\Model\OperationLog;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class LogController
{
    public function list(Request $request, Response $response)
    {
        $params = $request->getQueryParams();
        $query = OperationLog::with('user');

        // 按用户ID筛选
        if (isset($params['user_id'])) {
            $query->where('user_id', $params['user_id']);
        }

        // 按操作类型筛选
        if (isset($params['action'])) {
            $query->where('action', $params['action']);
        }

        // 按时间范围筛选
        if (isset($params['start_date'])) {
            $query->where('created_at', '>=', $params['start_date']);
        }
        if (isset($params['end_date'])) {
            $query->where('created_at', '<=', $params['end_date']);
        }

        // 分页
        $page = $params['page'] ?? 1;
        $perPage = $params['per_page'] ?? 10;
        $logs = $query->orderBy('created_at', 'desc')
                     ->skip(($page - 1) * $perPage)
                     ->take($perPage)
                     ->get();

        $total = $query->count();

        $response->getBody()->write(json_encode([
            'success' => true,
            'data' => [
                'logs' => $logs,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'per_page' => $perPage,
                    'total_pages' => ceil($total / $perPage)
                ]
            ]
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }
} 