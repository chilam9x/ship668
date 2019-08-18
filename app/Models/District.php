<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $table = 'districts';

    protected $fillable = [
        'provinceId',
        'district_type',
        'name',
        'name_slug'
    ];

    public $timestamps = true;

    public function province()
    {
        return $this->hasOne('App\Models\Province', 'id', 'provinceId');
    }

    public function wards()
    {
        return $this->hasMany('App\Models\Ward', 'districtId');
    }

    public function orders()
    {
        return $this->hasMany('App\Models\Order', 'district_id');
    }
}
