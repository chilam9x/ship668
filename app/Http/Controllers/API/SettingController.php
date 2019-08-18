<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\ApiController;
use App\Models\Version;
use Validator;
use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends ApiController
{
    
    public function getTransaction(Request $req){
        $query = Setting::whereIn('type',['transport_type','transport_type_des']);
        $results = $query->get();
        $data = [];
        foreach($results as $item){
//            if($item['type'] == 'transport_type_des'){
//                if($item['key'] == 'transport_type_des1'){
//                     $item['key'] = 1;
//                }else{
//                     $item['key'] = 3;
//                }
//                            }
            if(empty($data[$item['type']])){
                $data[$item['type']] = [];
            }
            $data[$item['type']][] = $item;
        }
        return $this->apiOk($data);
    } 
    public function getVersion(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'category' => 'required',
            'type' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->apiError($validator->errors()->first());
        }
        $query = Version::where([]);
        if (isset($req->category)) {
            if ($req->category == 'customer') {
                $query->where('category', 'customer');
            }
            if ($req->category == 'shipper') {
                $query->where('category', 'shipper');
            }
            if ($req->type == 'ios') {
                $query->where('device_type', 'ios');
            }
            if ($req->type == 'android') {
                $query->where('device_type', 'android');
            }
        }
        return $this->apiOk($query->first());
    }
}
