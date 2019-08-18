<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected $table = 'provinces';

    protected $fillable = [
        'name',
        'name_slug'
    ];

    public $timestamps = true;

    static function getProvinceOption($default = false)
    {
        $data = [];
        if ($default){
            $data[-1] = 'Tất cả';
        }
        $province = Province::orderBy('name', 'asc')->get();
        foreach ($province as $p) {
            $data[$p->id] = $p->name;
        }
        return $data;
    }

    public function districts()
    {
        return $this->hasMany('App\Models\District', 'provinceId');
    }

    public function wards()
    {
        return $this->hasMany('App\Models\Ward', 'districtId');
    }

    public function orders()
    {
        return $this->hasMany('App\Models\Order', 'province_id');
    }
}
