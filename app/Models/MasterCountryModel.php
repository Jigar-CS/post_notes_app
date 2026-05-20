<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterCountryModel extends Model
{
    protected $table = 'tbl_master_country';
    protected $primaryKey = 'country_id';
    public $timestamps = false;

    protected $fillable = [
        'country_name',
        'country_status'
    ];
}