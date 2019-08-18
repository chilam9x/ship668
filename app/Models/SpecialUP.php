<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpecialUP extends Model
{
    protected $table = 'special_u_ps';

    protected $appends = ['from_name', 'to_name'];

    protected $fillable = [
        'weight',
        'km',
        'price',
        'province_from',
        'province_to'
    ];

    public $timestamps = true;

    public function provinceFrom()
    {
        return $this->belongsTo('App\Models\Province', 'province_from');
    }
    public function provinceTo()
    {
        return $this->belongsTo('App\Models\Province', 'province_to');
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
