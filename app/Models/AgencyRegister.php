<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgencyRegister extends Model
{
    protected $table = 'agency_registers';

    protected $fillable = [
        'col_id', 'name', 'phone_number', 'province_id', 'district_id', 'ward_id', 'home_number'
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
