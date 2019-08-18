<?php

namespace App\Helpers;

use Mockery\Exception;
use Requests;


class GoogleMapsHelper
{

    public static function lookUpInfoFromAddress($keyword)
    {
        $results = [];

        $resp = Requests::post(
            'https://maps.googleapis.com/maps/api/geocode/json'
            .'?key=' . env('GOOGLE_API_KEY')
            .'&address=' . urlencode($keyword)
        );


        if ($resp && $resp->status_code == 200){

            try {

                $apiResult = json_decode($resp->body);

                if (!empty($apiResult->status) && $apiResult->status == 'OK') {
                    $results = $apiResult->results[0];
                }

            } catch (Exception $ex){

            }
        }

        return $results;
    }

    public static function lookUpInfoFromLatLng($lat, $lng)
    {
        $results = [];

        $url = 'http://maps.googleapis.com/maps/api/geocode/json?latlng='.trim($lat).','.trim($lng).'&sensor=false';
        $resp = Requests::get($url);
        // dump($resp->body);

        if ($resp && $resp->status_code == 200){

            try {

                $apiResult = json_decode($resp->body);

                if (!empty($apiResult->status) && $apiResult->status == 'OK') {
                    $results = $apiResult->results[0];
                }

            } catch (Exception $ex){
                
            }
        }

        return $results;
    }

    public static function getProvinceNameFromLatLng($lat, $lng)
    {
        $provinceName = 'Hồ Chí Minh';

        $info = self::lookUpInfoFromLatLng($lat, $lng);
        if ($info && $info->address_components) {
            foreach ($info->address_components as $component) {
                if ($component->types && in_array('administrative_area_level_1', $component->types)) {
                    $provinceName = $component->long_name;
                    break;
                }
            }
        }

        return $provinceName;
    }
}
