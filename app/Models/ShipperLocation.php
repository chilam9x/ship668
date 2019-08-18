<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipperLocation extends Model
{
    protected $table = 'shipper_locations';

    protected $fillable = [
        'user_id',
        'lat',
        'lng',
        'online',
    ];

    public function users() {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function history() {
        return $this->hasMany('App\Models\ShipperLocationHistory', 'parent_id');
    }
}
