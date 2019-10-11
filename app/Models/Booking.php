<?php

namespace App\Models;

use App\Helpers\GoogleMapsHelper;
use App\Models\BookingTransactionService;
use App\Models\District;
use App\Models\ProvincialUP;
use App\Models\Setting;
use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Booking extends Model
{
    protected $table = 'bookings';

    protected $appends = [
        'sender_name', 'sender_phone', 'category_name', 'sender_address', 'receiver_name', 'receiver_phone', 'receiver_address',
        'current_agency_name', 'first_agency_name', 'last_agency_name', 'shipper_name', 'shipper_info', 'shipper_phone', 'action_status',
    ];

    protected $fillable = [
        'uuid', 'sender_id', 'receiver_id', 'send_province_id', 'send_district_id', 'send_ward_id', 'send_homenumber', 'send_full_address', 'send_name',
        'send_phone', 'send_lat', 'send_lng', 'receive_province_id', 'receive_district_id', 'receive_ward_id', 'receive_homenumber', 'receive_full_address', 'receive_name',
        'receive_phone', 'receive_lat', 'receive_lng', 'price', 'weight', 'length', 'width', 'height', 'COD', 'sub_status',
        'transport_type', 'receive_type', 'payment_type', 'other_note', 'note', 'status', 'COD_status', 'payment_date', 'updated_by', 'owe',

    ];

    protected $hidden = [
        'currentAgencies', 'firstAgencies', 'lastAgencies', 'receiver', 'send_provinces', 'send_districts', 'send_wards',
        'receive_provinces', 'receive_districts', 'receive_wards', 'deliveries', 'sender',
    ];

    protected static function getLocation($province, $district, $ward, $home_number)
    {
        $province_name = Province::find($province)->name;
        $district_name = District::find($district)->name;
        $ward_name = Ward::find($ward)->name;
        $mapResults = GoogleMapsHelper::lookUpInfoFromAddress($province_name . ' ' . $district_name . ' ' . $ward_name . ' ' . $home_number);
        return $mapResults;
    }

    public function sender()
    {
        return $this->belongsTo('App\Models\User', 'sender_id');
    }

    public function currentAgencies()
    {
        return $this->belongsTo('App\Models\Agency', 'current_agency');
    }

    public function firstAgencies()
    {
        return $this->belongsTo('App\Models\Agency', 'first_agency');
    }

    public function lastAgencies()
    {
        return $this->belongsTo('App\Models\Agency', 'last_agency');
    }

    public function receiver()
    {
        return $this->belongsTo('App\Models\User', 'receiver_id');
    }

    public function send_provinces()
    {
        return $this->belongsTo('App\Models\Province', 'send_province_id');
    }
    public function shiperInfo()
    {
        return $this->belongsTo('App\Models\User', 'shiper_id')->select('name', 'id', 'username', 'home_number', 'phone_numer');
    }

    public function send_districts()
    {
        return $this->belongsTo('App\Models\District', 'send_district_id');
    }

    public function send_wards()
    {
        return $this->belongsTo('App\Models\Ward', 'send_ward_id');
    }

    public function receive_provinces()
    {
        return $this->belongsTo('App\Models\Province', 'receive_province_id');
    }

    public function receive_districts()
    {
        return $this->belongsTo('App\Models\District', 'receive_district_id');
    }

    public function receive_wards()
    {
        return $this->belongsTo('App\Models\Ward', 'receive_ward_id');
    }

    public function deliveries()
    {
        return $this->hasMany('App\Models\BookDelivery', 'book_id');
    }

    public function returnBookingInfo()
    {
        return $this->hasOne('App\Models\BookDelivery', 'book_id', 'id')
            ->where('category', 'return')->select('category', 'status', 'id', 'book_id', 'user_id as shipper_id');
    }

    public function returnDeliveries()
    {
        return $this->hasOne('App\Models\BookDelivery', 'book_id', 'id')->where('book_deliveries.category', 'return')
            ->whereIn('book_deliveries.status', ['processing', 'completed']);
    }
    // tab giao lai
    public function requestDeliveries()
    {

        return $this->hasOne('App\Models\BookDelivery', 'book_id', 'id')->where('book_deliveries.category', 'return')
            ->where('book_deliveries.status', 'deny');
//                ->where('bookings.status', 'sending');
    }

    public function reportImages()
    {
        return $this->hasManyThrough('App\Models\ReportImage', 'App\Models\BookDelivery', 'book_id', 'task_id', 'id', 'id')->select('image', 'report_images.id')->orderBy('report_images.id', 'DESC');
    }

    public function transactionTypeService()
    {
        return $this->hasMany('App\Models\BookingTransactionService', 'book_id', 'book_id')->select('service_id', 'book_id');
    }

    public function reportDeliverImage()
    {
        return $this->hasMany('App\Models\ReportImage', 'task_id', 'id')->select('image');
    }
    public function getCategoryNameAttribute()
    {
        if (isset($this->category) && $this->category == 'return') {
            return 'Yêu cầu trả lại';
        }
        return '';
    }

    public function getSenderNameAttribute()
    {
        if (isset($this->sender)) {
            return $this->sender->name != null ? $this->sender->name : '';
        }
        return '';
    }

    public function getReceiverNameAttribute()
    {
        if (isset($this->receiver)) {
            return $this->receiver->name != null ? $this->receiver->name : '';
        }
        return '';
    }

    public function getSenderPhoneAttribute()
    {
        if (isset($this->sender)) {
            return $this->sender->phone_number != null ? $this->sender->phone_number : '';
        }
        return '';
    }

    public function getReceiverPhoneAttribute()
    {
        if (isset($this->receiver)) {
            return $this->receiver->phone_number != null ? $this->receiver->phone_number : '';
        }
        return '';
    }

    public function getSenderAddressAttribute()
    {
        if (isset($this->sender)) {
            return $this->sender->full_address != null ? $this->sender->full_address : '';
        }
        return '';
    }

    public function getReceiverAddressAttribute()
    {
        if (isset($this->receiver)) {
            return $this->receiver->full_address != null ? $this->receiver->full_address : '';
        }
        return '';
    }

    public function getCurrentAgencyNameAttribute()
    {
        if (isset($this->currentAgencies)) {
            return $this->currentAgencies->name != null ? $this->currentAgencies->name : '';
        }
        return '';
    }

    public function getFirstAgencyNameAttribute()
    {
        if (isset($this->firstAgencies)) {
            return $this->firstAgencies->name != null ? $this->firstAgencies->name : '';
        }
        return '';
    }

    public function getLastAgencyNameAttribute()
    {
        if (isset($this->lastAgencies)) {
            return $this->lastAgencies->name != null ? $this->lastAgencies->name : '';
        }
        return '';
    }

    public function shipperSender()
    {
        return $this->hasOne('App\Models\BookDelivery', 'book_id')->where('category', 'send');
    }

    public function shipperRecivcier()
    {
        return $this->hasOne('App\Models\BookDelivery', 'book_id')->where(['category' => 'receive']);
    }
    public function getShipperInfoAttribute()
    {
        $data = '';
        if (isset($this->deliveries)) {
            foreach ($this->deliveries as $d) {
                if ($d->category == 'send') {
                    $data = $d->shipper_info;
                    return $data;
                } else if ($d->category == 'receive' && $d->status == 'completed') {
                    $data = $d->shipper_info;
                } else if ($d->category == 'receive') {
                    $data = $d->shipper_info;
                }
            }
        }
        return $data;
    }
    public function getShipperPhoneAttribute()
    {
        $data = '';
        if (isset($this->deliveries)) {
            foreach ($this->deliveries as $d) {
                if ($d->category == 'send') {
                    $data = $d->shipper_phone;
                    return $data;
                } else if ($d->category == 'receive' && $d->status == 'completed') {
                    $data = $d->shipper_phone;
                } else if ($d->category == 'receive') {
                    $data = $d->shipper_phone;
                }
            }
        }
        return $data;
    }
    public function getShipperNameAttribute()
    {
        $data = '';
        if (isset($this->deliveries)) {
            foreach ($this->deliveries as $d) {
                if ($d->category == 'send') {
                    $data = $d->shipper_name;
                    return $data;
                } else if ($d->category == 'receive' && $d->status == 'completed') {
                    $data = $d->shipper_name;
                } else if ($d->category == 'receive') {
                    $data = $d->shipper_name;
                }
            }
        }
        return $data;
    }
    public function getActionStatusAttribute()
    {
        $data = 0;
        if (isset($this->deliveries)) {
            foreach ($this->deliveries as $d) {
                if ($d->category == 'send' && $d->status == 'processing') {
                    $data = 1;
                }
            }
        }
        return $data;
    }
    public function prePricing()
    {
        $max_weight_df = 2000;
        $special_length_df = 5000;
        $earthRadius = 6372.795477598;
        $lat_fr = $this->send_lat;
        $lng_fr = $this->send_lng;
        $lat_to = $this->receive_lat;
        $lng_to = $this->receive_lng;
        if ($lat_fr == 0 || $lng_fr == 0) {
            $mapResults_fr = Booking::getLocation($this->send_province_id, $this->send_district_id, $this->send_ward_id, $this->send_homenumber);
            if (isset($mapResults_fr->geometry)) {
                if (isset($mapResults_fr->geometry->location)) {
                    $lat_fr = $mapResults_fr->geometry->location->lat;
                    $lng_fr = $mapResults_fr->geometry->location->lng;
                }
            }
        }
        if ($lat_to == 0 || $lng_to == 0) {
            $mapResults_to = Booking::getLocation($this->receive_province_id, $this->receive_district_id, $this->receive_ward_id, $this->receive_homenumber);
            if (isset($mapResults_to->geometry)) {
                if (isset($mapResults_to->geometry->location)) {
                    $lat_to = $mapResults_to->geometry->location->lat;
                    $lng_to = $mapResults_to->geometry->location->lng;
                }
            }
        }

        $latFrom = deg2rad($lat_fr);
        $lonFrom = deg2rad($lng_fr);
        $latTo = deg2rad($lat_to);
        $lonTo = deg2rad($lng_to);
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;
        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        $km = $angle * $earthRadius;

        $data = $this->bookcheckProvince($km, $max_weight_df, $special_length_df);

        $cod = $this->COD;

        if ($cod > 0) {
            $key_discount = $this->transport_type == 1 ? 'urban_cod' : 'cod';
            $percent_check = Setting::where('type', 'customer')->where('key', $key_discount)->first();
            $percent = $percent_check != null ? $percent_check->value : 0;
            $key_min_discount = $this->transport_type == 1 ? 'min_cod_urban' : 'min_cod';
            $min_discount_check = Setting::where('type', 'customer')->where('key', $key_min_discount)->first();
            $min_discount = $min_discount_check != null ? $min_discount_check->value : 0;
            $cod_discount = (($cod * abs($percent)) / 100) >= $min_discount ? (($cod * abs($percent)) / 100) : $min_discount;

            $data = $data + $cod_discount;
        }

        // cộng tiền vào dịch vụ cộng thêm
        //$transportTypeServices = Setting::where('type', 'transport_type')->orderBy('value', 'ASC')->get();
        $currentTransportServices = BookingTransactionService::where('book_id', $this->id)->get();

        foreach ($currentTransportServices as $item) {

            if (!empty($item)) {

                $data += $item->price;
            }
        }
        return $data;
    }

    public static function Pricing($req, $getmsg = false)
    {
        $max_weight_df = 2000;
        $special_length_df = 5000;
        $earthRadius = 6372.795477598;
        $lat_fr = $req->sender['location']['lat'];
        $lng_fr = $req->sender['location']['lng'];
        $lat_to = $req->receiver['location']['lat'];
        $lng_to = $req->receiver['location']['lng'];
        $msg = [];
        if ($lat_fr == 0 || $lng_fr == 0) {
            $mapResults_fr = Booking::getLocation($req->sender['address']['province'], $req->sender['address']['district'], $req->sender['address']['ward'], $req->sender['address']['homenumber']);
            if (isset($mapResults_fr->geometry)) {
                if (isset($mapResults_fr->geometry->location)) {
                    $lat_fr = $mapResults_fr->geometry->location->lat;
                    $lng_fr = $mapResults_fr->geometry->location->lng;
                }
            }
        }
        if ($lat_to == 0 || $lng_to == 0) {
            $mapResults_to = Booking::getLocation($req->receiver['address']['province'], $req->receiver['address']['district'], $req->receiver['address']['ward'], $req->receiver['address']['homenumber']);
            if (isset($mapResults_to->geometry)) {
                if (isset($mapResults_to->geometry->location)) {
                    $lat_to = $mapResults_to->geometry->location->lat;
                    $lng_to = $mapResults_to->geometry->location->lng;
                }
            }
        }
        $latFrom = deg2rad($lat_fr);
        $lonFrom = deg2rad($lng_fr);
        $latTo = deg2rad($lat_to);
        $lonTo = deg2rad($lng_to);
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;
        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        $km = $angle * $earthRadius;

        $data = Booking::checkProvince($req, $km, $max_weight_df, $special_length_df);
        $messageWeight = '';
        if ($req->weight <= 5000) {
            $messageWeight = 'Cước dưới 5000 gram: ' . $data;
        } else {
            $messageWeight = 'Cước trên 5000 gram/3k/kg : ' . $data;
        }
        // bổ sung phần tính tiền: 1 = nội thành; 2 = ngoại thành 1; 3 = ngoại thành 2
        if ($req->transport_type == 1) {
            $districtFrom = District::find($req->sender['address']['district']);
            $districtTo = District::find($req->receiver['address']['district']);
            $priceDistrictFrom = 0;
            $priceDistrictTo = 0;
            $districtTypeSpecal = [1, 2, 3];
            $provincialUPs = ProvincialUP::whereIn('district_type', $districtTypeSpecal)->get();
            if (in_array($districtFrom->district_type, $districtTypeSpecal) || in_array($districtTo->district_type, $districtTypeSpecal)) {
                foreach ($provincialUPs as $item) {
                    if ($item->district_type == $districtFrom->district_type) {
                        $priceDistrictFrom = $item->price;
                    }
                    if ($item->district_type == $districtTo->district_type) {
                        $priceDistrictTo = $item->price;
                    }
                }
                $data = ($priceDistrictFrom - $priceDistrictTo) > 0 ? $priceDistrictFrom : $priceDistrictTo;
                if ($req->weight <= 5000) {
                    $messageWeight = 'Cước dưới 5000 gram: ' . $data;
                } else {
                    $messageWeight = 'Cước trên 5000 gram/3k/kg : ' . $data;
                }
            }
        }
        if (!empty($messageWeight)) {
            $msg[] = $messageWeight;
        }
        // end bổ sung phần tính tiền nội thành/ngoại thành 1/ngoại thành 2

        if ($req->receive_type == 2) {
            $percent = Setting::where('type', 'customer')->where('key', 'receive_type')->first()->value;
            $receiver_fee = (($data * abs($percent)) / 100);
            $data = $data - (($data * abs($percent)) / 100);
            $msg[] = 'Giao hàng đến bưu cục (Giảm 7% cước) : ' . number_format($receiver_fee) . 'VND';
        } else {
            $msg[] = 'Phí lấy hàng tại nhà : 0VND';
        }
        if ($req->transport_type == 3) {
            $percent = Setting::where('type', 'customer')->where('key', 'transport_type')->first()->value;
            $transport_fee = (($data * abs($percent)) / 100);
            $data = $data + $transport_fee;
            $msg[] = 'Giao siêu tốc:' . number_format($transport_fee) . 'VND';
        }
        $cod = 0;
        if (isset($req->COD) && $req->COD != null) {
            $cod = $req->COD;
        } else if (isset($req->cod) && $req->cod != null) {
            $cod = $req->cod;
        }
        /*if ($cod > 0) {
        $province_type = false;
        if ($req->sender['address']['province'] == $req->receiver['address']['province']){
        $check_pr = Province::find($req->sender['address']['province']);
        if ($check_pr != null){
        if ($check_pr->province_type == 1){
        $province_type = true;
        }
        }
        }
        $key_discount = $req->transport_type == 1 || $province_type ? 'urban_cod' : 'cod';
        $percent_check = Setting::where('type', 'customer')->where('key', $key_discount)->first();
        $percent = $percent_check != null ? $percent_check->value : 0;
        $key_min_discount = $req->transport_type == 1 || $province_type ? 'min_cod_urban' : 'min_cod';
        $min_discount_check = Setting::where('type', 'customer')->where('key', $key_min_discount)->first();
        $min_discount = $min_discount_check != null ? $min_discount_check->value : 0;
        $cod_discount = (($cod * abs($percent)) / 100) >= $min_discount ? (($cod * abs($percent)) / 100) : $min_discount;
        $data = $data + $cod_discount;
        }*/

        if ($cod > 0) {
            $key_discount = $req->transport_type == 1 ? 'urban_cod' : 'cod';
            $percent_check = Setting::where('type', 'customer')->where('key', $key_discount)->first();
            $percent = $percent_check != null ? $percent_check->value : 0;
            $key_min_discount = $req->transport_type == 1 ? 'min_cod_urban' : 'min_cod';
            $min_discount_check = Setting::where('type', 'customer')->where('key', $key_min_discount)->first();
            $min_discount = $min_discount_check != null ? $min_discount_check->value : 0;
            $cod_discount = (($cod * abs($percent)) / 100) >= $min_discount ? (($cod * abs($percent)) / 100) : $min_discount;
            $msg[] = 'Phí thu hộ COD: ' . number_format($cod_discount) . 'VND';
//            if ($key_discount == 'urban_cod') {
            //                $msg[] = 'Phí thu hộ COD: ' . number_format($cod_discount).'VND';
            //            } else {
            //                $msg[] = 'cước thu hộ COD tuyến ngoài thành phố' . number_format($cod_discount).'VND';
            //            }
            $data = $data + $cod_discount;
        }

        // cộng tiền vào dịch vụ cộng thêm
        $transportTypeServices = Setting::where('type', 'transport_type')->orderBy('value', 'ASC')->get();
        foreach ($transportTypeServices as $item) {
            if (isset($req->transport_type_services) && !empty($req->transport_type_services)) {
                $selectedServices = explode(',', $req->transport_type_services);
                if (!empty($selectedServices)) {
                    if (in_array($item->id, $selectedServices)) {
                        $data += $item->value;
                        $msg[] = 'Phí ' . $item->name . ': ' . number_format($item->value) . 'VND';
                    }
                }
            }

            // will remove
            if (isset($req->transport_type_service1) && $req->transport_type_service1 == 1 && $item->key == 'transport_type_service1') {
                $data += $item->value;
            }
            if (isset($req->transport_type_service2) && $req->transport_type_service2 == 1 && $item->key == 'transport_type_service2') {
                $data += $item->value;
            }
            if (isset($req->transport_type_service3) && $req->transport_type_service3 == 1 && $item->key == 'transport_type_service3') {
                $data += $item->value;
            }
        }
        $msg[] = 'Tổng cước : ' . $data;
        if ($getmsg) {
            return [
                'total' => $data,
                'msg' => implode(' \n ', $msg),
            ];
        } else {
            return $data;
        }
    }
    public function bookcheckProvince($km, $max_weight_df, $special_length_df)
    {

        $send_province = $this->send_province_id;
        $receive_province = $this->receive_province_id;
        $send_district = $this->send_district_id;
        $receive_district = $this->receive_district_id;
        $weight = $this->weight;
        $params = [
            'max_weight_df' => $max_weight_df,
            'special_length_df' => $special_length_df,
            'km' => $km, 'weight' => $weight,
            'receive_district_id' => $receive_district,
            'send_district_id' => $send_district,
            'receive_province_id' => $receive_province,
            'currentUser' => $this->sender,
            'send_province_id' => $send_province,
        ];
        $result = Booking::cauclatorPriceProvince($params);
        return $result;
    }
    public static function cauclatorPriceProvince($params)
    {
        $result = 0;
        $send_province = $params['send_province_id'];
        $receive_province = $params['receive_province_id'];
        $send_district = $params['send_district_id'];
        $receive_district = $params['receive_district_id'];
        $weight = $params['weight'];
        $max_weight_df = $params['max_weight_df'];
        $special_length_df = $params['special_length_df'];
        $currentUser = $params['currentUser'];
        $km = $params['km'];
        $check_district = District::where('id', $send_district)->orWhere('id', $receive_district)->orderBy('district_type', 'desc')->get();

        foreach ($check_district as $item) {
            $type[] = $item->district_type;
        }
        if ($send_province == $receive_province) {
            $result = Booking::checkProvincial($type, $weight, $max_weight_df, $special_length_df, $currentUser);
        }

        $check = DB::table('special_u_ps')->where([['province_from', $send_province], ['province_to', $receive_province]])
            ->orWhere([['province_from', $receive_province], ['province_to', $send_province]]);
        if (!empty($check->first())) {
            $result = DB::table('special_u_ps')->where([['province_from', $send_province], ['province_to', $receive_province], ['weight', '<=', $weight]])
                ->orWhere([['province_from', $receive_province], ['province_to', $send_province], ['weight', '<=', $weight]])->max('price');
            if ($weight > env('MAX_WEIGHT')) {
                $special_price = DB::table('special_prices')->where([['province_from', $send_province], ['province_to', $receive_province]])
                    ->orWhere([['province_from', $receive_province], ['province_to', $send_province]])->max('price');
                $result += ceil(($weight - env('MAX_WEIGHT', $max_weight_df)) / env('SPECIAL_WEIGHT', $special_length_df)) * $special_price;
            }
        } else {
            if (in_array(5, $type)) {
                $max_km = DB::table('inter_municipal_u_ps')->where('district_type', 5)->where('km', '<=', $km)->max('km');
                $max_weight = DB::table('inter_municipal_u_ps')->where('district_type', 5)->where('weight', '<=', $weight)->max('weight');
                $result = DB::table('inter_municipal_u_ps')->select('price')->where('district_type', 5)->where('weight', $max_weight)->where('km', $max_km)->first()->price;
                $special_price = DB::table('special_prices')->where('district_type', 5)->where('km', $max_km)->first()->price;
                $result += ceil(($weight - env('MAX_WEIGHT', $max_weight_df)) / env('SPECIAL_WEIGHT', $special_length_df)) * $special_price;
            } else {
                $max_km = DB::table('inter_municipal_u_ps')->where('district_type', 4)->where('km', '<=', $km)->max('km');
                $max_weight = DB::table('inter_municipal_u_ps')->where('district_type', 4)->where('weight', '<=', $weight)->max('weight');
                $result = DB::table('inter_municipal_u_ps')->select('price')->where('district_type', 4)->where('weight', $max_weight)->where('km', $max_km)->first()->price;
                $special_price = DB::table('special_prices')->where('district_type', 4)->where('km', $max_km)->first()->price;
                $result += ceil(($weight - env('MAX_WEIGHT', $max_weight_df)) / env('SPECIAL_WEIGHT', $special_length_df)) * $special_price;
            }
            /* if (in_array(1, $type)) {
        $result += DB::table('provincial_u_ps')->select('price')->where('district_type', 1)->first()->price;
        } elseif (in_array(2, $type)) {
        $result += DB::table('provincial_u_ps')->select('price')->where('district_type', 2)->first()->price;
        } elseif (in_array(3, $type)) {
        $result += DB::table('provincial_u_ps')->select('price')->where('district_type', 3)->first()->price;
        } */
        }

        return $result;
    }

    // phan biet noi thanh ngoai thanh
    public static function checkProvince($req, $km, $max_weight_df, $special_length_df)
    {
        $send_province = $req->sender['address']['province'];
        $receive_province = $req->receiver['address']['province'];
        $send_district = $req->sender['address']['district'];
        $receive_district = $req->receiver['address']['district'];
        $weight = $req->weight;
        $params = [
            'max_weight_df' => $max_weight_df,
            'special_length_df' => $special_length_df,
            'km' => $km,
            'weight' => $weight,
            'receive_district_id' => $receive_district,
            'send_district_id' => $send_district,
            'receive_province_id' => $receive_province,
            'currentUser' => null,
            'send_province_id' => $send_province,
        ];

        return Booking::cauclatorPriceProvince($params);
    }

    // app dung giá nội thành tất cả
    //    static function checkProvince($req, $km, $max_weight_df, $special_length_df)
    //    {
    //        $result = 0;
    //        $send_province = $req->sender['address']['province'];
    //        $receive_province = $req->receiver['address']['province'];
    //        $send_district = $req->sender['address']['district'];
    //        $receive_district = $req->receiver['address']['district'];
    //        $weight = $req->weight;
    //
    //        $check_district = District::where('id', $receive_district)->orderBy('district_type', 'desc')->get();
    //        foreach ($check_district as $item) {
    //            $type[] = $item->district_type;
    //        }
    //        $result = Booking::checkProvincial($type, $weight, $max_weight_df, $special_length_df);
    //        return $result;
    //    }

    public static function checkProvincial($type, $weight, $max_weight_df, $special_length_df, $currentUser = null)
    {
        // áp dụng bảng giá riêng cho tài khoản VIP
        $db = DB::table('provincial_u_ps');
        $user = null;
        if (Auth::check() && Auth::user()) {
            $user = Auth::user();
        }
        if ($currentUser) {
            $user = $currentUser;
        }
        if ($user->is_vip == 1) { //VIP
            $db = $db->where('type', 1);
        } elseif ($user->is_vip == 2) { //Pro
            $db = $db->where('type', 2);
        } else {
            $db = $db->where('type', 0);
        }

        if (in_array(5, $type)) {
            // $result = $db->select('price')->where('district_type', 5)->where('weight', '<=', $weight)->orderBy('weight', 'desc')->first();
            $result = $db->where('district_type', 5)->first();
            // $special_price = DB::table('special_prices')->where('district_type', 1)->first()->price;
            // $result += (ceil(($weight - env('MAX_WEIGHT', $max_weight_df)) / env('SPECIAL_WEIGHT', $special_length_df)) * $special_price);
            $price = Booking::getPriceWeight($weight, $result);
            return $price;
        }
        if (in_array(4, $type)) {
            // $result = DB::table('inter_municipal_u_ps')->select('price')->where('district_type', 4)->where('weight', '<=', $weight)->orderBy('weight', 'desc')->first()->price;
            $result = $db->where('district_type', 4)->first();
            // $special_price = DB::table('special_prices')->where('district_type', 1)->first()->price;
            // $result += (ceil(($weight - env('MAX_WEIGHT', $max_weight_df)) / env('SPECIAL_WEIGHT', $special_length_df)) * $special_price);

            $price = Booking::getPriceWeight($weight, $result);
            return $price;
        }
        if (in_array(3, $type)) {
            $result = $db->where('district_type', 3)->first();
            $price = Booking::getPriceWeight($weight, $result);
            return $price;
        }
        if (in_array(2, $type)) {
            $result = $db->where('district_type', 2)->first();
            $price = Booking::getPriceWeight($weight, $result);
            return $price;
        }
        if (in_array(1, $type)) {
            $result = $db->where('district_type', 1)->first();
            $price = Booking::getPriceWeight($weight, $result);
            return $price;
        }
        return 0;
    }

    public static function getPriceWeight($weight, $result)
    {
        $price = 0;
        if ($weight < $result->weight) {
            $price = $result->price;
        } else {
            $subWeight = ($weight - $result->weight);
            $price = $result->price + ($subWeight * $result->price_plus / $result->weight_plus);
        }
        return $price;
    }

    protected static function boot()
    {
        parent::boot(); // TODO: Change the autogenerated stub
        static::deleting(function ($query) {
            $query->deliveries()->delete();
        });
    }

    //-----------RAYMOND API---------
    public static function create($data)
    {
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $user = $data->user();
        if (($user->province_id == 0 || $user->province_id == null) && ($user->district_id == 0 || $user->district_id == null) && ($user->ward_id == 0 || $user->ward_id == null)) {
            return 201;
        }else{
            $qr = QRCode::findQRCode($data->qrcode);
            $order = DB::table('bookings')->insertGetId([
                'COD' => $data->COD,
                'uuid' => $qr->name,
                'qrcode_id' => $qr->id,
                'sender_id' => $user->id,
                'send_province_id'=>$user->province_id,
                'send_district_id'=>$user->district_id,
                'send_ward_id'=>$user->ward_id,
                'send_homenumber'=>$user->home_number,
                'send_full_address'=>$user->home_number,
                'send_name'=>$user->name,
                'send_phone'=>$user->phone_number,
                'created_at' => date('Y-m-d H:i:s'),
                'status' => 'new',
            ]);
            if ($data->hasFile('image_order')) {
                $file = $data->image_order;
                $filename = date('Ymd-His-') . $file->getFilename() . '.' . $file->extension();
                $filePath = 'img/order/';
                $movePath = public_path($filePath);
                $file->move($movePath, $filename);
                DB::table('bookings')->where('id',$order)->update(['image_order'=>$filePath . $filename]);
            }
            DB::table('qrcode')->where('id', $qr->id)->update(['is_used' => 1, 'used_at' => date('Y-m-d H:i:s')]);
            return 200;
        }
    }
}
