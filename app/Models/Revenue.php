<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Revenue extends Model
{
    protected $table = 'revenues';

    protected $appends = ['agency_name'];
    protected $fillable = [
        'agency_id', 'book_id', 'booking_revenue', 'agency_discount', 'last_time'
    ];

    protected $hidden = ['agencies'];

    public function agencies(){
        return $this->belongsTo('App\Models\Agency', 'agency_id');
    }

    public function getAgencyNameAttribute(){
        return $this->agencies != null ? $this->agencies->name : '';
    }
}
