<?php

namespace Laravel\Models;

use Illuminate\Database\Eloquent\Model;

class EloquentPost extends Model 
{
    protected $table = 'posts';
    protected $fillable = ['user_id', 'title', 'content'];
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(EloquentUser::class, 'user_id');
    }
}