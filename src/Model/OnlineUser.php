<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OnlineUser extends Model
{
    protected $table = 'online_users';
    public $timestamps = false;
    
    protected $fillable = [
        'user_id',
        'token',
        'ip_address',
        'login_time'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 