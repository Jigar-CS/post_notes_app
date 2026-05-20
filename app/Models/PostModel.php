<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostModel extends Model
{
    protected $table = 'tbl_post';
    protected $primaryKey = 'post_id';
    
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'created_by',
        'updated_by',
        'category_id',
        'title',
        'content',
        'featured_image',
        'is_public',
        'post_status'
    ];
}