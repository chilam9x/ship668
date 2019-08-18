<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManagementScope extends Model
{
    protected $table = 'management_scopes';

    protected $fillable = [
        'agency_id',
        'district_id',
    ];

    public function district() {
        return $this->belongsTo('App\Models\District', 'district_id');
    }

    public function agency() {
        return $this->belongsTo('App\Models\Agency', 'agency_id');
    }

}
