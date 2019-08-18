<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProvincialUP extends Model
{
    protected $table = 'provincial_u_ps';

    protected $appends = ['type_name'];

    protected $fillable = [
        'weight',
        'km',
        'price',
        'district_type'
    ];

    public $timestamps = true;

    public function districtTypes()
    {
        return $this->belongsTo('App\Models\DistrictType', 'district_type');
    }

    public function getTypeNameAttribute()
    {
        if (isset($this->districtTypes)) {
            return $this->districtTypes->name != null ? $this->districtTypes->name : '';
        }
        return '';
    }

}
