<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterRoleModel extends Model
{
    protected $table = 'tbl_master_role';
    protected $primaryKey = 'role_id';
    public $timestamps = false;

    protected $fillable = [
        'role_name',
        'role_status'
    ];
}