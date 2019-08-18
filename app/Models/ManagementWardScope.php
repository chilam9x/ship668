<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManagementWardScope extends Model
{
    protected $table = 'management_ward_scopes';

    protected $fillable = [
        'agency_id',
        'ward_id',
    ];

    public function wards() {
        return $this->belongsTo('App\Models\Ward', 'ward_id');
    }

    public function agency() {
        return $this->belongsTo('App\Models\Agency', 'agency_id');
    }

}
