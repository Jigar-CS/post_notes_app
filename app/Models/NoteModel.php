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
}