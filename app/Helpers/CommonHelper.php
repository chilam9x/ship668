<?php

namespace App\Helpers;

use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;
use Auth;
use App\Models\Notification;
use App\Models\NotificationUser;
use DB;
use App\Models\User;
use App\Models\Device;
use App\Models\District;
use App\Models\Province;
use App\Models\Ward;
use App\Models\Booking;

class CommonHelper
{
    protected function getLocation($province, $district, $ward, $home_number) {
        $province_name = Province::find($province)->name;
        $district_name = District::find($district)->name;
        $ward_name = Ward::find($ward)->name;
        $mapResults = GoogleMapsHelper::lookUpInfoFromAddress($province_name . ' ' . $district_name . ' ' . $ward_name . ' ' . $home_number);
        return $mapResults;
    }

    public function searchPrice($req)
    {
        $lat_fr = 0;
        $lng_fr = 0;
        $lat_to = 0;
        $lng_to = 0;
        $mapResults_fr = $this->getLocation($req->send_province_id, $req->send_district_id, $req->send_ward_id, $req->send_homenumber);
        if (isset($mapResults_fr->geometry)) {
            if (isset($mapResults_fr->geometry->location)) {
                $lat_fr = $mapResults_fr->geometry->location->lat;
                $lng_fr = $mapResults_fr->geometry->location->lng;
            }
        }
        $mapResults_to = $this->getLocation($req->receive_province_id, $req->receive_district_id, $req->receive_ward_id, $req->receive_homenumber);
        if (isset($mapResults_to->geometry)) {
            if (isset($mapResults_to->geometry->location)) {
                $lat_to = $mapResults_to->geometry->location->lat;
                $lng_to = $mapResults_to->geometry->location->lng;
            }
        }
        $data = (object)[
            "weight" => $req->weight,
            "cod" => $req->COD,
            "receive_type" => $req->receive_type,
            "transport_type" => $req->transport_type,
            "sender" => [
                "address" => [
                    "district" => $req->send_district_id,
                    "homenumber" => $req->send_homenumber,
                    "province" => $req->send_province_id,
                    "ward" => $req->send_ward_id,
                ],
                "location" => [

                    "lat" => $lat_fr,
                    "lng" => $lng_fr
                ]
            ],
            "receiver" => [
                "address" => [
                    "district" => $req->receive_district_id,
                    "homenumber" => $req->receive_homenumber,
                    "province" => $req->receive_province_id,
                    "ward" => $req->receive_ward_id,
                ],
                "location" => [
                    "lat" => $lat_to,
                    "lng" => $lng_to
                ]
            ]
        ];
        $result = $req->type == 'booking' ? Booking::Pricing($data) : Booking::Pricing($data);
        return $result;
    }

    public function checkTransport($req)
    {
        $result = 2;
        if ($req->send_province_id != null && $req->receive_province_id != null && $req->send_district_id != null && $req->receive_district_id != null) {
            if ($req->send_province_id == $req->receive_province_id) {
                $check = Province::find($req->send_province_id);
                if ($check->province_type == 1) {
                    $result = 1;
                } else {
                    $district_send = District::find($req->send_district_id)->district_type;
                    $district_receive = District::find($req->receive_district_id)->district_type;
                    if ($district_send != 5 && $district_receive != 5) {
                        $result = 1;
                    }
                }
            }
        }
        return $result;
    }
}