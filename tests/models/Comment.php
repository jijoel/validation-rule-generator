<?php

namespace Test\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $table = 'comments';

    public function author()
    {
        return $this->belongsTo(Person::class);
    }
}
