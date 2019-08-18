<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SendAndReceiveAddress extends Model
{
    protected $table = 'send_and_receive_addresses';

    protected $fillable = [
        'user_id',
        'phone',
        'name',
        'full_address',
        'province_id',
        'district_id',
        'ward_id',
        'home_number',
        'type'
    ];

    public function users() {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
    public function provinces() {
        return $this->belongsTo('App\Models\Province', 'province_id');
    }

    public function districts() {
        return $this->belongsTo('App\Models\District', 'district_id');
    }

    public function wards() {
        return $this->belongsTo('App\Models\Ward', 'ward_id');
    }
}
