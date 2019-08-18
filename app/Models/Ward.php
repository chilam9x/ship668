<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ward extends Model
{
    protected $table = 'wards';

    protected $fillable = [
        'provinceId',
        'districtId',
        'name',
        'name_slug'
    ];

    public $timestamps = true;

    public static function boot()
    {
        parent::boot();
    }

    public function province()
    {
        return $this->hasOne('App\Models\Province', 'id', 'provinceId');
    }

    public function district()
    {
        return $this->hasOne('App\Models\District', 'id', 'districtId');
    }

    public function orders()
    {
        return $this->hasMany('App\Models\Order', 'ward_id');
    }

}
