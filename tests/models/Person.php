<?php

namespace Test\Models;

use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    protected $table = 'people';

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
