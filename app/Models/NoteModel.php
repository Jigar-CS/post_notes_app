<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoteModel extends Model
{
    protected $table = 'tbl_note';
    protected $primaryKey = 'note_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'created_by',
        'updated_by',
        'category_id',
        'title',
        'content',
        'note_status'
    ];

    public function scopeWithJoins($query)
    {
        return $query
            ->leftJoin('tbl_category','tbl_note.category_id','=','tbl_category.category_id')
            ->leftJoin('tbl_user as owner','tbl_note.user_id','=','owner.user_id')
            ->leftJoin('tbl_user as creator','tbl_note.created_by','=','creator.user_id')
            ->leftJoin('tbl_user as updater','tbl_note.updated_by','=','updater.user_id')
            ->select('tbl_note.*','tbl_category.category_name','owner.username as username','creator.username as created_by_name','updater.username as updated_by_name','creator.user_id as created_by_id','updater.user_id as updated_by_id');
    }
}