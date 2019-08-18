<?php

namespace App\Http\Controllers\UI\Ajax;

use App\Helpers\GoogleMapsHelper;
use App\Models\Agency;
use App\Models\Booking;
use App\Models\District;
use App\Models\Province;
use App\Models\ShipperLocation;
use App\Models\ShipperLocationHistory;
use App\Models\Ward;
use function array_merge;
use function dd;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use function number_format;
use function url;
use Validator;

class SettingController extends Controller
{
    protected function getLocation($province, $district, $ward, $home_number)
    {
        $province_name = Province::find($province)->name;
        $district_name = District::find($district)->name;
        $ward_name = Ward::find($ward)->name;
        $mapResults = GoogleMapsHelper::lookUpInfoFromAddress($province_name . ' ' . $district_name . ' ' . $ward_name . ' ' . $home_number);
        return $mapResults;
    }

    public function searchPrice(Request $req)
    {
        $messages = [
            'required' => 'Trường dữ liệu bắt buộc',
            'numeric' => 'Giá trị dữ liệu phải là kiểu số',
            'integer' => 'Giá trị dữ liệu phải là kiểu số nguyên',
            'min' => 'Giá trị dữ liệu tối thiểu bằng 0',
        ];
        $validate = [
            'province_fr' => 'required',
            'district_fr' => 'required',
            'ward_fr' => 'required',
            'home_number_fr' => 'required',
            'province_to' => 'required',
            'district_to' => 'required',
            'ward_to' => 'required',
            'home_number_to' => 'required',
            'weight' => 'required|numeric',
            'cod' => 'required|numeric|min:0'
        ];
        $validator = Validator::make($req->all(), $validate, $messages);
        if ($validator->fails()) {
            if ($req->type == 'booking') {
                return 0;
            } else {
                return $validator->errors();
            }
        }
        $lat_fr = 0;
        $lng_fr = 0;
        $lat_to = 0;
        $lng_to = 0;
        $mapResults_fr = $this->getLocation($req->province_fr, $req->district_fr, $req->ward_fr, $req->home_number_fr);
        if (isset($mapResults_fr->geometry)) {
            if (isset($mapResults_fr->geometry->location)) {
                $lat_fr = $mapResults_fr->geometry->location->lat;
                $lng_fr = $mapResults_fr->geometry->location->lng;
            }
        }
        $mapResults_to = $this->getLocation($req->province_to, $req->district_to, $req->ward_to, $req->home_number_to);
        if (isset($mapResults_to->geometry)) {
            if (isset($mapResults_to->geometry->location)) {
                $lat_to = $mapResults_to->geometry->location->lat;
                $lng_to = $mapResults_to->geometry->location->lng;
            }
        }
        $data = (object)[
            "weight" => $req->weight,
            "cod" => $req->cod,
            "receive_type" => $req->receive_type,
            "transport_type" => $req->transport_type,
            "transport_type_service1" => isset($req->transport_type_service1) ? $req->transport_type_service1 : '',
            "transport_type_service2" => isset($req->transport_type_service2) ? $req->transport_type_service2 : '',
            "transport_type_service3" => isset($req->transport_type_service3) ? $req->transport_type_service3 : '',
            "sender" => [
                "address" => [
                    "district" => $req->district_fr,
                    "homenumber" => $req->homenumber_fr,
                    "province" => $req->province_fr,
                    "ward" => $req->ward_fr,
                ],
                "location" => [

                    "lat" => $lat_fr,
                    "lng" => $lng_fr
                ]
            ],
            "receiver" => [
                "address" => [
                    "district" => $req->district_to,
                    "homenumber" => $req->homenumber_to,
                    "province" => $req->province_to,
                    "ward" => $req->ward_to,
                ],
                "location" => [
                    "lat" => $lat_to,
                    "lng" => $lng_to
                ]
            ]
        ];
        $result = $req->type == 'booking' ? Booking::Pricing($data) : number_format(Booking::Pricing($data));
        return $result;
    }

    public function checkTransport(Request $req)
    {
        $result = 2;
        if ($req->province_fr != null && $req->province_to != null && $req->district_fr != null && $req->district_to != null) {
            if ($req->province_fr == $req->province_to) {
                $check = Province::find($req->province_fr);
                if ($check->province_type == 1) {
                    $result = 1;
                } else {
                    $district_send = District::find($req->district_fr)->district_type;
                    $district_receive = District::find($req->district_to)->district_type;
                    if ($district_send != 5 && $district_receive != 5) {
                        $result = 1;
                    }
                }
            }
        }
        return $result;
    }

