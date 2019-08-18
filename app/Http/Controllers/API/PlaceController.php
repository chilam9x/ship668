<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\ApiController;
use App\Models\Province;
use App\Models\District;
use App\Models\Ward;
use App\Models\Agency;

class PlaceController extends ApiController
{
    public function getProvince(){
        $data = Province::select('id', 'name')->orderBy('name', 'asc')->get();
        if (empty($data)){
            return $this->apiError('can not found data');
        }
        return $this->apiOk($data);
    }

    public function getDistrict($id){
        if (!$id){
            return $this->apiError('please enter province id');
        }else{
            $data = District::select('id', 'name')->where('provinceId', $id)->orderBy('name', 'asc')->get();
            if (empty($data)){
                return $this->apiError('can not found data');
            }
            return $this->apiOk($data);
        }
    }

    public function getWard($id){
        if (!$id){
            return $this->apiError('please enter district id');
        }else{
            $data = Ward::select('id', 'name')->where('districtId', $id)->orderBy('name', 'asc')->get();
            if (empty($data)){
                return $this->apiError('can not found data');
            }
            return $this->apiOk($data);
        }
    }

    public function getAgency() {
        $agencies = Agency::where('status', 'active')->select('id', 'name')->get();
        return $this->apiOk($agencies);
    }
}
