<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Restroom extends Model
{
    protected $guarded = [];

    public function images()
    {
        return $this->hasMany('App\Models\RestroomImage');
    }
}
