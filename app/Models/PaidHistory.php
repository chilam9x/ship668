<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaidHistory extends Model
{
    protected $table = 'paid_histories';

    protected $appends = ['creator', 'agency_name', 'agency_address', 'agency_phone'];

    protected $fillable = [
        'id',
        'agency_id',
        'user_create',
        'paid_type',
        'value',
        'status'
    ];

    public function agencies()
    {
        return $this->belongsTo('App\Models\Agency', 'agency_id');
    }
    public function users()
    {
        return $this->belongsTo('App\Models\User', 'user_create');
    }

    public function getCreatorAttribute()
    {
        $data = '';
        if ($this->users != null){
            $data = $this->users->name != null ? $this->users->name : '';
        }
        return $data;
    }

    public function getAgencyNameAttribute()
    {
        $data = '';
        if ($this->agencies != null){
            $data = $this->agencies->name != null ? $this->agencies->name : '';
        }
        return $data;
    }

    public function getAgencyAddressAttribute()
    {
        $data = '';
        if ($this->agencies != null){
            $data = $this->agencies->address != null ? $this->agencies->address : '';
        }
        return $data;
    }

    public function getAgencyPhoneAttribute()
    {
        $data = '';
        if ($this->agencies != null){
            $data = $this->agencies->phone != null ? $this->agencies->phone : '';
        }
        return $data;
    }
}
