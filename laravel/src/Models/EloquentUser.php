<?php

namespace Laravel\Models;

use Illuminate\Database\Eloquent\Model;

class EloquentUser extends Model 
{
    protected $table = 'users';
    protected $fillable = ['name', 'email'];
    public $timestamps = false;

    public function posts()
    {
        return $this->hasMany(EloquentPost::class, 'user_id');
    }
}