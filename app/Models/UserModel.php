<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class UserModel extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'tbl_user';
    protected $primaryKey = 'user_id';
    public $timestamps = false; // We aren't using default created_at/updated_at columns here

    protected $fillable = [
        'username',
        'email',
        'password',
        'country_id',
        'role_id',
        'user_status'
    ];

    // Hide sensitive fields from API responses
    protected $hidden = [
        'password'
    ];
}