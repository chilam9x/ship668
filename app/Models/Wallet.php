<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $appends = [
        // 'customer_name', 'customer_phone_number',
    ];

    public function user() {
    	return $this->belongsTo('App\Models\User', 'customer_id');
    }
}
