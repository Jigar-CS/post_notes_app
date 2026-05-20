<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserModel extends Model
{
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
}