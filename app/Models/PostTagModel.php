<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostTagModel extends Model
{
    protected $table = 'tbl_post_tag';
    protected $primaryKey = 'post_tag_id';
    public $timestamps = false;

    protected $fillable = [
        'post_id',
        'note_id',
        'tag_id'
    ];
}