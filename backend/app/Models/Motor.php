<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Motor extends Model
{
    protected $fillable = [
        'name',
        'brand',
        'plate_number',
        'price_per_day',
        'status',
    ];
}
