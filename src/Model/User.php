<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'users';
    protected $fillable = [
        'username',
        'email',
        'password',
        'role',
        'status',
        'last_login_ip',
        'last_login_time',
        'login_attempts',
        'locked_until',
        'avatar'
    ];

    protected $hidden = [
        'password'
    ];

    public function isOnline()
    {
        return OnlineUser::where('user_id', $this->id)->exists();
    }

    public function hasPermission($permission)
    {
        // 如果是管理员，拥有所有权限
        if ($this->role === 'admin') {
            return true;
        }

        // 获取角色
        $role = Role::where('name', $this->role)->first();
        if (!$role) {
            return false;
        }

        return $role->permissions->contains('name', $permission);
    }

    public function getPermissions()
    {
        if ($this->role === 'admin') {
            return Permission::all()->pluck('name')->toArray();
        }

        $role = Role::where('name', $this->role)->first();
        return $role ? $role->permissions->pluck('name')->toArray() : [];
    }
} 