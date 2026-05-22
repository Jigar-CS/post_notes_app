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

    public function scopeWithJoins($query)
    {
        return $query
            ->leftJoin('tbl_user','tbl_post.user_id','=','tbl_user.user_id')
            ->leftJoin('tbl_category','tbl_post.category_id','=','tbl_category.category_id')
            ->leftJoin('tbl_user as creator','tbl_post.created_by','=','creator.user_id')
            ->leftJoin('tbl_user as updater','tbl_post.updated_by','=','updater.user_id')
            ->select('tbl_post.*','tbl_user.username','tbl_category.category_name','creator.username as created_by_name','updater.username as updated_by_name','creator.user_id as created_by_id','updater.user_id as updated_by_id');
    }
}