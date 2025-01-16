<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class TokenBlacklist extends Model
{
    protected $table = 'token_blacklist';
    public $timestamps = false;
    
    protected $fillable = [
        'token',
        'expired_at'
    ];
} 