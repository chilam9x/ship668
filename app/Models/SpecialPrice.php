<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpecialPrice extends Model
{
    protected $table = 'special_prices';

    protected $appends = ['type_name', 'from_name', 'to_name'];

    protected $fillable = ['district_type', 'province_from', 'province_to', 'km', 'price'];

    public function districtTypes()
    {
        return $this->belongsTo('App\Models\DistrictType', 'district_type');
    }

    public function provinceFrom()
    {
        return $this->belongsTo('App\Models\Province', 'province_from');
    }
    public function provinceTo()
    {
        return $this->belongsTo('App\Models\Province', 'province_to');
    }

    public function getTypeNameAttribute()
    {
        if (isset($this->districtTypes)) {
            return $this->districtTypes->name != null ? $this->districtTypes->name : '';
        }
        return '';
    }

    public function getFromNameAttribute()
    {
        if (isset($this->provinceFrom)) {
            return $this->provinceFrom->name != null ? $this->provinceFrom->name : '';
        }
        return '';
    }
    public function getToNameAttribute()
    {
        if (isset($this->provinceTo)) {
            return $this->provinceTo->name != null ? $this->provinceTo->name : '';
        }
        return '';
    }


}