    public function searchAgency(Request $request)
    {
        $lat_fr = 0;
        $lng_fr = 0;
        $mapResults_fr = $this->getLocation($request->province_fr, $request->district_fr, $request->ward_fr, $request->home_number_fr);
        if (isset($mapResults_fr->geometry)) {
            if (isset($mapResults_fr->geometry->location)) {
                $lat_fr = $mapResults_fr->geometry->location->lat;
                $lng_fr = $mapResults_fr->geometry->location->lng;
            }
        }
        $distanceQuery = DB::raw("round((6372.795477598 * 2 * ATAN2(SQRT(SIN(RADIANS(lat - {$lat_fr}) / 2) * SIN(RADIANS(lat - {$lat_fr}) / 2) + COS(RADIANS({$lat_fr})) * COS(RADIANS(lat))
         * SIN(RADIANS(lng - {$lng_fr}) / 2) * SIN(RADIANS(lng - {$lng_fr}) / 2)), SQRT(1 - SIN(RADIANS(lat - {$lat_fr}) / 2) * SIN(RADIANS(lat - {$lat_fr}) / 2) + COS(RADIANS({$lat_fr}))
          * COS(RADIANS(lat)) * SIN(RADIANS(lng - {$lng_fr}) / 2) * SIN(RADIANS(lng - {$lng_fr}) / 2))))) AS distance");
        $query = DB::table('agencies')->select('name as Tên', 'phone as Số điện thoại', 'address as Địa chỉ', $distanceQuery)->orderBy('distance', 'asc')->limit(10)->get();
        return $query;
    }

    public function searchBooking($id)
    {
        $completed_at = null;
        $data = DB::table('bookings')->where('uuid', $id)->first();
        $result = [];
        if ($data != null) {
            switch ($data->receive_type) {
                case 1 :
                    $receive_type = 'Lấy hàng tại nhà';
                    break;
                case 2 :
                    $receive_type = 'Giao hàng đến bưu cục (Giảm 7% cước)';
                    break;
                default :
                    $receive_type = '';
            }
            switch ($data->payment_type) {
                case 1 :
                    $payment_type = 'Người gửi trả cước';
                    break;
                case 2 :
                    $payment_type = 'Người nhận trả cước';
                    break;
                default :
                    $payment_type = '';
            }
            switch ($data->transport_type) {
                case 1 :
                    // $transport_type = 'Giao nội trong thành phố (Giao ngay)';
                    $transport_type = 'Giao chuẩn';
                    break;
                case 2 :
                    $transport_type = 'Giao tiết kiệm';
                    break;
                case 3 :
                    // $transport_type = 'Giao nhanh';
                    $transport_type = 'Giao siêu tốc';
                    break;
                case 4 :
                    $transport_type = 'Giao COD';
                    break;
                default :
                    $transport_type = '';
            }
            switch ($data->status) {
                case 'new' :
                    $status = 'Mới';
                    break;
                case 'taking' :
                    $status = 'Đang lấy';
                    break;
                case 'sending' :
                    $status = 'Đang giao';
                    break;
                case 'completed' :
                    $completed_at = ['completed_at' => $data->completed_at];
                    $status = 'Hoàn tất';
                    break;
                case 'return' :
                    $status = 'Trả lại';
                    $completed_at = ['return_at' => $data->completed_at];
                    break;
                case 'move' :
                    $status = 'Chuyển kho';
                    break;
                case 'cancel' :
                    $completed_at = ['cancel_at' => $data->updated_at];
                    $status = 'Hủy';
                    break;
                default :
                    $status = '';
            }
            $result = [
                'name' => $data->name,
                'created_at' => $data->created_at,
                'uuid' => $data->uuid,
                'status' => $status,
                'weight' => $data->weight,
                'transport_type' => $transport_type,
                'cod' => number_format($data->COD) . ' vnđ',
                'payment_type' => $payment_type,
                'other_note' => $data->other_note,
                'note' => $data->note,
                'price' => number_format($data->price) . ' vnđ',
                'phone_number_to' => $data->receive_phone,
                'name_to' => $data->receive_name,
                'address_fr' => $data->send_full_address,
                'address_to' => $data->receive_full_address,
                'receive_type' => $receive_type,
                'name_fr' => $data->send_name,
                'phone_number_fr' => $data->send_phone,
                'paid' =>  number_format($data->paid) . ' vnđ',
                'incurred' =>  number_format($data->incurred) . ' vnđ',
            ];
            if ($completed_at != null){
                $result = array_merge($result, $completed_at);
            }
        }
        return !empty($result) ? $result : 'Đơn hàng không tồn tại';
    }

    public function shipperLocation(Request $req)
    {
        $data = ShipperLocation::where('user_id', $req->id)->first();
        if ($data == null){
            $data = new ShipperLocation();
            $data->user_id = $req->id;
        }
        // $data->lat = $req->location['lat'];
        // $data->lng = $req->location['lng'];
        $data->lat = $req->lat;
        $data->lng = $req->lng;
        $data->save();
        $check = ShipperLocationHistory::where('parent_id', $data->id)->orderBy('id', 'desc')->first();
        $flag = true;
        if($check != null){
            // if ($check->lat == $req->location['lat'] && $check->lng == $req->location['lng']){
            if ($check->lat == $req->lat && $check->lng == $req->lng){
                $flag = false;
            }
        }
        if ($flag){
            $history = new ShipperLocationHistory();
            $history->parent_id = $data->id;
            $history->user_id = $data->user_id;
            $history->lat = $data->lat;
            $history->lng = $data->lng;
            $history->save();
        }
        $location = ShipperLocation::with('users')->where('online', 1);
        if (Auth::user()->role == 'collaborators'){
            $scope = Collaborator::where('user_id', Auth::user()->id)->pluck('agency_id');
            $user = User::with('revenues','shipper')->where('role', 'shipper')->where('delete_status', 0)->where('status', 'active')->whereHas('shipper', function ($query) use ($scope) {
                    $query->whereIn('agency_id', $scope);
                })->pluck('id');
            $location = $location->whereIn('user_id', $user);
        }
        $location = $location->whereHas('users', function($query){
            $query->where('delete_status', 0)->where('status', 'active');
        })->get();
        $result = [];
        if (isset($location)){
            foreach ($location as $l){
                $name = $l->users->name == null ? "Chưa có tên" : $l->users->name;
                $result[] = [
                    '<div style="min-width: 250px; max-width: 250px; max-height: 80px; float: left">
                        <div style="width: 30%; float: left">
                            <img src="'.url($l->users->avatar != null ? '/'.$l->users->avatar : asset('/img/default-avatar.jpg')).'" width="100%">
                        </div>
                        <div style="width: 65%; float: left; margin: 15px 0px 0px 5px">
                            <strong>Tên: '.$name.'</strong><br/><br/>
                           <b>Phone: '.$l->users->phone_number.'</b>
                         </div>     
                    </div>',
                    $l->lat,
                    $l->lng,
                    $l->user_id ];
            }
        }
        return $result;
    }

    public function getProvince() {
        $provinces = Province::where('active', 1)->orderBy('name', 'ASC')->get();
        return response()->json($provinces);
    }

    public function getDistrict() {
        $db = District::orderBy('name', 'ASC');
        if (isset(request()->province_id) && request()->province_id > 0) {
            $db = $db->where('provinceId', request()->province_id);
        }
        $districts = $db->get();
        return response()->json($districts);
    }

    public function getWard() {
        $db = Ward::orderBy('name', 'ASC');
        if (isset(request()->district_id) && request()->district_id > 0) {
            $db = $db->where('districtId', request()->district_id);
        }
        $wards = $db->get();
        return response()->json($wards);
    }

    public function getBookingByReceiver() {
        if (!Auth::check()) {
            return response()->json('Bạn chưa đăng nhập!');
        }
        $db = Booking::where('sender_id', request()->user()->id);

        if (isset(request()->receive_phone) && !empty(request()->receive_phone)) {
            $db = $db->where('receive_phone', 'LIKE', '%' . request()->receive_phone . '%');
        }

        $bookings = $db->orderBy('created_at', 'DESC')->get();
        return response()->json($bookings);
    }

    public function getLastBooking() {
        if (!Auth::check()) {
            return response()->json('Bạn chưa đăng nhập!');
        }
        $lastBooking = Booking::where('sender_id', request()->user()->id)->orderBy('created_at', 'DESC')->first();
        return response()->json($lastBooking);
    }
}
