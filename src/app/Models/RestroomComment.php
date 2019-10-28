<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestroomComment extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('App\Models\User\User');
    }

    public function restroom()
    {
        return $this->belongsTo('App\Models\Restroom');
    }
}
