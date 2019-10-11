<?php

namespace App\Http\Controllers\UI;

use App\Http\Requests\CreateBookingRequest;
use App\Http\Requests\FrontEnt\BookingRequest;
use App\Models\Booking;
use App\Models\District;
use App\Models\Province;
use App\Models\User;
use App\Models\Ward;
use App\Models\DeliveryAddress;
use App\Models\SendAndReceiveAddress;
use App\Models\Notification;
use App\Models\NotificationUser;
use App\Models\Setting;
use function dd;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use \Auth;
use Illuminate\Support\Facades\DB;
use function redirect;
use App\Models\BookDelivery;
use App\Models\Shipper;
use App\Models\Agency;
use App\Models\Collaborator;
use Session;
use App\Helpers\NotificationHelper;
use App\Jobs\NotificationJob;
use Validator;
use Excel;
use App\Helpers\CommonHelper;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct()
    {
        $this->middleware('ui.auth');
    }

    protected function getAddress($province, $district, $ward, $home_number)
    {
        $province_name = Province::find($province)->name;
        $district_name = District::find($district)->name;
        $ward_name = Ward::find($ward)->name;;
        return $home_number . ', ' . $ward_name . ', ' . $district_name . '. ' . $province_name;
    }

    public function allBooking(){
        $db = DB::table('bookings as b')->leftJoin('qrcode as q','b.qrcode_id','=','q.id')->where('b.sender_id', Auth::user()->id)->select('b.*','q.name as qr_name');

        if (isset(request()->keyword) && !empty(request()->keyword)) {
            $db = $db->where(function($q){
                $q->where('name', 'LIKE', '%' . request()->keyword . '%');
                $q->orWhere('uuid', 'LIKE', '%' . request()->keyword . '%');
                $q->orWhere('send_phone', 'LIKE', '%' . request()->keyword . '%');
                $q->orWhere('send_full_address', 'LIKE', '%' . request()->keyword . '%');
                $q->orWhere('send_name', 'LIKE', '%' . request()->keyword . '%');
                $q->orWhere('receive_phone', 'LIKE', '%' . request()->keyword . '%');
                $q->orWhere('receive_full_address', 'LIKE', '%' . request()->keyword . '%');
                $q->orWhere('receive_name', 'LIKE', '%' . request()->keyword . '%');
            });
        }

        $all = $db->orderBy('b.created_at', 'desc')->paginate(10);
        $url = url('front-ent/booking/all');
        $keyword = isset(request()->keyword) ? request()->keyword : '';
        $countBookStatus = $this->countBookStatus();
        return view('front-ent.element.booking.index', ['bookings' => $all, 'active' => 'all', 'url' => $url, 'keyword' => $keyword, 'countBookStatus' => $countBookStatus]);

    }

    public function receivedBooking(){
        $db = Booking::where('sender_id', Auth::user()->id)->where('status', 'sending');

        if (isset(request()->keyword) && !empty(request()->keyword)) {
            $db = $db->where(function($q){
                $q->where('name', 'LIKE', '%' . request()->keyword . '%');
                $q->orWhere('uuid', 'LIKE', '%' . request()->keyword . '%');
                $q->orWhere('send_phone', 'LIKE', '%' . request()->keyword . '%');
                $q->orWhere('send_full_address', 'LIKE', '%' . request()->keyword . '%');
                $q->orWhere('send_name', 'LIKE', '%' . request()->keyword . '%');
                $q->orWhere('receive_phone', 'LIKE', '%' . request()->keyword . '%');
                $q->orWhere('receive_full_address', 'LIKE', '%' . request()->keyword . '%');
                $q->orWhere('receive_name', 'LIKE', '%' . request()->keyword . '%');
            });
        }

        $received = $db->orderBy('created_at', 'desc')->paginate(10);
        $url = url('front-ent/booking/received');
        $keyword = isset(request()->keyword) ? request()->keyword : '';
        $countBookStatus = $this->countBookStatus();
        return view('front-ent.element.booking.index', ['bookings' => $received, 'active' => 'received', 'url' => $url, 'keyword' => $keyword, 'countBookStatus' => $countBookStatus]);

    }

    public function sentBooking(){
        $db = Booking::where('sender_id', Auth::user()->id)->where('status', 'completed');

        if (isset(request()->keyword) && !empty(request()->keyword)) {
            $db = $db->where(function($q){
                $q->where('name', 'LIKE', '%' . request()->keyword . '%');
                $q->orWhere('uuid', 'LIKE', '%' . request()->keyword . '%');
                $q->orWhere('send_phone', 'LIKE', '%' . request()->keyword . '%');
                $q->orWhere('send_full_address', 'LIKE', '%' . request()->keyword . '%');
                $q->orWhere('send_name', 'LIKE', '%' . request()->keyword . '%');
                $q->orWhere('receive_phone', 'LIKE', '%' . request()->keyword . '%');
                $q->orWhere('receive_full_address', 'LIKE', '%' . request()->keyword . '%');
                $q->orWhere('receive_name', 'LIKE', '%' . request()->keyword . '%');
            });
        }

        $sent = $db->orderBy('completed_at', 'desc')->paginate(10);
        $url = url('front-ent/booking/sent');
        $keyword = isset(request()->keyword) ? request()->keyword : '';
        $countBookStatus = $this->countBookStatus();
        return view('front-ent.element.booking.index', ['bookings' => $sent, 'active' => 'sent', 'url' => $url, 'keyword' => $keyword, 'countBookStatus' => $countBookStatus]);
    }

    public function returnBooking(){
        $db = Booking::where('sender_id', Auth::user()->id)->where('status', 'return');

        if (isset(request()->keyword) && !empty(request()->keyword)) {
            $db = $db->where(function($q){
                $q->where('name', 'LIKE', '%' . request()->keyword . '%');
                $q->orWhere('uuid', 'LIKE', '%' . request()->keyword . '%');
                $q->orWhere('send_phone', 'LIKE', '%' . request()->keyword . '%');
                $q->orWhere('send_full_address', 'LIKE', '%' . request()->keyword . '%');
                $q->orWhere('send_name', 'LIKE', '%' . request()->keyword . '%');
                $q->orWhere('receive_phone', 'LIKE', '%' . request()->keyword . '%');
                $q->orWhere('receive_full_address', 'LIKE', '%' . request()->keyword . '%');
                $q->orWhere('receive_name', 'LIKE', '%' . request()->keyword . '%');
            });
        }

        $bookings = $db->orderBy('completed_at', 'desc')->paginate(10);
        $url = url('front-ent/booking/return');
        $keyword = isset(request()->keyword) ? request()->keyword : '';
        $countBookStatus = $this->countBookStatus();
        return view('front-ent.element.booking.index', ['bookings' => $bookings, 'active' => 'return', 'url' => $url, 'keyword' => $keyword, 'countBookStatus' => $countBookStatus]);
    }

    public function getCancelBooking(){
        $db = Booking::where('sender_id', Auth::user()->id)->where('status', 'cancel');

        if (isset(request()->keyword) && !empty(request()->keyword)) {
            $db = $db->where(function($q){
                $q->where('name', 'LIKE', '%' . request()->keyword . '%');
                $q->orWhere('uuid', 'LIKE', '%' . request()->keyword . '%');
                $q->orWhere('send_phone', 'LIKE', '%' . request()->keyword . '%');
                $q->orWhere('send_full_address', 'LIKE', '%' . request()->keyword . '%');
                $q->orWhere('send_name', 'LIKE', '%' . request()->keyword . '%');
                $q->orWhere('receive_phone', 'LIKE', '%' . request()->keyword . '%');
                $q->orWhere('receive_full_address', 'LIKE', '%' . request()->keyword . '%');
                $q->orWhere('receive_name', 'LIKE', '%' . request()->keyword . '%');
            });
        }

        $bookings = $db->orderBy('completed_at', 'desc')->paginate(10);
        $url = url('front-ent/booking/get-cancel');
        $keyword = isset(request()->keyword) ? request()->keyword : '';
        $countBookStatus = $this->countBookStatus();
        return view('front-ent.element.booking.index', ['bookings' => $bookings, 'active' => 'cancel', 'url' => $url, 'keyword' => $keyword, 'countBookStatus' => $countBookStatus]);
    }

    public function cancelBooking($id){
        DB::beginTransaction();
        try {
            $query = Booking::find($id);
            $query->status = 'cancel';
            $query->save();
            DB::commit();

            // thông báo tới admin, customer, shipper khi hủy đơn hàng
            $notificationHelper = new NotificationHelper();
            $bookingTmp = $query->toArray();
            $bookDeliveryTmp = BookDelivery::where('book_id', $id)->first();
            if ($bookDeliveryTmp && !empty($bookDeliveryTmp)) {
                $bookingTmp['shipper_id'] = $bookDeliveryTmp->user_id;
                $bookingTmp['book_delivery_id'] = $bookDeliveryTmp->id;
                // $notificationHelper->notificationBooking($bookingTmp, 'shipper', ' vừa được hủy', 'push_order_change');
                dispatch(new NotificationJob($bookingTmp, 'shipper', ' vừa được hủy', 'push_order_change'));
            }
            // $notificationHelper->notificationBooking($bookingTmp, 'admin', ' vừa được hủy', 'push_order_change');
            // $notificationHelper->notificationBooking($bookingTmp, 'customer', ' vừa được hủy', 'push_order_change');
            dispatch(new NotificationJob($bookingTmp, 'admin', ' vừa được hủy', 'push_order_change'));
            dispatch(new NotificationJob($bookingTmp, 'customer', ' vừa được hủy', 'push_order_change'));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->apiError($e->getMessage());
        }
        return redirect()->back()->with('success', 'Hủy đơn hàng thành công');

    }

    public function index()
    {
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $userLogin = array();
        $deliveryAddressSend = array();
        $deliveryAddressReceive = array();
        $deliveryAddressDefault = array();
        if (Auth::check()) {
            $userLogin = Auth::user();
            $deliveryAddressDefault = DeliveryAddress::where('user_id', $userLogin->id)->where('default', 1)->first();
            $deliveryAddressSend = SendAndReceiveAddress::where('user_id', $userLogin->id)->where('type', 'send')->get();
            $deliveryAddressReceive = SendAndReceiveAddress::where('user_id', $userLogin->id)->where('type', 'receive')->get();
            foreach ($deliveryAddressSend as $item) {
                $deliveryAddressSend->provinces = $item->provinces;
                $deliveryAddressSend->districts = $item->districts;
                $deliveryAddressSend->wards = $item->wards;
            }
            foreach ($deliveryAddressReceive as $item) {
                $deliveryAddressReceive->provinces = $item->provinces;
                $deliveryAddressReceive->districts = $item->districts;
                $deliveryAddressReceive->wards = $item->wards;
            }
        }
        $transportTypeServices = Setting::where('type', 'transport_type')->orderBy('value', 'ASC')->get();
        if (empty($transportTypeServices) || count($transportTypeServices) == 0) {
            $this->insertTransportTypeService();
            $transportTypeServices = Setting::where('type', 'transport_type')->orderBy('value', 'ASC')->get();
        }
        $transportTypeDes = Setting::where('type', 'transport_type_des')->orderBy('key', 'ASC')->get();
        if (empty($transportTypeDes) || count($transportTypeDes) == 0) {
            $this->insertTransportTypeDes();
            $transportTypeDes = Setting::where('type', 'transport_type_des')->orderBy('key', 'ASC')->get();
        }
        foreach ($transportTypeDes as $item) {
            if ($item->key == 'transport_type_des1') {
                $transportTypeDes1 = $item;
            } else {
                $transportTypeDes2 = $item;
            }
        }
        $data = array(
            'userLogin' => $userLogin,
            'deliveryAddressSend' => $deliveryAddressSend,
            'deliveryAddressDefault' => $deliveryAddressDefault,
            'deliveryAddressReceive' => $deliveryAddressReceive,
            'transportTypeServices' => $transportTypeServices,
            'transportTypeDes1' => @$transportTypeDes1,
            'transportTypeDes2' => @$transportTypeDes2
        );
        return view('front-ent.element.booking', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateBookingRequest $req)
    {

        DB::beginTransaction();
        try {
            $sender_id = null;
            $receiver_id = null;
            // $sender_check = User::where('phone_number', $req->phone_number_fr)->where('role', 'customer')->where('delete_status', 0)->first();
            $receiver_check = User::where('phone_number', $req->phone_number_to)->where('role', 'customer')->where('delete_status', 0)->first();
            // if (!empty($sender_check)) {
            //     $sender_id = $sender_check->id;
            // } else {
            //     $user = new User();
            //     $user->phone_number = $req->phone_number_fr;
            //     $user->save();
            //     $sender_id = $user->id;
            // }

            $sender_id = Auth::user()->id;
            if (!empty($receiver_check)) {
                $receiver_id = $receiver_check->id;
            } else {
                $user = new User();
                $user->phone_number = $req->phone_number_to;
                $user->save();
                $receiver_id = $user->id;
            }
            $booking = new Booking();
            $booking->sender_id = $sender_id;
            $booking->receiver_id = $receiver_id;
            $booking->name = $req->name;
            $booking->send_name = $req->name_fr;
            $booking->send_phone = $req->phone_number_fr;
            $booking->send_province_id = $req->province_id_fr;
            $booking->send_district_id = $req->district_id_fr;
            $booking->send_ward_id = $req->ward_id_fr;
            $booking->send_homenumber = $req->home_number_fr;
            $booking->send_full_address = $this->getAddress($req->province_id_fr, $req->district_id_fr, $req->ward_id_fr, $req->home_number_fr);
            $booking->receive_name = $req->name_to;
            $booking->receive_phone = $req->phone_number_to;
            $booking->receive_province_id = $req->province_id_to;
            $booking->receive_district_id = $req->district_id_to;
            $booking->receive_ward_id = $req->ward_id_to;
            $booking->receive_homenumber = $req->home_number_to;
            $booking->receive_full_address = $this->getAddress($req->province_id_to, $req->district_id_to, $req->ward_id_to, $req->home_number_to);
            $booking->receive_type = $req->receive_type;
            $booking->price = $req->price;
            $booking->weight = $req->weight;
            $booking->transport_type = $req->transport_type;
            $booking->payment_type = $req->payment_type;
            $booking->COD = $req->cod != null ? $req->cod : 0;
            $booking->other_note = $req->other_note;
            $booking->status = 'new';
            if ($req->hasFile('image_order')) {
                $file = $req->image_order;
                $filename = date('Ymd-His-') . $file->getFilename() . '.' . $file->extension();
                $filePath = 'img/order/';
                $movePath = public_path($filePath);
                $file->move($movePath, $filename);
                $booking->image_order = $filePath . $filename;
            }
            $transport_type_services = null;
            if(!empty($req->transport_type_service1)){
                $transport_type_services[] = 11;
            }
            if(!empty($req->transport_type_service2)){
                $transport_type_services[] = 12;
            }
            if(!empty($req->transport_type_service3)){
                $transport_type_services[] = 13;
            }
            $booking->transport_type_service1 = (isset($req->transport_type_service1) && $req->transport_type_service1 == 1) ? 1 : 0;
            $booking->transport_type_service2 = (isset($req->transport_type_service2) && $req->transport_type_service2 == 1) ? 1 : 0;
            $booking->transport_type_service3 = (isset($req->transport_type_service3) && $req->transport_type_service3 == 1) ? 1 : 0;
           
            if(!empty($transport_type_services)){
             
                $booking->transport_type_services = implode(',', $transport_type_services);
            }
    
            // kiểm tra khách lần đầu tiên sử dụng hệ thống (khách mới)
            $check = Booking::where('sender_id', $req->user()->id)->count();
            if ($check == 0) {
                $booking->is_customer_new = 1;
            }

            $booking->save();
            //tạo uuid
            $uuid = Booking::find($booking->id);
            $uuid->uuid = str_random(5) . $uuid->id;
            //tạo qrcode
            date_default_timezone_set('Asia/Ho_Chi_Minh');
            $qrcode_id=DB::table('qrcode')->insertGetId(
                ['name' => $uuid->uuid, 'is_used' => 1,'created_at'=>date('Y-m-d H:i:s'),'used_at'=>date('Y-m-d H:i:s')]
            );
            $uuid->qrcode_id=$qrcode_id;
            $uuid->save();

            DB::commit();

            // Thông báo tới admin có đơn hàng mới
            $bookingTmp = $booking->toArray();
            $bookingTmp['uuid'] = $uuid->uuid;
            // $notificationHelper = new NotificationHelper();
            // $notificationHelper->notificationBooking($bookingTmp, 'admin', ' vừa được tạo', 'push_order');
            dispatch(new NotificationJob($bookingTmp, 'admin', ' vừa được tạo', 'push_order'));
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
        }
        $req->session()->flash('success', 'Tạo mới đơn hàng thành công! cảm ơn bạn đã sử dụng hệ thống!');
        return redirect(url('/'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $booking = Booking::find($id);
        $userLogin = array();
        $deliveryAddressSend = array();
        $deliveryAddressReceive = array();
        if (Auth::check()) {
            $userLogin = Auth::user();
            $deliveryAddressSend = SendAndReceiveAddress::where('user_id', $userLogin->id)->where('type', 'send')->get();
            $deliveryAddressReceive = SendAndReceiveAddress::where('user_id', $userLogin->id)->where('type', 'receive')->get();
            foreach ($deliveryAddressSend as $item) {
                $deliveryAddressSend->provinces = $item->provinces;
                $deliveryAddressSend->districts = $item->districts;
                $deliveryAddressSend->wards = $item->wards;
            }
            foreach ($deliveryAddressReceive as $item) {
                $deliveryAddressReceive->provinces = $item->provinces;
                $deliveryAddressReceive->districts = $item->districts;
                $deliveryAddressReceive->wards = $item->wards;
            }
        }
        $transportTypeServices = Setting::where('type', 'transport_type')->orderBy('value', 'ASC')->get();
        if (empty($transportTypeServices) || count($transportTypeServices) == 0) {
            $this->insertTransportTypeService();
            $transportTypeServices = Setting::where('type', 'transport_type')->orderBy('value', 'ASC')->get();
        }
        $transportTypeDes = Setting::where('type', 'transport_type_des')->orderBy('key', 'ASC')->get();
        if (empty($transportTypeDes) || count($transportTypeDes) == 0) {
            $this->insertTransportTypeDes();
            $transportTypeDes = Setting::where('type', 'transport_type_des')->orderBy('key', 'ASC')->get();
        }
        foreach ($transportTypeDes as $item) {
            if ($item->key == 'transport_type_des1') {
                $transportTypeDes1 = $item;
            } else {
                $transportTypeDes2 = $item;
            }
        }
        $data = array(
            'userLogin' => $userLogin,
            'deliveryAddressSend' => $deliveryAddressSend,
            'deliveryAddressReceive' => $deliveryAddressReceive,
            'bookings' => $booking,
            'transportTypeServices' => $transportTypeServices,
            'transportTypeDes1' => @$transportTypeDes1,
            'transportTypeDes2' => @$transportTypeDes2
        );
        return view('front-ent.element.booking', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(BookingRequest $req, $id)
    {
        DB::beginTransaction();
        try {
            $sender_id = null;
            $receiver_id = null;
            // $sender_check = User::where('phone_number', $req->phone_number_fr)->where('role', 'customer')->where('delete_status', 0)->first();
            $receiver_check = User::where('phone_number', $req->phone_number_to)->where('role', 'customer')->where('delete_status', 0)->first();
            // if (!empty($sender_check)) {
            //     $sender_id = $sender_check->id;
            // } else {
            //     $check_sender_delete = User::where('phone_number', $req->phone_number_fr)->where('role', 'customer')->where('delete_status', 1)->first();
            //     if (!empty($check_sender_delete)) {
            //         $check_sender_delete->delete_status = 0;
            //         $check_sender_delete->save();
            //         $sender_id = $check_sender_delete->id;
            //     } else {
            //         $user = new User();
            //         $user->phone_number = $req->phone_number_fr;
            //         $user->save();
            //         $sender_id = $user->id;
            //     }
            // }
            // $sender_id = Auth::user()->id;
            if (!empty($receiver_check)) {
                $receiver_id = $receiver_check->id;
            } else {
                $user = new User();
                $user->phone_number = $req->phone_number_to;
                $user->save();
                $receiver_id = $user->id;
            }
            $booking = Booking::find($id);
            // $booking->sender_id = $sender_id;
            $booking->receiver_id = $receiver_id;
            $booking->name = $req->name;
            $booking->send_name = $req->name_fr;
            $booking->send_phone = $req->phone_number_fr;
            $booking->send_province_id = $req->province_id_fr;
            $booking->send_district_id = $req->district_id_fr;
            $booking->send_ward_id = $req->ward_id_fr;
            $booking->send_homenumber = $req->home_number_fr;
            $booking->send_full_address = $this->getAddress($req->province_id_fr, $req->district_id_fr, $req->ward_id_fr, $req->home_number_fr);
            $booking->receive_name = $req->name_to;
            $booking->receive_phone = $req->phone_number_to;
            $booking->receive_province_id = $req->province_id_to;
            $booking->receive_district_id = $req->district_id_to;
            $booking->receive_ward_id = $req->ward_id_to;
            $booking->receive_homenumber = $req->home_number_to;
            $booking->receive_full_address = $this->getAddress($req->province_id_to, $req->district_id_to, $req->ward_id_to, $req->home_number_to);
            $booking->receive_type = $req->receive_type;
            $booking->price = $req->price;
            $booking->weight = $req->weight;
            $booking->transport_type = $req->transport_type;
            $booking->payment_type = $req->payment_type;
            $booking->COD = $req->cod;
            $booking->other_note = $req->other_note;
            $booking->transport_type_service1 = (isset($req->transport_type_service1) && $req->transport_type_service1 == 1) ? 1 : 0;
            $booking->transport_type_service2 = (isset($req->transport_type_service2) && $req->transport_type_service2 == 1) ? 1 : 0;
            $booking->transport_type_service3 = (isset($req->transport_type_service3) && $req->transport_type_service3 == 1) ? 1 : 0;
            $booking->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
        }
        return redirect(url('front-ent/booking/all'))->with('success', 'cập nhật đơn hàng thành công');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function printBooking($id)
    {
        $booking = Booking::find($id);
        $agency = null;
        switch ($booking->status) {
            case 'taking':
                $shipper_id = BookDelivery::where('book_id', $booking->id)->where('category', 'receive')->where('status', 'processing')->first()->user_id;
                $agency_id = Shipper::where('user_id', $shipper_id)->first();
                if (!empty($agency_id)) {
                    $agency = Agency::find($agency_id->agency_id);
                }
                break;
            case 'sending':
                $check = BookDelivery::where('book_id', $booking->id)->where('category', 'send')->where('status', 'processing')->first();
                if (!empty(($check))) {
                    $shipper_id = $check->user_id;
                } else {
                    $shipper_id = BookDelivery::where('book_id', $booking->id)->where('category', 'receive')->where('status', 'completed')->first()->user_id;
                }
                $agency_id = Shipper::where('user_id', $shipper_id)->first();
                if (!empty($agency_id)) {
                    $agency = Agency::find($agency_id->agency_id);
                }
                break;
            case 'completed':
                $shipper_id = BookDelivery::where('book_id', $booking->id)->where('category', 'send')->where('status', 'completed')->first()->user_id;
                $agency_id = Shipper::where('user_id', $shipper_id)->first();
                if (!empty($agency_id)) {
                    $agency = Agency::find($agency_id->agency_id);
                }
                break;
            default:
                $agency = Agency::where('ward_id', $booking->send_ward_id)->first();
        }
        $user = null;
        if ($agency != null) {
            $collaborator = Collaborator::where('agency_id', $agency->id)->with('users')->first();
            if (!empty($collaborator)) {
                $user = $collaborator->users;
            }
        }
        return view('front-ent.element.booking.print', ['booking' => $booking, 'agency' => $agency, 'collaborator' => $user]);
    }

    public function printBookNewTalking() {
        $bookings = Booking::whereIn('status', ['new', 'taking'])
                        ->where('sender_id', request()->user()->id)
                        ->get();
        $agency = null;
        if (!empty($bookings) && count($bookings) > 0) {
            foreach ($bookings as $booking) {
                if ($booking->status == 'taking') {
                    $shipper_id = BookDelivery::where('book_id', $booking->id)->where('category', 'receive')->where('status', 'processing')->first();
                    if (!empty($shipper_id)) {
                        $shipper_id = $shipper_id->user_id;
                        $agency_id = Shipper::where('user_id', $shipper_id)->first();
                        if (!empty($agency_id)) {
                            $agency = Agency::find($agency_id->agency_id);
                        }
                    }
                } else {
                    $agency = Agency::where('ward_id', $booking->send_ward_id)->first();
                }

                $user = null;
                if ($agency != null) {
                    $collaborator = Collaborator::where('agency_id', $agency->id)->with('users')->first();
                    if (!empty($collaborator)) {
                        $user = $collaborator->users;
                    }
                }
                $booking->agency = $agency;
                $booking->collaborator = $user;
            }
        }
        
        return view('front-ent.element.booking.print_list_book', ['bookings' => $bookings]);
    }

    public function getCreateByImport() {
        $data['provinces'] = Province::where('active', 1)->get();
        return view('front-ent.element.booking.create_by_import', $data);
    }

    public function postCreateByImport() {
        $commonHelper = new CommonHelper();
        $messages = [
            'file.mimes' => 'Định dạng không đúng!',
            'file.required' => 'Vui lòng chọn file excel!'
        ];
        $roles = [
            'file' => 'required|mimes:xls,xlsx'
        ];
        $validator = Validator::make(request()->all(), $roles, $messages);
        if ($validator->fails()) {
            return redirect('front-ent/create-book-by-import')->withErrors($validator)->withInput();
        }

        $file = request()->file;
        $fileName = date('Ymd-His-') . request()->user()->id . '-' . $file->getClientOriginalName();
        $filePath = 'uploads/create_by_imports/';
        $movePath = public_path($filePath);
        if ($file->move($movePath, $fileName)) {
            // tiến hành đọc file excel
            $results = Excel::selectSheets('don_hang')->load($filePath . $fileName, function($reader) {})->get();
            if (empty($results) || count($results) == 0) {
                $validator->errors()->add('file', 'Dữ liệu đơn hàng rỗng. Vui lòng kiểm tra lại!');
                if (file_exists($filePath . $fileName)) {
                    unlink($filePath . $fileName);
                }
                return redirect('front-ent/create-book-by-import')->withErrors($validator)->withInput();
            }

            // insert vào table bookings
            DB::beginTransaction();
            try {
                foreach ($results as $item) {
                    $receiver_check = User::where('phone_number', $item->sdt_nguoi_nhan)->where('role', 'customer')->where('delete_status', 0)->first();
                    if (!empty($receiver_check)) {
                        $receiver_id = $receiver_check->id;
                    } else {
                        $user = new User();
                        $user->phone_number = $item->sdt_nguoi_nhan;
                        $user->save();
                        $receiver_id = $user->id;
                    }

                    $provinceSender = explode('-', $item->thanh_pho_nguoi_gui);
                    $districtSender = explode('-', $item->quan_huyen_nguoi_gui);
                    $wardSender = explode('-', $item->phuong_xa_nguoi_gui);
                    $provinceReceiver = explode('-', $item->thanh_pho_nguoi_nhan);
                    $districtReceiver = explode('-', $item->quan_huyen_nguoi_nhan);
                    $wardReceiver = explode('-', $item->phuong_xa_nguoi_nhan);
                    $receiveType = explode('-', $item->hinh_thuc_gui_hang);
                    $paymentType = explode('-', $item->ghi_chu_bat_buoc);
                    $transportType = explode('-', $item->hinh_thuc_giao_hang);

                    // kiểm tra định dạng địa chỉ
                    if (empty(trim($provinceSender[0]))) {
                        $validator->errors()->add('file', 'Đơn hàng "' . $item->ten_hang . '" có định dạng "thanh_pho_nguoi_gui" chưa đúng. Vui lòng kiểm tra lại!');
                        if (file_exists($filePath . $fileName)) {
                            unlink($filePath . $fileName);
                        }
                        return redirect('front-ent/create-book-by-import')->withErrors($validator)->withInput();
                    }
                    if (empty(trim($districtSender[0]))) {
                        $validator->errors()->add('file', 'Đơn hàng "' . $item->ten_hang . '" có định dạng "quan_huyen_nguoi_gui" chưa đúng. Vui lòng kiểm tra lại!');
                        if (file_exists($filePath . $fileName)) {
                            unlink($filePath . $fileName);
                        }
                        return redirect('front-ent/create-book-by-import')->withErrors($validator)->withInput();
                    }
                    if (empty(trim($wardSender[0]))) {
                        $validator->errors()->add('file', 'Đơn hàng "' . $item->ten_hang . '" có định dạng "phuong_xa_nguoi_gui" chưa đúng. Vui lòng kiểm tra lại!');
                        if (file_exists($filePath . $fileName)) {
                            unlink($filePath . $fileName);
                        }
                        return redirect('front-ent/create-book-by-import')->withErrors($validator)->withInput();
                    }
                    if (empty(trim($provinceReceiver[0]))) {
                        $validator->errors()->add('file', 'Đơn hàng "' . $item->ten_hang . '" có định dạng "thanh_pho_nguoi_nhan" chưa đúng. Vui lòng kiểm tra lại!');
                        if (file_exists($filePath . $fileName)) {
                            unlink($filePath . $fileName);
                        }
                        return redirect('front-ent/create-book-by-import')->withErrors($validator)->withInput();
                    }
                    if (empty(trim($districtReceiver[0]))) {
                        $validator->errors()->add('file', 'Đơn hàng "' . $item->ten_hang . '" có định dạng "quan_huyen_nguoi_nhan" chưa đúng. Vui lòng kiểm tra lại!');
                        if (file_exists($filePath . $fileName)) {
                            unlink($filePath . $fileName);
                        }
                        return redirect('front-ent/create-book-by-import')->withErrors($validator)->withInput();
                    }
                    if (empty(trim($wardReceiver[0]))) {
                        $validator->errors()->add('file', 'Đơn hàng "' . $item->ten_hang . '" có định dạng "phuong_xa_nguoi_nhan" chưa đúng. Vui lòng kiểm tra lại!');
                        if (file_exists($filePath . $fileName)) {
                            unlink($filePath . $fileName);
                        }
                        return redirect('front-ent/create-book-by-import')->withErrors($validator)->withInput();
                    }
                    if (empty(trim($receiveType[0])) || !in_array(trim($receiveType[0]), [1, 2])) {
                        $validator->errors()->add('file', 'Đơn hàng "' . $item->ten_hang . '" có định dạng "hinh_thuc_gui_hang" chưa đúng. Vui lòng kiểm tra lại!');
                        if (file_exists($filePath . $fileName)) {
                            unlink($filePath . $fileName);
                        }
                        return redirect('front-ent/create-book-by-import')->withErrors($validator)->withInput();
                    }
                    if (empty(trim($paymentType[0])) || !in_array(trim($paymentType[0]), [1, 2])) {
                        $validator->errors()->add('file', 'Đơn hàng "' . $item->ten_hang . '" có định dạng "ghi_chu_bat_buoc" chưa đúng. Vui lòng kiểm tra lại!');
                        if (file_exists($filePath . $fileName)) {
                            unlink($filePath . $fileName);
                        }
                        return redirect('front-ent/create-book-by-import')->withErrors($validator)->withInput();
                    }
                    if (empty(trim($transportType[0])) || !in_array(trim($transportType[0]), [1, 2, 3])) {
                        $validator->errors()->add('file', 'Đơn hàng "' . $item->ten_hang . '" có định dạng "hinh_thuc_giao_hang" chưa đúng. Vui lòng kiểm tra lại!');
                        if (file_exists($filePath . $fileName)) {
                            unlink($filePath . $fileName);
                        }
                        return redirect('front-ent/create-book-by-import')->withErrors($validator)->withInput();
                    }
                    if (empty(trim($item->khoi_luong)) || $item->khoi_luong <= 0) {
                        $validator->errors()->add('file', 'Đơn hàng "' . $item->ten_hang . '" có "khoi_luong" chưa đúng. Vui lòng kiểm tra lại!');
                        if (file_exists($filePath . $fileName)) {
                            unlink($filePath . $fileName);
                        }
                        return redirect('front-ent/create-book-by-import')->withErrors($validator)->withInput();
                    }
                    if (empty(trim($item->dia_chi_nguoi_gui))) {
                        $validator->errors()->add('file', 'Đơn hàng "' . $item->ten_hang . '" có "dia_chi_nguoi_gui" chưa đúng. Vui lòng kiểm tra lại!');
                        if (file_exists($filePath . $fileName)) {
                            unlink($filePath . $fileName);
                        }
                        return redirect('front-ent/create-book-by-import')->withErrors($validator)->withInput();
                    }
                    if (empty(trim($item->dia_chi_nguoi_nhan))) {
                        $validator->errors()->add('file', 'Đơn hàng "' . $item->ten_hang . '" có "dia_chi_nguoi_nhan" chưa đúng. Vui lòng kiểm tra lại!');
                        if (file_exists($filePath . $fileName)) {
                            unlink($filePath . $fileName);
                        }
                        return redirect('front-ent/create-book-by-import')->withErrors($validator)->withInput();
                    }
                    if (empty(trim($item->sdt_nguoi_nhan))) {
                        $validator->errors()->add('file', 'Đơn hàng "' . $item->ten_hang . '" có "sdt_nguoi_nhan" chưa đúng. Vui lòng kiểm tra lại!');
                        if (file_exists($filePath . $fileName)) {
                            unlink($filePath . $fileName);
                        }
                        return redirect('front-ent/create-book-by-import')->withErrors($validator)->withInput();
                    }

                    $booking = new Booking();
                    $booking->sender_id = Auth::user()->id;
                    $booking->receiver_id = $receiver_id;
                    $booking->name = $item->ten_hang;
                    $booking->send_name = $item->ten_nguoi_gui;
                    $booking->send_phone = $item->sdt_nguoi_gui;
                    $booking->send_province_id = trim($provinceSender[0]);
                    $booking->send_district_id = trim($districtSender[0]);
                    $booking->send_ward_id = trim($wardSender[0]);
                    $booking->send_homenumber = $item->dia_chi_nguoi_gui;
                    $booking->send_full_address = $this->getAddress($booking->send_province_id, $booking->send_district_id, $booking->send_ward_id, $booking->send_homenumber);

                    $booking->receive_name = $item->ten_nguoi_nhan;
                    $booking->receive_phone = $item->sdt_nguoi_nhan;
                    $booking->receive_province_id = trim($provinceReceiver[0]);
                    $booking->receive_district_id = trim($districtReceiver[0]);
                    $booking->receive_ward_id = trim($wardReceiver[0]);
                    $booking->receive_homenumber = $item->dia_chi_nguoi_nhan;
                    $booking->receive_full_address = $this->getAddress($booking->receive_province_id, $booking->receive_district_id, $booking->receive_ward_id, $booking->receive_homenumber);

                    $booking->receive_type = trim($receiveType[0]);
                    $booking->weight = $item->khoi_luong;
                    $booking->payment_type = trim($paymentType[0]);
                    $booking->COD = $item->so_tien_thu_ho != null ? $item->so_tien_thu_ho : 0;
                    $booking->other_note = $item->ghi_chu_khac;
                    $booking->status = 'new';
                    $booking->uuid = str_random(5);
                    $booking->transport_type = trim($transportType[0]);
                    $booking->price = $commonHelper->searchPrice($booking);

                    // kiểm tra tỉnh/tp áp dụng giao hàng không?
                    $provinceSender = Province::where('id', $booking->send_province_id)->first()->active;
                    if ($provinceSender != 1) {
                        $validator->errors()->add('file', 'Đơn hàng "' . $booking->name . '" chưa áp dụng giao hàng cho khu vực "' . $item->thanh_pho_nguoi_gui . '". Vui lòng kiểm tra lại!');
                        if (file_exists($filePath . $fileName)) {
                            unlink($filePath . $fileName);
                        }
                        return redirect('front-ent/create-book-by-import')->withErrors($validator)->withInput();
                    }
                    $provinceReceiver = Province::where('id', $booking->receive_province_id)->first()->active;
                    if ($provinceReceiver != 1) {
                        $validator->errors()->add('file', 'Đơn hàng "' . $booking->name . '" chưa áp dụng giao hàng cho khu vực "' . $item->thanh_pho_nguoi_nhan . '". Vui lòng kiểm tra lại!');
                        if (file_exists($filePath . $fileName)) {
                            unlink($filePath . $fileName);
                        }
                        return redirect('front-ent/create-book-by-import')->withErrors($validator)->withInput();
                    }

                    // kiểm tra hình thức giao trong thành phố
                    if ($commonHelper->checkTransport($booking) == 2 && $booking->transport_type == 1) {
                        $validator->errors()->add('file', 'Đơn hàng "' . $booking->name . '" không áp dụng trong giao hàng thành phố. Vui lòng kiểm tra lại!');
                        if (file_exists($filePath . $fileName)) {
                            unlink($filePath . $fileName);
                        }
                        return redirect('front-ent/create-book-by-import')->withErrors($validator)->withInput();
                    }

                    // kiểm tra khách lần đầu tiên sử dụng hệ thống (khách mới)
                    $check = Booking::where('sender_id', Auth::user()->id)->count();
                    if ($check == 0) {
                        $booking->is_customer_new = 1;
                    }

                    $booking->save();
                    $booking->uuid .= $booking->id;
                    $booking->save();
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $validator->errors()->add('file', 'Quá trình tạo đơn hàng loạt bị lỗi. Vui lòng kiểm tra lại!');
                if (file_exists($filePath . $fileName)) {
                    unlink($filePath . $fileName);
                }
                return redirect('front-ent/create-book-by-import')->withErrors($validator)->withInput();
            }
            return redirect('front-ent/booking/all');    
        } else {
            $validator->errors()->add('file', 'Không thể upload file Excel. Vui lòng kiểm tra lại!');
            return redirect('front-ent/create-book-by-import')->withErrors($validator)->withInput();
        }
    }

    // đếm đơn hàng theo trạng thái
    private function countBookStatus() {
        $data = [
            'all' => 0,
            'received' => 0,
            'sent' => 0,
            'return' => 0,
            'cancel' => 0
        ];
        $data['all'] = Booking::where('sender_id', Auth::user()->id)->count();
        $data['received'] = Booking::where('sender_id', Auth::user()->id)->where('status', 'sending')->count();
        $data['sent'] = Booking::where('sender_id', Auth::user()->id)->where('status', 'completed')->count();
        $data['return'] = Booking::where('sender_id', Auth::user()->id)->where('status', 'return')->count();
        $data['cancel'] = Booking::where('sender_id', Auth::user()->id)->where('status', 'cancel')->count();
        return $data;
    }

    public function exportBooking(Request $request)
    {
        $path = 'donhang_tatca.xlsx';
        $booking = Booking::where('sender_id', request()->user()->id)
                        ->whereDate('created_at', '>=', request()->date_assign)
                        ->whereDate('created_at', '<=', request()->date_assign_to);

        if (isset(request()->status) && !empty(request()->status)) {
            if (request()->status != 'all') {
                $booking = $booking->where('status', request()->status);
            }
        }

        $booking = $booking->orderBy('bookings.created_at', 'desc')->get();
        $result = [];
        $num = 1;
        foreach ($booking as $b) {
            $status = '';
            if ($b->status == 'new') {
                $status = 'Mới';
            } elseif ($b->status == 'return') {
                $status = 'Trả lại';
            } elseif ($b->status == 'delay') {
                $status = 'Delay';
            } elseif ($b->status == 'cancel') {
                $status = 'Hủy';
            } elseif ($b->status == 'taking') {
                $status = 'Đang đi lấy';
            } elseif ($b->status == 'sending') {
                $status = 'Đang giao hàng';
            } elseif ($b->status == 'completed') {
                $status = 'Đã giao hàng';
            }
            
            $data = array();
            $data['Stt'] = $num;
            $data['uuid'] = $b->uuid;
            $data['name'] = $b->name;
            $data['send_name'] = $b->send_name;
            $data['send_full_address'] = $b->send_full_address;
            $data['receive_name'] = $b->receive_name;
            $data['receive_phone'] = $b->receive_phone;
            $data['receive_full_address'] = $b->receive_full_address;
            $data['price'] = $b->price;
            $data['COD'] = $b->COD;
            $data['status'] = $status;
            $data['created_at'] = $b->created_at;
            $data['completed_at'] = $b->completed_at;
            $data['payment_type'] = ($b->payment_type == 1) ? 'Người gửi trả cước' : 'Người nhận trả cước';
            $data['other_note'] = $b->other_note;
            $data['note'] = $b->note;

            $result[] = $data;
            $num++;
        }
        $file_path = public_path('/excel_temp/customers/' . $path);
        Excel::load($file_path, function ($reader) use ($result, $request) {
            $reader->sheet('Sheet1', function ($sheet) use ($result, $request) {
                $cellDate = 'P1';
                $sheet->cell($cellDate, function ($cell) use ($request) {
                    $cell->setValue('Ngày: ' . $request->date_assign . ' -> ' . $request->date_assign_to);   
                });
                $sheet->fromArray($result, null, 'A6', true, false);
            });

        })->setFilename('DanhSachDonHang')->export('xlsx');
    }

    private function insertTransportTypeService() {
        DB::table('settings')->insert([
            array(
                'type' => 'transport_type',
                'key' => 'transport_type_service1',
                'name' => 'Giao hẹn giờ (phương sai 30 phút)',
                'value' => 10000,
                'description' => 'Chú thích Giao hẹn giờ (phương sai 30 phút)'
            ),
            array(
                'type' => 'transport_type',
                'key' => 'transport_type_service2',
                'name' => 'Giao 1 phần, trả lại 1 phần',
                'value' => 11000,
                'description' => 'Chú thích Giao 1 phần, trả lại 1 phần'
            ),
            array(
                'type' => 'transport_type',
                'key' => 'transport_type_service3',
                'name' => 'Giao bến xe, nhà xe',
                'value' => 12000,
                'description' => 'Chú thích Giao bến xe, nhà xe'
            )
        ]);
    }

    private function insertTransportTypeDes() {
        DB::table('settings')->insert([
            array(
                'type' => 'transport_type_des',
                'key' => 'transport_type_des1',
                'name' => 'Giao chuẩn',
                'value' => 0,
                'description' => 'Chú thích cho giao chuẩn'
            ),
            array(
                'type' => 'transport_type_des',
                'key' => 'transport_type_des2',
                'name' => 'Giao siêu tốc',
                'value' => 0,
                'description' => 'Chú thích Giao siêu tốc'
            )
        ]);
    }

    // public function getExportExcelExampleBook() {
    //     $data['title'] = [
    //         ['sdt_nguoi_gui', 'ten_nguoi_gui', 'thanh_pho_nguoi_gui', 'quan_huyen_nguoi_gui', 'phuong_xa_nguoi_gui', 'dia_chi_nguoi_gui', 'dia_chi_day_du_nguoi_gui', 'hinh_thuc_gui_hang', 'sdt_nguoi_nhan', 'ten_nguoi_nhan', 'thanh_pho_nguoi_nhan', 'quan_huyen_nguoi_nhan', 'phuong_xa_nguoi_nhan', 'dia_chi_nguoi_nhan', 'dia_chi_day_du_nguoi_nhan', 'ten_hang', 'khoi_luong', 'hinh_thuc_giao_hang', 'so_tien_thu_ho', 'ghi_chu_bat_buoc', 'ghi_chu_khac']
    //     ];
    //     $provinceSender = !empty(Auth::user()->province_id) ? Province::find(Auth::user()->province_id) : Province::find(50);
    //     $districtSender = !empty(Auth::user()->district_id) ? District::find(Auth::user()->district_id) : District::find(573);
    //     $wardSender = !empty(Auth::user()->ward_id) ? Ward::find(Auth::user()->ward_id) : Ward::find(9508);
    //     $provinceReceiver = Province::find(50);
    //     $districtReceiver = District::find(573);
    //     $wardReceiver = Ward::find(9508);
    //     for ($i = 1; $i <= 3; $i++) {
    //         $tmp = [
    //             Auth::user()->phone_number, Auth::user()->name,
    //             $provinceSender->id . ' - ' . $provinceSender->name,
    //             $districtSender->id . ' - ' . $districtSender->name,
    //             $wardSender->id . ' - ' . $wardSender->name,
    //             empty(Auth::user()->home_number) ? '710 Nguyễn Kiệm' : Auth::user()->home_number,
    //             empty(Auth::user()->home_number) ? '710 Nguyễn Kiệm' . ', ' . $wardSender->name . ', ' . $districtSender->name . ', ' . $provinceSender->name : Auth::user()->home_number . ', ' . $wardSender->name . ', ' . $districtSender->name . ', ' . $provinceSender->name,
    //             ($i % 2 != 0) ? '1 - Lấy hàng tại nhà' : '2 - Giao hàng đến bưu cục',
    //             'Số ĐT người nhận ' . $i,
    //             'Tên người nhận ' . $i,
    //             $provinceReceiver->id . ' - ' . $provinceReceiver->name,
    //             $districtReceiver->id . ' - ' . $districtReceiver->name,
    //             $wardReceiver->id . ' - ' . $wardReceiver->name,
    //             '1 Nguyễn Kiệm',
    //             '1 Nguyễn Kiệm' . ', ' . $wardReceiver->name . ', ' . $districtReceiver->name . ', ' . $provinceReceiver->name,
    //             'Tên đơn hàng ' . $i,
    //             200 + $i,
    //             ($i == 1) ? '1 - Giao trong thành phố' : (($i == 2) ? '2 - Giao tiết kiệm' : '3 - Giao nhanh'),
    //             $i == 1 ? 0 : 110000 + $i,
    //             $i == 1 ? '1 - Người gửi trả cước' : '2 - Người nhận trả cước',
    //             'Khách ghi chú ' . $i
    //         ];
    //         $data['books'][] = $tmp;
    //     }
    //     Excel::create('file_excel_mau', function($excel) use($data) {
    //         $excel->sheet('don_hang', function($sheet) use($data) {
    //             $sheet->rows($data['title']);
    //             $sheet->rows($data['books']);
    //             $sheet->cell('A1:U1', function($cell) {
    //                 $cell->setBackground('#40E0D0');
    //             });
    //         });
    //         $excel->sheet('hinh_thuc_gui_hang', function($sheet) use($data) {
    //             $sheet->rows([
    //                 ['1 - Lấy hàng tại nhà'],
    //                 ['2 - Giao hàng đến bưu cục']
    //             ]);
    //         });
    //         $excel->sheet('hinh_thuc_giao_hang', function($sheet) use($data) {
    //             $sheet->rows([
    //                 ['1 - Giao trong thành phố (Giao ngay)'],
    //                 ['2 - Giao tiết kiệm'],
    //                 ['3 - Giao nhanh']
    //             ]);
    //         });
    //         $excel->sheet('ghi_chu_bat_buoc', function($sheet) use($data) {
    //             $sheet->rows([
    //                 ['1 - Người gửi trả cước'],
    //                 ['2 - Người nhận trả cước']
    //             ]);
    //         });
    //         // $excel->sheet('tmp', function($sheet) use($data) {
    //         //     // $objects = District::where('provinceId', 48)->orderBy('name', 'ASC')->get();
    //         //     $objects = Ward::where('districtId', 563)->orderBy('name', 'ASC')->get();
    //         //     foreach ($objects as $object) {
    //         //         $tmp = [];
    //         //         $tmp[] = $object->id . ' - ' . $object->name;
    //         //         // $tmps[] = $tmp;
    //         //         $sheet->rows([$tmp]);
    //         //     }
    //         // });
    //         // $districtBDs = District::where('provinceId', 50)->orderBy('name', 'ASC')->get();
    //         // foreach ($districtBDs as $districtBD) {
    //         //     $excel->sheet($districtBD->id.'-', function($sheet) use($districtBD) {
    //         //         // $objects = District::where('provinceId', 48)->orderBy('name', 'ASC')->get();
    //         //         $objects = Ward::where('districtId', $districtBD->id)->orderBy('name', 'ASC')->get();
    //         //         foreach ($objects as $object) {
    //         //             $tmp = [];
    //         //             $tmp[] = $object->id . ' - ' . $object->name;
    //         //             // $tmps[] = $tmp;
    //         //             $sheet->rows([$tmp]);
    //         //         }
    //         //     });
    //         // }
    //     })->download('xlsx');
    // }
}
