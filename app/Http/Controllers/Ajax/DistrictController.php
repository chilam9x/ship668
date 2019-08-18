<?php

namespace App\Http\Controllers\Ajax;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use App\Models\District;

class DistrictController extends Controller
{
    public function updateAllowBooking($districtId) {
    	$district = District::find($districtId);
    	$district->allow_booking = request()->allow_booking;
    	$district->save();
    	return json_encode($district);
    }
}
