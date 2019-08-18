<?php

namespace App\Models;

use function dd;
use function dump;
use Illuminate\Database\Eloquent\Model;
use function is;
use function number_format;

class Agency extends Model
{
    protected $table = 'agencies';

    protected $appends = ['collaborator_name', 'total_revenue', 'agency_discount', 'turnover_paid', 'discount_paid'];

    protected $fillable = [
        'name',
        'full_address',
        'province_id',
        'district_id',
        'ward_id',
        'home_number',
        'lat',
        'lng',
        'status'
    ];

    protected $hidden = ['revenues', 'liabilities'];

    public function provinces() {
        return $this->belongsTo('App\Models\Province', 'province_id');
    }

    public function districts() {
        return $this->belongsTo('App\Models\District', 'district_id');
    }

    public function wards() {
        return $this->belongsTo('App\Models\Ward', 'ward_id');
    }

    public function managementScope() {
        return $this->hasMany('App\Models\ManagementScope', 'agency_id');
    }

    public function collaborators() {
        return $this->hasMany('App\Models\Collaborator', 'agency_id');
    }

    public function shippers() {
        return $this->hasMany('App\Models\Shipper', 'user_id');
    }

    public function revenues() {
        return $this->hasMany('App\Models\Revenue', 'agency_id');
    }

    public function liabilities() {
        return $this->hasOne('App\Models\Liabilities', 'agency_id');
    }

    public function getTotalRevenueAttribute() {
       $total_revenue = $this->revenues->sum('booking_revenue');
       return number_format($total_revenue);
    }

    public function getAgencyDiscountAttribute() {
       $agency_discount = $this->revenues->sum('agency_discount');
       return number_format($agency_discount);
    }


    public function getDiscountPaidAttribute() {
        $discount_paid = $this->liabilities != null ? $this->liabilities->discount_paid : 0;
        return number_format($discount_paid);
    }


    public function getTurnoverPaidAttribute() {
       $turnover_paid = $this->liabilities != null ? $this->liabilities->turnover_paid : 0;
       return number_format($turnover_paid);
    }

    public function getCollaboratorNameAttribute()
    {
        $data = [];
        $coll = Collaborator::where('agency_id', $this->id)->get();
        if (isset($coll)){
            foreach ($coll as $c){
                $name = User::where('id', $c->user_id)->first()->name;
                $data[] = $name;
            }
        }
        return !empty($data) ? implode(', ', $data) : '';
    }

}

