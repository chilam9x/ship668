<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipper extends Model
{
    protected $table = 'shippers';

    protected $appends = ['shipper_name'];

    protected $fillable = [
        'user_id', 'agency_id', 'lat', 'lng'
    ];

    public function getShipperNameAttribute()
    {
        if (isset($this->users)) {
            return $this->users->name != null ? $this->users->name : '';
        }
        return '';
    }

    public function users(){
        return $this->belongsTo('App\Models\User', 'user_id');
    }
    public function agencies(){
        return $this->belongsTo('App\Models\User', 'agency_id');
    }
}
