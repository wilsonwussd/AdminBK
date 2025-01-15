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

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function isOnline()
    {
        return OnlineUser::where('user_id', $this->id)->exists();
    }

    public function hasPermission($permission)
    {
        return $this->role && $this->role->hasPermission($permission);
    }
} 