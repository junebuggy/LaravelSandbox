<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    /* Fillable */
    protected $fillable = [
        'name', 'path'
    ];
}
