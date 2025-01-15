<?php
namespace App\Controller;

use App\Model\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;

class AvatarController
{
    private $uploadDirectory;

    public function __construct()
    {
        $this->uploadDirectory = __DIR__ . '/../../public/uploads/avatars';
    }

    public function upload(Request $request, Response $response)
    {
        $user_id = $request->getAttribute('user_id');
        $user = User::find($user_id);

        if (!$user) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => '用户不存在'
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                          ->withStatus(404);
        }

        $uploadedFiles = $request->getUploadedFiles();
        
        if (empty($uploadedFiles['avatar'])) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => '没有上传文件'
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                          ->withStatus(400);
        }

        $avatar = $uploadedFiles['avatar'];
        
        if ($avatar->getError() !== UPLOAD_ERR_OK) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => '文件上传失败'
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                          ->withStatus(400);
        }

        // 验证文件类型
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($avatar->getClientMediaType(), $allowedTypes)) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => '不支持的文件类型'
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                          ->withStatus(400);
        }

        // 生成唯一文件名
        $extension = pathinfo($avatar->getClientFilename(), PATHINFO_EXTENSION);
        $basename = bin2hex(random_bytes(8));
        $filename = sprintf('%s.%0.8s', $basename, $extension);

        try {
            $avatar->moveTo($this->uploadDirectory . DIRECTORY_SEPARATOR . $filename);

            // 删除旧头像
            if ($user->avatar) {
                $oldAvatarPath = $this->uploadDirectory . DIRECTORY_SEPARATOR . basename($user->avatar);
                if (file_exists($oldAvatarPath)) {
                    unlink($oldAvatarPath);
                }
            }

            // 更新用户头像
            $user->avatar = '/uploads/avatars/' . $filename;
            $user->save();

            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => '头像上传成功',
                'data' => [
                    'avatar' => $user->avatar
                ]
            ]));
            return $response->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => '文件保存失败'
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                          ->withStatus(500);
        }
    }
} 