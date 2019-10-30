<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestroomRating extends Model
{
    protected $guarded = [];

    public function restroom()
    {
        return $this->belongsTo('App\Models\Restroom');
    }
}
