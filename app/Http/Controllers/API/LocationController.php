<?php

namespace App\Http\Controllers\Api;

use App\Models\Shipper;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\ApiController;
use function is;
use JWTAuth;
use App\Models\User;
use JWTAuthException;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Models\District;
use App\Helpers\GoogleMapsHelper;
use App\Events\GetLocationShipper;
use LRedis;
use App\Models\ShipperLocation;

class LocationController extends ApiController
{
    public function location(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'lat' => 'required',
            'lng' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->apiError($validator->errors()->first());
        }
        $id = isset($req->user()->id) ? $req->user()->id : $req->id;
        // $shipper = Shipper::where('user_id', $id)->first();
        $data = array(
            'lat' => $req->lat,
            'lng' => $req->lng,
            'id' => $id
        );

        $shipperLocation = ShipperLocation::where('user_id', $id)->first();
        if (empty($shipperLocation)) {
            $shipperLocation = new ShipperLocation;
        }
        $shipperLocation->user_id = $id;
        $shipperLocation->lat = $req->lat;
        $shipperLocation->lng = $req->lng;
        $status = $shipperLocation->save();

        if ($status) {
            event(new GetLocationShipper($data));
            return $this->apiOk($shipperLocation);
        } else {
            return $this->apiError('Updated shipper fail!');    
        }
        
    }

}  
