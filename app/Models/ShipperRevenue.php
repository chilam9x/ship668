<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipperRevenue extends Model
{
    protected $table = 'shipper_revenues';

    protected $fillable = [
        'shipper_id', 'total_price', 'total_COD', 'price_paid', 'COD_paid'
    ];

    public function users(){
        return $this->belongsTo('App\Models\User', 'shipper_id');
    }
}
