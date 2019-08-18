<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipperRegister extends Model
{
    protected $table = 'shipper_registers';

    protected $fillable = [
        'birth_day', 'name', 'email', 'province_id', 'district_id',
        'ward_id', 'home_number', 'phone_number','id_number','bank_account',
        'bank_account_number', 'bank_name', 'bank_branch'
    ];

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
