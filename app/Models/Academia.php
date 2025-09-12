<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Academia extends Model
{
    protected $fillable = [
        'name',
        'address',
        'latitude',
        'longitude',
    ];
}
