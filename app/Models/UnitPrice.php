<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitPrice extends Model
{
    protected $table = 'unit_prices';

    protected $fillable = [
        'type',
        'price',
    ];
}
