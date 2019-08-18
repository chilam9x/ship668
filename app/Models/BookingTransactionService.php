<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;
use function abs;
use App\Helpers\GoogleMapsHelper;
use function dd;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use function in_array;
use function print_r;
use function var_dump;
use App\Models\District;
use App\Models\ProvincialUP;
use App\Models\Setting;
use Auth;
class BookingTransactionService extends Model {

    protected $table = 'booking_transport_services';
    protected $fillable = [
        'user_id', 'book_id', 'key', 'price', 'service_id', 'created_at'
    ];
    
    public function service(){
        return $this->hasOne('App\Models\Setting', 'id', 'service_id')->select('name','id');
    }
     protected $appends = [
        'name'
    ];

    public function getNameAttribute() {
        if (isset($this->service)) {
            $name =  $this->service->name;
            unset($this->service);
            return $name;
        }
        return '';
    }



}
