<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class UserModel extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'tbl_user';
    protected $primaryKey = 'user_id';
    public $timestamps = false;
    
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

    public function scopeWithCountry($query)
    {
        return $query->leftJoin('tbl_master_country', 'tbl_user.country_id', '=', 'tbl_master_country.country_id')
            ->select('tbl_user.user_id', 'tbl_user.username', 'tbl_user.email', 'tbl_user.country_id', 'tbl_user.role_id', 'tbl_user.user_status', 'tbl_master_country.country_name');
    }
}