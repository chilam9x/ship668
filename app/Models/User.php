<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use JWTAuth;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'avatar', 'username', 'birth_day', 'name', 'email', 'password', 'province_id', 'district_id',
        'ward_id', 'home_number', 'phone_number', 'id_number', 'role', 'status', 'bank_account',
        'bank_account_number', 'bank_name', 'bank_branch', 'fb_id', 'gg_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token'
    ];

    public function generateJwt()
    {
        try {

            $token = JWTAuth::fromUser($this, ['id' => $this->authToken]);

        } catch (JWTException $e) {
            return false;
        }

        //dump($token);
        return $token;
    }

    // public function notification()
    // {
    //     return $this->hasMany('App\Models\Notification', 'user_id', 'id');
    // }

    public function provinces()
    {
        return $this->belongsTo('App\Models\Province', 'province_id');
    }

    public function districts()
    {
        return $this->belongsTo('App\Models\District', 'district_id');
    }

    public function wards()
    {
        return $this->belongsTo('App\Models\Ward', 'ward_id');
    }

    public function shipper()
    {
        return $this->hasOne('App\Models\Shipper', 'user_id');
    }

    public function revenues()
    {
        return $this->hasOne('App\Models\ShipperRevenue', 'shipper_id');
    }

    // public function deviceIos()
    // {
    //     return $this->hasMany('App\Models\Device', 'user_id')->where('device_type', 'ios');
    // }

    // public function deviceAndroid()
    // {
    //     return $this->hasMany('App\Models\Device', 'user_id')->where('device_type', 'android');
    // }
}
