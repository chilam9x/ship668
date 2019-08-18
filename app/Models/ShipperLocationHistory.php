<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipperLocationHistory extends Model
{
    protected $table = 'shipper_location_histories';

    protected $fillable = [
        'user_id',
        'parent_id',
        'lat',
        'lng',
        'online',
    ];
}
