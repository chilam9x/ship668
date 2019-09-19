<?php

namespace App\Http\Controllers\API;

use App\Models\Agency;
use App\Models\BookDelivery;
use App\Models\Province;
use App\Models\District;
use App\Models\Ward;
use App\Models\ReportImage;
use App\Models\SendAndReceiveAddress;
use App\Models\Shipper;
use App\Models\ShipperRevenue;
use App\Models\Booking;
use App\Models\DeliveryAddress;
use App\Models\User;
use App\Models\Notification;
use App\Models\NotificationUser;
use App\Models\ManagementWardScope;
use App\Models\ManagementScope;
use App\Models\ShipperLocation;
use Carbon\Carbon;
use function dd;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;
use function in_array;
use function is;
use Uuid,
    Auth;
use Illuminate\Support\Facades\DB;
use App\Helpers\NotificationHelper;
use App\Jobs\NotificationJob;

class OrderController extends ApiController {

    protected function changeStatus($status) {
        switch ($status) {
            case "new":
                return 'mới';
                break;
            case "received":
                return 'Đã nhận';
                break;
            case "sent":
                return 'Đã giao';
                break;
            case "delay":
                return 'delay';
                break;
            case "cancel":
                return 'hủy';
                break;
            default:
                return '';
        }
    }

    private function getShipperScope() {
        $wards = ManagementWardScope::where('shipper_id', request()->user()->id)->pluck('ward_id');
        return $wards;
    }

    protected function loadAgency(Request $req)
    {
        $limit = $req->get('limit', 10);
//        $earthRadius = 6372.795477598;
        $distanceQuery = DB::raw("(6372.795477598 * 2 * ATAN2(SQRT(SIN(RADIANS(lat - {$req->lat}) / 2) * SIN(RADIANS(lat - {$req->lat}) / 2) + COS(RADIANS({$req->lat})) * COS(RADIANS(lat))
         * SIN(RADIANS(lng - {$req->lng}) / 2) * SIN(RADIANS(lng - {$req->lng}) / 2)), SQRT(1 - SIN(RADIANS(lat - {$req->lat}) / 2) * SIN(RADIANS(lat - {$req->lat}) / 2) + COS(RADIANS({$req->lat}))
          * COS(RADIANS(lat)) * SIN(RADIANS(lng - {$req->lng}) / 2) * SIN(RADIANS(lng - {$req->lng}) / 2)))) AS distance");
        $query = Agency::select('*', $distanceQuery)->orderBy('distance', 'asc')->paginate($limit);
        return $this->apiOk($query);
    }

    protected function setSendOrReceiveAddress($req, $type) {
        $user_id = $req->sender_id;
        $check = SendAndReceiveAddress::where('user_id', $user_id);
        if ($type == 'send') {
            $check = $check->where('phone', $req->send_phone)->where('name', $req->send_name)->where('type', 'send')->where('province_id', $req->send_province_id)
                ->where('district_id', $req->send_district_id)->where('ward_id', $req->send_ward_id)->where('home_number', $req->send_homenumber)->first();
        }
        if ($type == 'receive') {
            $check = $check->where('phone', $req->receive_phone)->where('name', $req->receive_name)->where('type', 'receive')->where('province_id', $req->receive_province_id)
                ->where('district_id', $req->receive_district_id)->where('ward_id', $req->receive_ward_id)->where('home_number', $req->receive_homenumber)->first();
        }
        if (!empty((array) $check)) {
            $check->updated_at = Carbon::now();
            $check->save();
        } else {
            $data = new SendAndReceiveAddress();
            $data->user_id = $user_id;
            if ($type == 'send') {
                $data->phone = $req->send_phone;
                $data->name = $req->send_name;
                $data->province_id = $req->send_province_id;
                $data->district_id = $req->send_district_id;
                $data->ward_id = $req->send_ward_id;
                $data->home_number = $req->send_homenumber;
                $data->full_address = $req->send_full_address;
                $data->type = 'send';
            } else {
                $data->phone = $req->receive_phone;
                $data->name = $req->receive_name;
                $data->province_id = $req->receive_province_id;
                $data->district_id = $req->receive_district_id;
                $data->ward_id = $req->receive_ward_id;
                $data->home_number = $req->receive_homenumber;
                $data->full_address = $req->receive_full_address;
                $data->type = 'receive';
            }
            $data->save();
        }
        return $this->apiOk('success');
    }

    public function getBooking(Request $req) {
        if (isset($this->user)) {
            $query = DeliveryAddress::where('user_id', $this->user->id)->with('users', 'provinces', 'districts', 'wards')->get();
            $data = [];
            foreach ($query as $q) {
                $result['id'] = $q->id;
                $result['phone'] = $q->users->phone_number;
                $result['name'] = $q->users->name;
                $result['address'] = [
                    'province_id' => $q->province_id,
                    'district_id' => $q->district_id,
                    'ward_id' => $q->ward_id,
                    'home_number' => $q->home_number,
                    'full_address' => $q->home_number . ', ' . $q->wards->name . ', ' . $q->districts->name . ', ' . $q->provinces->name
                ];
                $result['default'] = $q->default;
                $data[] = $result;
            }
            return $this->apiOk(!empty($data) ? $data : null);
        } else {
            return $this->apiOk(null);
        }
    }

    public function booking(Request $req)
    {
        $validate = [
            'sender.phone' => 'required|min:10',
            'sender.name' => 'required|string',
            'sender.address.province' => 'required|integer',
            'sender.address.district' => 'required|integer',
            'sender.address.ward' => 'required|integer',
            'sender.address.homenumber' => 'required',
            'sender.address.full_address' => 'required',

            'receiver.address.province' => 'required|integer',
            'receiver.address.district' => 'required|integer',
            'receiver.address.ward' => 'required|integer',
            'receiver.address.homenumber' => 'required',
            'receiver.address.full_address' => 'required',
            'receiver.name' => 'required|string',
            'receiver.phone' => 'required|string',
            'price' => 'required|between:0,99.99',
            'weight' => 'required|between:0,99.99',
            'transport_type' => 'required|integer',
            'receive_type' => 'required|integer',
            'payment_type' => 'required|integer',
            'COD' => 'required|numeric|min:0',
        ];
        /* if ($req->other_note){
             $validate['other_note'] = 'regex:/(^[A-Za-z0-9 ]+$)+/';
         }*/
        $validator = Validator::make($req->all(), $validate);
        $validator->after(function ($validator) use ($req) {
            if ($req->sender['address']['province'] != null) {
                $pr = Province::where('id', $req->sender['address']['province'])->first()->active;
                if ($pr != 1) {
                    $validator->errors()->add('sender.address.province', 'Chưa áp dụng giao hàng khu vực này');
                }
            }
            if ($req->receiver['address']['province'] != null) {
                $pr = Province::where('id', $req->receiver['address']['province'])->first()->active;
                if ($pr != 1) {
                    $validator->errors()->add('receiver.address.province', 'Chưa áp dụng giao hàng khu vực này');
                }
            }
            if ($req->sender['address']['district'] != null) {
                $pr = District::where('id', $req->sender['address']['district'])->first()->allow_booking;
                if ($pr != 1) {
                    $validator->errors()->add('sender.address.district', 'Chưa áp dụng giao hàng khu vực này');
                }
            }
            if ($req->receiver['address']['district'] != null) {
                $pr = District::where('id', $req->receiver['address']['district'])->first()->allow_booking;
                if ($pr != 1) {
                    $validator->errors()->add('receiver.address.district', 'Chưa áp dụng giao hàng khu vực này');
                }
            }
        });
        if ($validator->fails()) {
            return $this->apiError($validator->errors()->first());
        }
        $receiver = User::where('phone_number', $req->receiver['phone'])->first();
        DB::beginTransaction();
        try {
            $book = new Booking();
            $book->sender_id = $req->user()->id;
            if (!empty($receiver)) {
                $book->receiver_id = $receiver->id;
            }
            $book->name = $req->name;
            $book->send_name = $req->sender['name'];
            $book->send_phone = $req->sender['phone'];
            $book->send_province_id = $req->sender['address']['province'];
            $book->send_district_id = $req->sender['address']['district'];
            $book->send_ward_id = $req->sender['address']['ward'];
            $book->send_homenumber = $req->sender['address']['homenumber'];
            $book->send_full_address = $req->sender['address']['full_address'];
            $book->receive_name = $req->receiver['name'];
            $book->receive_phone = $req->receiver['phone'];
            $book->receive_province_id = $req->receiver['address']['province'];
            $book->receive_district_id = $req->receiver['address']['district'];
            $book->receive_ward_id = $req->receiver['address']['ward'];
            $book->receive_homenumber = $req->receiver['address']['homenumber'];
            $book->receive_full_address = $req->receiver['address']['full_address'];
            $book->receive_type = $req->receive_type;
            $book->price = $req->price;
            $book->weight = $req->weight;
            $book->transport_type = $req->transport_type;
            $book->payment_type = $req->payment_type;
            $book->COD = $req->COD;
            $book->other_note = $req->other_note;
            $book->status = 'new';
            $book->transport_type_services = $req->transport_type_services;
            $book->transport_type_service1 = (isset($req->transport_type_service1) && $req->transport_type_service1 == 1) ? 1 : 0;
            $book->transport_type_service2 = (isset($req->transport_type_service2) && $req->transport_type_service2 == 1) ? 1 : 0;
            $book->transport_type_service3 = (isset($req->transport_type_service3) && $req->transport_type_service3 == 1) ? 1 : 0;
            // kiểm tra khách lần đầu tiên sử dụng hệ thống (khách mới)
            $check = Booking::where('sender_id', $req->user()->id)->count();
            if ($check == 0) {
                $book->is_customer_new = 1;
            }
            
            $book->save();
            $uuid = Booking::find($book->id);
            $uuid->uuid = str_random(5) . $uuid->id;
            $uuid->save();
            $this->setSendOrReceiveAddress($book, 'send');
            $this->setSendOrReceiveAddress($book, 'receive');
            DB::commit();
            
            // Thông báo tới admin có đơn hàng mới
            $bookingTmp = $book->toArray();
            $bookingTmp['uuid'] = $uuid->uuid;
            // $this->addNotificationBook($bookingTmp, $title, $userIds = []);
            dispatch(new NotificationJob($bookingTmp, 'admin', ' vừa được tạo', 'push_order'));
            dispatch(new NotificationJob($bookingTmp, 'customer', ' vừa được tạo', 'push_order'));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->apiError($e->getMessage());
        }
        return $this->apiOk($uuid);
    }

    

    public function getBookShipper(Request $req) {
        $shipperOnline = ShipperLocation::where('user_id', request()->user()->id)->where('online', 1)->first();
        if (empty($shipperOnline)) {
            return $this->apiErrorWithStatus(403, 'Kích hoạt chế độ Đang hoạt động để thấy đơn hàng1!');
        }

        $limit = $req->get('limit', 10);
        $query = Booking::query()
            ->join('book_deliveries', 'bookings.id', '=', 'book_deliveries.book_id');
        $query->where('bookings.status', '!=', 'cancel')->where('book_deliveries.user_id', $req->user()->id);
        if (isset($req->category)) {
            if ($req->category == 'receive') {
                $query->where(function ($query) {
                    $query->where('book_deliveries.category', '=', 'receive')->where('book_deliveries.status', 'processing');
                });
                $query->where('sub_status', '!=', 'deny')->where('bookings.status', '!=', 'completed');
                if (isset($req->keyword) && !empty($req->keyword)) {
                    $query->where(function($q) use($req) {
                        $q->where('send_name', 'LIKE', '%' . $req->keyword . '%');
                        $q->orWhere('send_phone', 'LIKE', '%' . $req->keyword . '%');
                        $q->orWhere('send_full_address', 'LIKE', '%' . $req->keyword . '%');
                        $q->orWhere('name', 'LIKE', '%' . $req->keyword . '%');
                        $q->orWhere('uuid', 'LIKE', '%' . $req->keyword . '%');
                    });
                }
                if (isset($req->district_id) && !empty($req->district_id)) {
                    $query->where('bookings.send_district_id', 'LIKE', '%' . $req->district_id . '%');
                }
                if (isset($req->ward_id) && !empty($req->ward_id)) {
                    $query->where('bookings.send_ward_id', 'LIKE', '%' . $req->ward_id . '%');
                }
            }
            if ($req->category == 'send') {
                $query->where(function ($query) {
                    $query->where('book_deliveries.category', '=', 'send')->where('book_deliveries.sending_active', '=', 1)->where('book_deliveries.status', 'processing');
                });
                $query->where('bookings.status', '!=', 'completed');
                $query->whereNotIn('sub_status',['deny']);
                if (isset($req->keyword) && !empty($req->keyword)) {
                    $query->where(function($q) use($req) {
                        $q->where('receive_name', 'LIKE', '%' . $req->keyword . '%');
                        $q->orWhere('receive_phone', 'LIKE', '%' . $req->keyword . '%');
                        $q->orWhere('receive_full_address', 'LIKE', '%' . $req->keyword . '%');
                        $q->orWhere('name', 'LIKE', '%' . $req->keyword . '%');
                        $q->orWhere('uuid', 'LIKE', '%' . $req->keyword . '%');
                    });
                }
                if (isset($req->district_id) && !empty($req->district_id)) {
                    $query->where('bookings.receive_district_id', 'LIKE', '%' . $req->district_id . '%');
                }
                if (isset($req->ward_id) && !empty($req->ward_id)) {
                    $query->where('bookings.receive_ward_id', 'LIKE', '%' . $req->ward_id . '%');
                }
            }
            if ($req->category == 'return') {
                $query->where('bookings.status', 'return');
                
                $query->where(function ($query) {
                    $query->where('book_deliveries.category', '=', 'return');
                   $query->where('book_deliveries.status', 'processing');
                });
                $query->where('sub_status', '!=', 'deny')->where('bookings.status', '!=', 'completed');
                if (isset($req->keyword) && !empty($req->keyword)) {
                    $query->where(function($q) use($req){
                        $q->where('send_name', 'LIKE', '%' . $req->keyword . '%');
                        $q->orWhere('send_phone', 'LIKE', '%' . $req->keyword . '%');
                        $q->orWhere('send_full_address', 'LIKE', '%' . $req->keyword . '%');
                        $q->orWhere('name', 'LIKE', '%' . $req->keyword . '%');
                        $q->orWhere('uuid', 'LIKE', '%' . $req->keyword . '%');
                    });
                }
                if (isset($req->district_id) && !empty($req->district_id)) {
                    $query->where('bookings.send_district_id', 'LIKE', '%' . $req->district_id . '%');
                }
                if (isset($req->ward_id) && !empty($req->ward_id)) {
                    $query->where('bookings.send_ward_id', 'LIKE', '%' . $req->ward_id . '%');
                }
            }
        }
        $rows = $query->select('bookings.*', 'bookings.transport_type', 'book_deliveries.id', 'book_deliveries.user_id', 'book_deliveries.book_id', 'book_deliveries.category', 'book_deliveries.status', 'book_deliveries.last_move', 'book_deliveries.delay_total', 'book_deliveries.sending_active', 'book_deliveries.created_at as assign_time', 'book_deliveries.completed_at as completed_time')
            ->with('transactionTypeService.service')
            ->orderBy('is_prioritize', 'desc')
            ->orderBy('assign_time', 'desc')
            ->paginate($limit);

        foreach ($rows->items() as $item) {
            $item->reportDeliverImage;
            $item->sender_info = [
                'name' => $item->send_name,
                'phone' => $item->send_phone,
                'address' => [
                    'province_id' => $item->send_province_id,
                    'district_id' => $item->send_district_id,
                    'ward_id' => $item->send_ward_id,
                    'home_number' => $item->send_homenumber,
                    'full_address' => $item->send_full_address
                ],
                'location' => [
                    'lat' => $item->send_lat,
                    'lng' => $item->send_lng
                ]
            ];
            $item->receiver_info = [
                'name' => $item->receive_name,
                'phone' => $item->receive_phone,
                'address' => [
                    'province_id' => $item->receive_province_id,
                    'district_id' => $item->receive_district_id,
                    'ward_id' => $item->receive_ward_id,
                    'home_number' => $item->receive_homenumber,
                    'full_address' => $item->receive_full_address
                ],
                'location' => [
                    'lat' => $item->receive_lat,
                    'lng' => $item->receive_lng
                ]
            ];
            unset(
                $item->send_province_id, $item->send_district_id, $item->send_ward_id, $item->send_homenumber, $item->send_full_address, $item->send_name, $item->send_phone, $item->receive_name, $item->receive_phone, $item->receive_province_id, $item->receive_district_id, $item->receive_ward_id, $item->receive_homenumber, $item->receive_full_address, $item->send_lat, $item->send_lng, $item->receive_lat, $item->receive_lng);
        }
        return $this->apiOk($rows);
    }
        
    public function newGetBookShipperWait(Request $req) {
        
    }

    public function getBookShipperWait(Request $req) {
        $shipperOnline = ShipperLocation::where('user_id', request()->user()->id)->where('online', 1)->first();
        if (empty($shipperOnline)) {
            return $this->apiErrorWithStatus(403, 'Kích hoạt chế độ Đang hoạt động để thấy đơn hàng!');
        }
        
        $limit = $req->get('limit', 10);

        $messages = [
            'category.required' => 'Vui lòng chọn hình thức: đi lấy/đi giao/đi trả'
        ];
        $roles = [
            'category' => 'required'
        ];
        $validator = Validator::make(request()->all(), $roles, $messages);
        if ($validator->fails()) {
            return response([
                'msg' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }
        // đon hàng mới chưa phân công + delay
        if ($req->category == 'new-delay') {
            return $this->newGetBookShipperWait($req);
        }
        // đơn hàng mới chưa phân công
        if ($req->category == 'new') {
            // kiểm tra shipper có được thấy những đơn hàng mới không
            if ($req->user()->auto_receive != 1) {
                return $this->apiError('Shipper chưa được quyền thấy những đơn hàng mới đợi lấy');
            }
            // bao gồm cả đơn hàng delay
            $rows = Booking::where(function($q) {
                    $q->where('status', 'new');
                    $q->orWhere(function($q) {
                        $q->where('bookings.status', 'taking');
                        $q->where('bookings.sub_status', 'delay');
                        $q->whereHas('deliveries', function($q) {
                            //$q->where('book_deliveries.user_id',0);
                            $q->where('book_deliveries.status', '=', 'delay');
                            $q->where('book_deliveries.category', 'receive');
                        });
                    });
                })->groupBy('sender_id')
                ->whereIn('send_ward_id', $this->getShipperScope());

            if (isset($req->district_id) && !empty($req->district_id)) {
                $rows = $rows->where('send_district_id', $req->district_id);
            }
            if (isset($req->ward_id) && !empty($req->ward_id)) {
                $rows = $rows->where('send_ward_id', $req->ward_id);
            }
            if (isset($req->keyword) && !empty($req->keyword)) {
                $rows = $rows->where(function($q) use($req) {
                    $q->where('send_name', 'LIKE', '%' . $req->keyword . '%');
                    $q->orWhere('send_phone', 'LIKE', '%' . $req->keyword . '%');
                    $q->orWhere('name', 'LIKE', '%' . $req->keyword . '%');
                    $q->orWhere('uuid', 'LIKE', '%' . $req->keyword . '%');
                    $q->orWhere('send_full_address', 'LIKE', '%' . $req->keyword . '%');
                });
            }

            $rows = $rows->select('status', 'id', 'uuid', 'sender_id', 'created_at', 'send_name', 'send_phone', 'send_full_address', 'send_province_id', 'send_district_id', 'send_ward_id', 'send_homenumber', 'is_prioritize', 'name', DB::raw('count(sender_id) as total_book'))
                    ->orderBy('is_prioritize', 'DESC')
                    ->orderBy('bookings.created_at', 'desc')
                   ->paginate($limit);
//            dump($rows->toSql());
//                 $query = str_replace(array('?'), array('\'%s\''), $rows->toSql());
//    $query = vsprintf($query, $rows->getBindings());
//    dump($query);

            foreach ($rows as $item) {
                $address = str_replace($item->send_homenumber . ', ', '', $item->send_full_address);
                $item->send_full_address = $address;
               
                $item->sender_info = [
                    'name' => $item->send_name,
                    'phone' => $item->send_phone,
                    'address' => [
                        'province_id' => $item->send_province_id,
                        'district_id' => $item->send_district_id,
                        'ward_id' => $item->send_ward_id,
                        'home_number' => $item->send_homenumber,
                        'full_address' => $address
                    ]
                ];
                $item->canEdit = 0;
            }
            return $this->apiOk($rows);
        }

        // đơn hàng đi giao chưa phân công
        if ($req->category == 'send') {
            // kiểm tra shipper có được thấy những đơn hàng đi giao không
            if ($req->user()->auto_send != 1) {
                return $this->apiError('Shipper chưa được quyền thấy những đơn hàng đợi đi giao');
            }

            $rows = Booking::join('book_deliveries as bd', 'bookings.id', '=', 'bd.book_id')
                    ->join('users', 'bd.user_id', '=', 'users.id')
                    ->leftJoin('agencies', 'bookings.agency_confirm_id', '=', 'agencies.id')
                ->where(function($q) {
                    $q->where(function($q1) {
                            $q1->where('bookings.status', 'sending')
                                ->where('bd.status', 'completed')
                                ->where('bd.category', 'receive');
                        });
                    $q->orWhere(function($q2) {
                            $q2->where('bookings.status', 'return')
                                ->where('bd.status', 'deny')
                                ->where('bd.category', 'return');
                        });
                    })
                    ->where('bookings.status', '!=', 'cancel')
                    ->where('bookings.status', '!=', 'completed')
                    ->where('sub_status', '!=', 'deny')
                    ->where('bd.sending_active', 1)
                    ->whereIn('receive_ward_id', $this->getShipperScope());

            if (isset($req->district_id) && !empty($req->district_id)) {
                $rows = $rows->where('receive_district_id', $req->district_id);
            }
            if (isset($req->ward_id) && !empty($req->ward_id)) {
                $rows = $rows->where('receive_ward_id', $req->ward_id);
            }
            if (isset($req->keyword) && !empty($req->keyword)) {
                $rows = $rows->where(function($q) use($req){
                    $q->where('receive_name', 'LIKE', '%' . $req->keyword . '%');
                    $q->orWhere('receive_phone', 'LIKE', '%' . $req->keyword . '%');
                    $q->orWhere('bookings.name', 'LIKE', '%' . $req->keyword . '%');
                    $q->orWhere('bookings.uuid', 'LIKE', '%' . $req->keyword . '%');
                    $q->orWhere('bookings.receive_full_address', 'LIKE', '%' . $req->keyword . '%');
                });
            }
            if (isset($req->agency_id) && !empty($req->agency_id)) {
                $rows = $rows->where('agency_confirm_id', $req->agency_id);
            }

            $rows = $rows->select('bookings.status', 'bookings.id', 'bookings.uuid', 'bookings.created_at', 'receive_name', 'bookings.transport_type', 'bookings.send_name', 'bookings.send_phone', 'bookings.send_province_id', 'bookings.send_district_id', 'bookings.send_ward_id', 'bookings.send_homenumber', 'bookings.send_full_address', 'receive_phone', 'bookings.receive_full_address', 'receive_province_id', 'receive_district_id', 'receive_ward_id', 'receive_homenumber', 'is_prioritize', 'bookings.name', 'bookings.note', 'bookings.other_note', 'users.name as shipper_receive_name', 'users.phone_number as shipper_receive_phone', 'bd.id as task_id', 'bookings.agency_confirm_id', 'agencies.name as agency_name')
                    ->orderBy('is_prioritize', 'DESC')
                    ->orderBy('bookings.created_at', 'desc')
                    ->paginate($limit);

            foreach ($rows as $item) {
                if (!empty($item->agency_confirm_id) && !empty($item->agency_name)) {
                    $item->agency_confirm = $item->agency_name;
                } else {
                    $item->agency_confirm = $item->shipper_receive_name . ' - ' . $item->shipper_receive_phone;
                }
                $receviceAddress =  str_replace($item->receive_homenumber . ', ', '', $item->receive_full_address);
                $item->receive_full_address = $receviceAddress;
                $item->receiver_info = [
                    'name' => $item->receive_name,
                    'phone' => $item->receive_phone,
                    'address' => [
                        'province_id' => $item->receive_province_id,
                        'district_id' => $item->receive_district_id,
                        'ward_id' => $item->receive_ward_id,
                        'home_number' => $item->receive_homenumber,
                        'full_address' => $receviceAddress
                    ]
                ];
                $address = str_replace($item->send_homenumber . ', ', '', $item->send_full_address);
                $item->sender_info = [
                    'name' => $item->send_name,
                    'phone' => $item->send_phone,
                    'address' => [
                        'province_id' => $item->send_province_id,
                        'district_id' => $item->send_district_id,
                        'ward_id' => $item->send_ward_id,
                        'home_number' => $item->send_homenumber,
                        'full_address' => $address
                    ]
                ];
                $item->canEdit = 0;
            }
            return $this->apiOk($rows);
        }
        return $this->apiOk('Không có dữ liệu!');
    }

    public function getAreaScope() {
        $messages = [
            'category.required' => 'Vui lòng chọn hình thức: đi lấy/đi giao/đi trả'
        ];
        $roles = [
            'category' => 'required'
        ];
        $validator = Validator::make(request()->all(), $roles, $messages);
        if ($validator->fails()) {
            return response([
                'msg' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        // đơn hàng mới chưa phân công
        if (request()->category == 'new') {
            // kiểm tra shipper có được thấy những đơn hàng mới không
            if (request()->user()->auto_receive != 1) {
                return $this->apiError('Shipper chưa được quyền thấy những đơn hàng mới đợi lấy');
            }

            $rows = Booking::where('bookings.status', 'new')
                    ->whereIn('send_ward_id', $this->getShipperScope());

            $districtIds = $rows->pluck('send_district_id');
            $wardIds = $rows->pluck('send_ward_id');
            $data['districts'] = District::whereIn('id', $districtIds)->select('id', 'name')->get();
            $data['wards'] = Ward::whereIn('id', $wardIds)->select('id', 'districtId as district_id', 'name')->get();
            return $this->apiOk($data);
        }

        // đơn hàng đi giao chưa phân công
        if (request()->category == 'send') {
            // kiểm tra shipper có được thấy những đơn hàng đi giao không
            if (request()->user()->auto_send != 1) {
                return $this->apiError('Shipper chưa được quyền thấy những đơn hàng đợi đi giao');
            }

            $rows = Booking::join('book_deliveries as bd', 'bookings.id', '=', 'bd.book_id')
                    ->join('users', 'bd.user_id', '=', 'users.id')
                    ->leftJoin('agencies', 'bookings.agency_confirm_id', '=', 'agencies.id')
                    ->where(function($q){
                        $q->where(function($q1){
                            $q1->where('bookings.status', 'sending')
                                ->where('bd.status', 'completed')
                                ->where('bd.category', 'receive');
                        });
                        $q->orWhere(function($q2){
                            $q2->where('bookings.status', 'return')
                                ->where('bd.status', 'deny')
                                ->where('bd.category', 'return');
                        });
                    })
                    ->where('sub_status', '!=', 'deny')
                    ->where('bd.sending_active', 1)
                    ->whereIn('receive_ward_id', $this->getShipperScope());

            $districtIds = $rows->pluck('receive_district_id');
            $wardIds = $rows->pluck('receive_ward_id');
            $data['districts'] = District::whereIn('id', $districtIds)->select('id', 'name')->get();
            $data['wards'] = Ward::whereIn('id', $wardIds)->select('id', 'districtId as district_id', 'name')->get();
            return $this->apiOk($data);
        }
        return $this->apiOk('Không có dữ liệu!');
    }

    public function getAreaScopeShipping() {
        $query = DB::table('bookings')
            ->join('book_deliveries', 'bookings.id', '=', 'book_deliveries.book_id');
        $query->where('bookings.status', '!=', 'cancel')->where('book_deliveries.user_id', request()->user()->id);
        $districtIds = [];
        $wardIds = [];
        if (isset(request()->category)) {
            if (request()->category == 'receive') {
                $query->where(function ($query) {
                    $query->where('book_deliveries.category', '=', 'receive')->where('book_deliveries.status', 'processing');
                });
                $districtIds = $query->pluck('send_district_id');
                $wardIds = $query->pluck('send_ward_id');
            }
            if (request()->category == 'send') {
                $query->where(function ($query) {
                    $query->where('book_deliveries.category', '=', 'send')->where('book_deliveries.sending_active', '=', 1)->where('book_deliveries.status', 'processing');
                });
                $districtIds = $query->pluck('receive_district_id');
                $wardIds = $query->pluck('receive_ward_id');
            }
            if (request()->category == 'return') {
                $query->where(function ($query) {
                    $query->where('book_deliveries.category', '=', 'return');
                    $query->where('book_deliveries.status', 'processing');
                });
                $districtIds = $query->pluck('send_district_id');
                $wardIds = $query->pluck('send_ward_id');
            }
        }        
        $data['districts'] = District::whereIn('id', $districtIds)->select('id', 'name')->get();
        $data['wards'] = Ward::whereIn('id', $wardIds)->select('id', 'districtId as district_id', 'name')->get();
        return $this->apiOk($data);
    }

    public function getBookShipperWaitDetail(Request $req) {
        $shipperOnline = ShipperLocation::where('user_id', request()->user()->id)->where('online', 1)->first();
        if (empty($shipperOnline)) {
            return $this->apiErrorWithStatus(403, 'Kích hoạt chế độ Đang hoạt động để thấy đơn hàng!');
        }
        $limit = $req->get('limit', 10);

        $messages = [
            'sender_id.required' => 'Vui lòng chọn người gửi (khách hàng)',
            'category.required' => 'Vui lòng chọn hình thức: đi lấy/đi giao/đi trả'
        ];
        $roles = [
            'sender_id' => 'required',
            'category' => 'required'
        ];
        $validator = Validator::make(request()->all(), $roles, $messages);
        if ($validator->fails()) {
            return response([
                'msg' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        // đơn hàng mới chưa phân công
        if ($req->category == 'new') {
            // kiểm tra shipper có được thấy những đơn hàng mới không
            if ($req->user()->auto_receive != 1) {
                return $this->apiError('Shipper chưa được quyền thấy những đơn hàng mới đợi lấy');
            }
            
            $bookings = Booking::where('sender_id', request()->sender_id)
                ->where(function($q) {
                    $q->where('status', 'new');
                    $q->orWhere(function($q) {
                        $q->where('bookings.status', 'taking');
                        $q->where('bookings.sub_status', 'delay');
                        $q->whereHas('deliveries', function($q) {
                            $q->where('book_deliveries.status', '!=', 'completed');
                            $q->where('book_deliveries.category', 'receive');
                        });
                    });
                })
                ->select('id', 'uuid', 'send_full_address', 'sender_id', 'send_province_id', 'send_district_id', 'send_ward_id', 'send_name', 'send_phone', 'other_note', 'note', 'created_at', 'updated_at', 'completed_at', 'is_prioritize')
                            ->paginate($limit);

            foreach ($bookings as $item) {
                $item->sender_info = [
                    'name' => $item->send_name,
                    'phone' => $item->send_phone,
                    'address' => [
                        'province_id' => $item->send_province_id,
                        'district_id' => $item->send_district_id,
                        'ward_id' => $item->send_ward_id,
                        'home_number' => $item->send_homenumber,
                        'full_address' => $item->send_full_address
                    ]
                ];
            }
            return $this->apiOk($bookings);
        }
        
        return $this->apiOk('Không có dữ liệu!');
    }

    public function assignShipperAuto() { 
        $messages = [
            'sender_id.required' => 'Vui lòng chọn người gửi (khách hàng)',
            'category.required' => 'Vui lòng chọn hình thức: đi lấy/đi giao/đi trả'
        ];
        $roles = [
            'sender_id' => 'required',
            'category' => 'required'
        ];
        $validator = Validator::make(request()->all(), $roles, $messages);

        if ($validator->fails()) {
            return response([
                'msg' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        // đi lấy
        if (request()->category == 'new') {
            // kiểm tra shipper có được thấy những đơn hàng mới không
            if (request()->user()->auto_receive != 1) {
                return $this->apiError('Shipper chưa được quyền thấy những đơn hàng mới đợi lấy');
            }
            $bookingIds = Booking::where(function($q) {
                    $q->where('status', 'new');
                    $q->orWhere(function($q) {
                        $q->where('bookings.status', 'taking');
                        $q->where('bookings.sub_status', 'delay');
                        $q->whereHas('deliveries', function($q) {
                            $q->where('book_deliveries.status', '=', 'delay');
                            $q->where('book_deliveries.category', 'receive');
                        });
                    });
                })->where('sender_id', request()->sender_id)->pluck('id');
            //$bookingIds = Booking::where('sender_id', request()->sender_id)->where('status', 'new')->pluck('id');

            if (count($bookingIds) == 0) {
                return $this->apiError('Đơn hàng đã được phân công cho shipper khác. Hãy làm mới lại danh sách!');
            }

            DB::beginTransaction();
            try {
                foreach ($bookingIds as $bookingId) {
                    $booking = Booking::find($bookingId);
                    $booking->status = 'taking';
                    $booking->save();
                     $bookDelivery = BookDelivery::where('book_id', $booking->id)->where('category', 'receive')->first();
                    if (empty($bookDelivery)) {
                        $bookDelivery = new BookDelivery;
                    } 
                    $bookDelivery->status = 'processing';
                    $bookDelivery->user_id = request()->user()->id;
                    $bookDelivery->send_address = $booking->send_full_address;
                    $bookDelivery->receive_address = $booking->receive_full_address;
                    $bookDelivery->book_id = $booking->id;
                    $bookDelivery->category = 'receive';
                    $bookDelivery->sending_active = 1;
                    $bookDelivery->save();
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return $e;
            }

            return $this->apiOk('Chọn đơn đi lấy thành công!');
        }

        // đi giao
        if (request()->category == 'send') {
            return $this->apiOk('Chọn đơn đi giao thành công!');
        }

        // đi trả
        if (request()->category == 'return') {
            return $this->apiOk('Chọn đơn đi trả thành công!');
        }
    }

    public function updatePrioritize() { 
        $messages = [
            'category.required' => 'Vui lòng chọn hình thức: đi lấy/đi giao/đi trả',
            'is_prioritize.required' => 'Vui lòng nhập trạng thái ưu tiên',
            'id.required' => 'Vui lòng nhập ID đơn hàng'
        ];
        $roles = [
            'category' => 'required',
            'is_prioritize' => 'required',
            'id' => 'required'
        ];
        $validator = Validator::make(request()->all(), $roles, $messages);
        if ($validator->fails()) {
            return response([
                'msg' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        // đi lấy
        if (request()->category == 'new') {
            // kiểm tra shipper có được thấy những đơn hàng mới không
            if (request()->user()->auto_receive != 1) {
                return $this->apiError('Shipper chưa được quyền thấy những đơn hàng mới đợi lấy');
            }

            DB::beginTransaction();
            try {
                Booking::where('id', request()->id)->update(['is_prioritize' => request()->is_prioritize]);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return $e;
            }

            return $this->apiOk('Cập nhật đơn hàng ưu tiên thành công!');
        }

        // đi giao
        if (request()->category == 'send') {
            // kiểm tra shipper có được thấy những đơn hàng mới không
            if (request()->user()->auto_send != 1) {
                return $this->apiError('Shipper chưa được quyền thấy những đơn hàng đợi giao');
            }

            DB::beginTransaction();
            try {
                Booking::where('id', request()->id)->update(['is_prioritize' => request()->is_prioritize]);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return $e;
            }
            return $this->apiOk('Cập nhật đơn hàng ưu tiên thành công!');
        }

        // đi trả
        if (request()->category == 'return') {
            return $this->apiOk('Cập nhật đơn hàng ưu tiên thành công!');
        }

        return $this->apiError('Chọn hình thức chưa đúng. Vui lòng kiểm tra lại');
    }

    public function assignSingleShipperAuto() { 
        $messages = [
            'id.required' => 'Vui lòng nhập ID đơn hàng',
            'category.required' => 'Vui lòng chọn hình thức: đi lấy/đi giao/đi trả'
        ];
        $roles = [
            'id' => 'required',
            'category' => 'required'
        ];
        $validator = Validator::make(request()->all(), $roles, $messages);

        if ($validator->fails()) {
            return response([
                'msg' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        // đi lấy
        if (request()->category == 'new') {
            // kiểm tra shipper có được thấy những đơn hàng mới không
            if (request()->user()->auto_receive != 1) {
                return $this->apiError('Shipper chưa được quyền thấy những đơn hàng mới đợi lấy');
            }

            $booking = Booking::where(function($q) {
                    $q->where('status', 'new');
                    $q->orWhere(function($q) {
                        $q->where('bookings.status', 'taking');
                        $q->where('bookings.sub_status', 'delay');
                        $q->whereHas('deliveries', function($q) {
                            //  $q->where('book_deliveries.user_id', 0);
                            $q->where('book_deliveries.status', '=', 'delay');
                            $q->where('book_deliveries.category', 'receive');
                        });
                    });
                })->where('id', request()->id)->first();

            if (empty($booking)) {
                return $this->apiError('Đơn hàng đã được phân công cho shipper khác. Hãy làm mới lại danh sách!');
            }

            DB::beginTransaction();
            try {
                $booking->status = 'taking';
                $booking->save();
                $bookDelivery = BookDelivery::where('book_id',$booking->id)->where('category','receive')->first();
                if(empty($bookDelivery)){
                    $bookDelivery = new BookDelivery;
                }
                $bookDelivery->status = 'processing';
                $bookDelivery->user_id = request()->user()->id;
                $bookDelivery->send_address = $booking->send_full_address;
                $bookDelivery->receive_address = $booking->receive_full_address;
                $bookDelivery->book_id = $booking->id;
                $bookDelivery->category = 'receive';
                $bookDelivery->sending_active = 1;
                $bookDelivery->save();  

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return $e;
            }

            return $this->apiOk('Chọn đơn đi lấy thành công!');
        }

        // đi giao
        if (request()->category == 'send') {
            // kiểm tra shipper có được thấy những đơn hàng đợi giao không
            if (request()->user()->auto_send != 1) {
                return $this->apiError('Shipper chưa được quyền thấy những đơn hàng đợi giao');
            }

            $booking = Booking::find(request()->id);
            $bookDeliveries = BookDelivery::where('book_id', $booking->id);
            $countBookDeliveries = $bookDeliveries->count();
            if ($countBookDeliveries == 2) {
                $deliverySend = $bookDeliveries->where('category', '!=', 'recive')
                                    ->where('sending_active', 1)
                                    ->first();
                if (empty($deliverySend)) {
                    return $this->apiError('Chọn đi giao thất bại. Task không tồn tại!');
                }
                DB::beginTransaction();
                try {
                    $booking->update(['status' => 'sending']);
                    $deliverySend->update(['status' => 'processing', 'category' => 'send', 'user_id' => request()->user()->id]);                    

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    return $e;
                }
            } else {
                DB::beginTransaction();
                try {
                    $booking->update(['status' => 'sending']);
                    BookDelivery::insert([
                        'user_id' => request()->user()->id,
                        'send_address' => $booking->send_full_address,
                        'receive_address' => $booking->receive_full_address,
                        'book_id' => request()->id,
                        'category' => 'send',
                        'sending_active' => 1,
                        'updated_at' => Carbon::now(),
                    ]);
                    BookDelivery::where('book_id', request()->id)->where('category', '!=', 'send')->update(['sending_active' => 0]);

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    return $e;
                }
            }
            return $this->apiOk('Chọn đơn đi giao thành công!');
        }

        // đi trả
        if (request()->category == 'return') {
            // kiểm tra shipper có được thấy những đơn hàng đợi giao không
            if (request()->user()->auto_send != 1) {
                return $this->apiError('Shipper chưa được quyền thấy những đơn hàng đợi giao/trả');
            }

            $booking = Booking::find(request()->id);
            $bookDeliveries = BookDelivery::where('book_id', $booking->id);
            $deliverySend = $bookDeliveries->where('category', '!=', 'recive')
                                ->where('sending_active', 1)
                                ->first();
            if (empty($deliverySend)) {
                return $this->apiError('Chọn đi trả thất bại. Task không tồn tại!');
            }
            DB::beginTransaction();
            try {
                $booking->update(['status' => 'return']);
                $deliverySend->update(['status' => 'processing', 'category' => 'return', 'user_id' => request()->user()->id]);                    

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return $e;
            }
           
            return $this->apiOk('Chọn đơn đi trả thành công!');
        }
        return $this->apiError('Chọn đơn thất bại. Kiểm tra lại hình thức chọn đơn');
    }

    public function getBookShipperCount() {
        $data = [
            'reveice_wait' => 0,
            'reveice' => 0,
            'send_wait' => 0,
            'send' => 0
        ];

        $shipperOnline = ShipperLocation::where('user_id', request()->user()->id)->where('online', 1)->first();
        if (empty($shipperOnline)) {
            // return $this->apiError('Kích hoạt chế độ Đang hoạt động để thấy đơn hàng!');
            return $this->apiOk($data);
        }

        // chờ lấy
        $data['reveice_wait'] = Booking::where(function($q) {
                $q->where('status', 'new');
                $q->orWhere(function($q) {
                    $q->where('bookings.status', 'taking');
                    $q->where('bookings.sub_status', 'delay');
                    $q->whereHas('deliveries', function($q) {
                        //$q->where('book_deliveries.user_id',0);
                        $q->where('book_deliveries.status', '=', 'delay');
                        $q->where('book_deliveries.category', 'receive');
                    });
                });
            })
            ->whereIn('send_ward_id', $this->getShipperScope())
            ->count();

        // chờ giao
        $data['send_wait'] = Booking::join('book_deliveries as bd', 'bookings.id', '=', 'bd.book_id')
                    ->join('users', 'bd.user_id', '=', 'users.id')
                    ->leftJoin('agencies', 'bookings.agency_confirm_id', '=', 'agencies.id')
                    ->where(function($q){
                        $q->where(function($q1){
                            $q1->where('bookings.status', 'sending')
                                ->where('bd.status', 'completed')
                                ->where('bd.category', 'receive');
                        });
                        $q->orWhere(function($q2){
                            $q2->where('bookings.status', 'return')
                                ->where('bd.status', 'deny')
                                ->where('bd.category', 'return');
                        });
                    })
                    ->where('sub_status', '!=', 'deny')
                    ->where('bd.sending_active', 1)
                    ->whereIn('receive_ward_id', $this->getShipperScope())
                    ->count();

        // đi lấy
        $data['reveice'] = DB::table('bookings')
            ->join('book_deliveries', 'bookings.id', '=', 'book_deliveries.book_id')
            ->where('bookings.status', '!=', 'cancel')
            ->where('book_deliveries.user_id', request()->user()->id)
            ->where(function ($query) {
                $query->where('book_deliveries.category', '=', 'receive')
                    ->where('book_deliveries.status', 'processing');
            })
            ->count();
            
        // đi giao
        $data['send'] = DB::table('bookings')
            ->join('book_deliveries', 'bookings.id', '=', 'book_deliveries.book_id')
            ->where('bookings.status', '!=', 'cancel')
            ->where('book_deliveries.user_id', request()->user()->id)
            ->where(function ($query) {
                $query->where('book_deliveries.category', '=', 'send')
                    ->where('book_deliveries.sending_active', '=', 1)
                    ->where('book_deliveries.status', 'processing');
            })
            ->count();

        // kiểm tra shipper có được thấy những đơn hàng mới không
        if (request()->user()->auto_receive != 1) {
            $data['reveice_wait'] = 0;
        }
        // kiểm tra shipper có được thấy những đơn hàng mới không
        if (request()->user()->auto_receive != 1) {
            $data['send_wait'] = 0;
        }
        return $this->apiOk($data);
    }

    public function updateBook($id, Request $req)
    {
        $book = Booking::find($id);
        if ($book->status != 'new') {
            return $this->apiError('you can not update right now');
        } else {
            $validator = Validator::make($req->all(), [
                'sender.phone' => 'required|min:10',
                'sender.name' => 'required|string',
                'sender.address.province' => 'required|integer',
                'sender.address.district' => 'required|integer',
                'sender.address.ward' => 'required|integer',
                'sender.address.homenumber' => 'required',
                'sender.address.full_address' => 'required',

                'receiver.address.province' => 'required|integer',
                'receiver.address.district' => 'required|integer',
                'receiver.address.ward' => 'required|integer',
                'receiver.address.homenumber' => 'required',
                'receiver.address.full_address' => 'required',
                'receiver.name' => 'required|string',
                'receiver.phone' => 'required|string',

                'price' => 'required|between:0,99.99',
                'weight' => 'required|between:0,99.99',
                'receive_type' => 'required|integer',
                'transport_type' => 'required|integer',
                'payment_type' => 'required|integer',
            ]);
        }
        $validator->after(function ($validator) use ($req) {
            if ($req->sender['address']['province'] != null) {
                $pr = Province::where('id', $req->sender['address']['province'])->first()->active;
                if ($pr != 1) {
                    $validator->errors()->add('sender.address.province', 'Chưa áp dụng giao hàng khu vực này');
                }
            }
            if ($req->receiver['address']['province'] != null) {
                $pr = Province::where('id', $req->receiver['address']['province'])->first()->active;
                if ($pr != 1) {
                    $validator->errors()->add('receiver.address.province', 'Chưa áp dụng giao hàng khu vực này');
                }
            }
            if ($req->sender['address']['district'] != null) {
                $pr = District::where('id', $req->sender['address']['district'])->first()->allow_booking;
                if ($pr != 1) {
                    $validator->errors()->add('sender.address.district', 'Chưa áp dụng giao hàng khu vực này');
                }
            }
            if ($req->receiver['address']['district'] != null) {
                $pr = District::where('id', $req->receiver['address']['district'])->first()->allow_booking;
                if ($pr != 1) {
                    $validator->errors()->add('receiver.address.district', 'Chưa áp dụng giao hàng khu vực này');
                }
            }
        });
        if ($validator->fails()) {
            return $this->apiError($validator->errors()->first());
        }
        $receiver = User::where('phone_number', $req->receiver['phone'])->first();
        DB::beginTransaction();
        try {
            $book->sender_id = $req->user()->id;
            if (!empty($receiver)) {
                $book->receiver_id = $receiver->id;
            }
            $book->name = $req->name ? $req->name : null;
            $book->send_name = $req->sender['name'];
            $book->send_phone = $req->sender['phone'];
            $book->send_province_id = $req->sender['address']['province'];
            $book->send_district_id = $req->sender['address']['district'];
            $book->send_ward_id = $req->sender['address']['ward'];
            $book->send_homenumber = $req->sender['address']['homenumber'];
            $book->send_full_address = $req->sender['address']['full_address'];
            $book->receive_name = $req->receiver['name'];
            $book->receive_phone = $req->receiver['phone'];
            $book->receive_province_id = $req->receiver['address']['province'];
            $book->receive_district_id = $req->receiver['address']['district'];
            $book->receive_ward_id = $req->receiver['address']['ward'];
            $book->receive_homenumber = $req->receiver['address']['homenumber'];
            $book->receive_full_address = $req->receiver['address']['full_address'];
            $book->receive_type = $req->receive_type;
            $book->price = $req->price;
            $book->weight = $req->weight;
            $book->transport_type = $req->transport_type;
            $book->payment_type = $req->payment_type;
            $book->COD = $req->COD != null ? $req->COD : 0;
            $book->other_note = $req->other_note;
            $book->status = 'new';
            $book->save();
            $this->setSendOrReceiveAddress($book, 'send');
            $this->setSendOrReceiveAddress($book, 'receive');
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->apiError($e->getMessage());
        }
        return $this->apiOk('success');
    }

    public function updateBookShipper($id, Request $req)
    {
        $validator = Validator::make($req->all(), [
            'status' => 'required',
        
        ]);
        if ($validator->fails()) {
            return $this->apiError($validator->errors()->first());
        }
        if (!in_array($req->status, ['processing', 'completed', 'delay', 'return', 'cancel', 'deny'])) {
            return $this->apiError('status invalid');
        }

        $delivery = BookDelivery::where('id', $id)->first();
        if (empty($delivery)) {
            return $this->apiError('task can not found');
        }

        $booking = Booking::find($delivery->book_id);
        if (empty($booking)) {
            return $this->apiError('booking can not found');
        }
        $bookingTmp = $booking->toArray();
        $bookingTmp['book_delivery_id'] = $delivery->id;

        // không cho cập nhật bất kì cái gì khi đơn hàng đã hoàn tất
        if ($booking->status == 'completed') {
            return $this->apiError('Đơn hàng đã hoàn thành không thể cập nhật thêm!');
        }


        DB::beginTransaction();
        try {
            switch ($req->status) {
                case 'processing' :
                    if ($delivery->status == 'delay' && $delivery->delay_total > 0) {
                        $delivery->delay_total -= 1;
                    }
                    $delivery->status = $req->status;
                    if ($delivery->category == 'receive') {
                        $booking->status = 'taking';
                        $booking->sub_status = 'none';
                    } elseif ($delivery->category == 'send') {
                        $booking->status = 'sending';
                        $booking->sub_status = 'none';
                        if ($delivery->status == 'completed') {
                            if ($booking->COD > 0) {
                                $user = User::find($booking->sender_id);
                                $user->total_COD -= $booking->COD;
                                $user->save();
                            }
                        }
                    }
                    break;
                case 'delay' :
                    
                    if (in_array($booking->status, ['taking', 'sending'])) {
                        //$delivery->user_id = 0;
//                        if ($booking->status == 'taking') {
//                            // $booking->status = 'new';
//                        }
                    }
                    if ($delivery->delay_total >= 2) {
                        $delivery->status = 'cancel';
                        $delivery->delay_total += 1;
                        $booking->status = 'cancel';
                        $booking->sub_status = 'none';
                    } else {
                        $delivery->status = 'delay';
                        $delivery->delay_total += 1;
                        $booking->sub_status = 'delay';
                    }
                    break;
                case 'completed':
                    $agency_check = Shipper::where('user_id', $delivery->user_id)->first();
                    $agency_id = $agency_check != null ? $agency_check->agency_id : 1;
                    $revenue = ShipperRevenue::where('shipper_id', $delivery->user_id)->first();
                    if ($revenue == null) {
                        $revenue = new ShipperRevenue();
                    }
                    if ($delivery->category == 'return') {
                        $booking->last_agency = $agency_id;
                        if ($booking->owe == 1) {
                            if ($booking->incurred > 0) {
                                if ($req->owe == 0 || !$req->owe) {
                                    $booking->owe = 0;
                                } else {
                                    $revenue->shipper_id = $delivery->user_id;
                                    $revenue->total_price += $booking->incurred;
                                    $booking->paid += $booking->incurred;
                                }
                            }
                        } else {
                            if (isset($req->owe)) {
                                if ($req->owe == 1) {
                                    $revenue->shipper_id = $delivery->user_id;
                                    $revenue->total_price += ($booking->price + $booking->incurred);
                                    $booking->paid += ($booking->price + $booking->incurred);
                                    $booking->owe = 1;
                                }
                            }
                        }

                        // thông báo đơn hàng được trả lại
                        dispatch(new NotificationJob($bookingTmp, 'customer', ' đã được trả lại', 'push_order_change'));
                    } else {
                        if ($delivery->category == 'receive') {
                            if ($booking->payment_type == 1) {
                                if ($req->owe) {
                                    if ($req->owe == 1) {
                                        $booking->paid = $booking->price;
                                        $booking->owe = $req->owe;
                                        // $revenue->shipper_id = $delivery->user_id;
                                        $revenue->total_price += $booking->price;

                                        dispatch(new NotificationJob($bookingTmp, 'customer', ' đã được thanh toán nợ', 'push_customer_owe'));
                                    }
                                }
                            }
                            $revenue->shipper_id = $delivery->user_id;
                            $booking->status = 'sending';
                            $booking->first_agency = $agency_id;
                            $booking->current_agency = $agency_id;

                            // thông báo đơn hàng được lấy
                            dispatch(new NotificationJob($bookingTmp, 'customer', ' đã được lấy', 'push_order_change'));
                        } elseif ($delivery->category == 'send') {
                            $check = BookDelivery::where('book_id', $delivery->book_id)->where('category', 'receive')->where('status', 'processing')->first();
                            if ($check != null) {
                                $check->status = 'completed';
                                $check->save();
                            }
                            if ($booking->COD > 0 && $booking->status != 'completed') {
                                $user = User::find($booking->sender_id);
                                $user->total_COD += $booking->COD;
                                $user->save();
                            }
                            if ($booking->payment_type == 2) {
                                $booking->paid = $booking->price;
                                // $revenue->shipper_id = $delivery->user_id;
                                $revenue->total_price += $booking->price;
                                $booking->owe = 1;
                            }
                            $revenue->shipper_id = $delivery->user_id;
                            $booking->last_agency = $agency_id;
                            $revenue->total_COD += $booking->COD;
                            $booking->status = 'completed';

                            // thông báo đơn hàng được giao
                            dispatch(new NotificationJob($bookingTmp, 'customer', ' đã được giao', 'push_order_change'));
                        }
                        $booking->sub_status = 'none';
                    }
                    $booking->completed_at = Carbon::now();
                    $delivery->completed_at = Carbon::now();
                    $delivery->status = $req->status;
                    $revenue->save();
                    break;
                case 'deny' :
                    if ($delivery->category == 'send') {
                        if ($delivery->status == 'completed') {
                            if ($booking->COD > 0) {
                                $user = User::find($booking->sender_id);
                                $user->total_COD -= $booking->COD;
                                $user->save();
                            }
                        }
                        $delivery->category = 'return';
                        // $delivery->user_id = 0;
                        $booking->status = 'return';
                        $booking->sub_status = 'none';
                    } else if ($delivery->category == 'return' && $delivery->user_id != 0) {
                        $booking->sub_status = 'deny';
                    }
                    $delivery->status = 'deny';

                    // thông báo đơn hàng giao lại/trả lại
                    dispatch(new NotificationJob($bookingTmp, 'customer', ' đã được giao lại/trả lại', 'push_order_change'));
                    break;
                case 'cancel' :
                    $delivery->status = 'cancel';
                    $booking->status = 'cancel';
                    $booking->sub_status = 'none';

                    // thông báo đơn hàng hủy
                    dispatch(new NotificationJob($bookingTmp, 'customer', ' đã được hủy', 'push_order_change'));
                    break;
                default:
                    $delivery->status = $req->status;
            }
            if (isset($req->note)) {
                $delivery->note = $req->note;
                $booking->note = $req->note;
            }
            if (isset($req->image)) {
                foreach ($req->image as $item) {
                    $report = ReportImage::where('task_id', $delivery->id)->where('image', $item)->count();
                    if ($report == 0) {
                        ReportImage::insert([
                            'task_id' => $delivery->id,
                            'image' => $item
                        ]);
                    }
                }
            }
            $booking->save();
            $delivery->save();
            DB::commit();
            return $this->apiOk('success');
        } catch
        (\Exception $e) {
            DB::rollBack();
            return $this->apiError($e->getMessage());
        }
    }

    public function uploadImage(Request $req)
    {
        $file = $req->file;
        $filename = date('Ymd-His-') . $file->getFilename() . '.' . $file->extension();
        $filePath = 'img/booking/';
        $movePath = public_path($filePath);
        $file->move($movePath, $filename);
        return $this->apiOk($filePath . $filename);
    }

    public function bookDetail($id, Request $req)
    {
        // cập nhật thông báo đã đọc
        if (isset(request()->notification_id) && request()->notification_id > 0) {
            NotificationUser::where('notification_id', request()->notification_id)->where('user_id', $req->user()->id)->update(['is_readed' => 1]);
        }
        // end cập nhật thông báo đã đọc

        try {
            $query = Booking::where('id', $id)->first();
            $query->returnBookingInfo;
            $query->sender_info = [
                'name' => $query->send_name,
                'phone' => $query->send_phone,
                'address' => [
                    'province_id' => $query->send_province_id,
                    'district_id' => $query->send_district_id,
                    'ward_id' => $query->send_ward_id,
                    'home_number' => $query->send_homenumber,
                    'full_address' => $query->send_full_address
                ],
                'location' => [
                    'lat' => $query->send_lat,
                    'lng' => $query->send_lng
                ]
            ];
            $query->receiver_info = [
                'name' => $query->receive_name,
                'phone' => $query->receive_phone,
                'address' => [
                    'province_id' => $query->receive_province_id,
                    'district_id' => $query->receive_district_id,
                    'ward_id' => $query->receive_ward_id,
                    'home_number' => $query->receive_homenumber,
                    'full_address' => $query->receive_full_address
                ],
                'location' => [
                    'lat' => $query->receive_lat,
                    'lng' => $query->receive_lng
                ]
            ];
            $query->total_price = $query->payment_type == 1 ? @$query->price + @$query->incurred :
                @$query->price + @$query->incurred + @$query->COD;
            unset(
                $query->send_province_id, $query->send_district_id, $query->send_ward_id, $query->send_homenumber, $query->send_full_address,
                $query->send_name, $query->send_phone, $query->receive_name, $query->receive_phone, $query->receive_province_id,
                $query->receive_district_id, $query->receive_ward_id, $query->receive_homenumber, $query->receive_full_address,
                $query->send_lat, $query->send_lng, $query->receive_lat, $query->receive_lng,
                $query->send_address, $query->receive_address, $query->receive_homenumber, $query->receive_full_address);
            return $this->apiOk($query);
        } catch (\Exception $e) {
            return $this->apiError($e->getMessage());
        }
    }

    public function bookDetailShipper($id, Request $req)
    {
        try {
            $item = Booking::query()
                ->join('book_deliveries', 'bookings.id', '=', 'book_deliveries.book_id')
                ->where('book_deliveries.id', $id)
                ->select('bookings.*','bookings.id as book_id', 'bookings.status as booking_status', 'book_deliveries.id', 'book_deliveries.category', 'book_deliveries.category', 'book_deliveries.status')
                ->with('transactionTypeService.service')
                
                ->first();

            // cập nhật thông báo đã đọc
            if (isset(request()->notification_id) && request()->notification_id > 0) {
                NotificationUser::where('notification_id', request()->notification_id)->where('user_id', $req->user()->id)->update(['is_readed' => 1]);
            }
           
            // end cập nhật thông báo đã đọc
            if(empty($item)){
                return $this->apiError('Không tìm thấy task này');
            }
//            $services = $item->transactionTypeService->pluck('service.name');
//            unset($item->transactionTypeService);
//            $item->transaction_type_service = $services;

            $item->sender_info = [
                'name' => $item->send_name,
                'phone' => $item->send_phone,
                'address' => [
                    'province_id' => $item->send_province_id,
                    'district_id' => $item->send_district_id,
                    'ward_id' => $item->send_ward_id,
                    'home_number' => $item->send_homenumber,
                    'full_address' => $item->send_full_address
                ],
                'location' => [
                    'lat' => $item->send_lat,
                    'lng' => $item->send_lng
                ]
            ];
            $item->receiver_info = [
                'name' => $item->receive_name,
                'phone' => $item->receive_phone,
                'address' => [
                    'province_id' => $item->receive_province_id,
                    'district_id' => $item->receive_district_id,
                    'ward_id' => $item->receive_ward_id,
                    'home_number' => $item->receive_homenumber,
                    'full_address' => $item->receive_full_address
                ],
                'location' => [
                    'lat' => $item->receive_lat,
                    'lng' => $item->receive_lng
                ]
            ];
            $item->allow_owe = 1;
            $item->total_price = @$item->price;
            if ($item->category == 'send' && $item->status == 'processing') {
                if($item->payment_type == 1){
                    unset($item->allow_owe);
                }
                else if ($item->payment_type == 2) {
                    $item->allow_owe = 0;
                }
                $item->total_price = @$item->payment_type == 1 ? @$item->COD + @$item->incurred :
                @$item->price + @$item->incurred + @$item->COD;
            }
            if ($item->category == 'return' && $item->status == 'processing') {
                $item->allow_owe = 1;
                $item->total_price = @$item->price + @$item->incurred;
            }
            if ($item->category == 'receive' && $item->status == 'processing') {
                if($item->payment_type == 2){
                    unset($item->allow_owe);
                }
                $item->total_price = @$item->payment_type == 1 ? @$item->price + @$item->incurred :
                @$item->incurred;
            }

            if ($item->status == 'completed') {
                if ($item->category == 'send') {
                    if($item->payment_type == 1){
                        unset($item->allow_owe);
                    }
                    else if ($item->payment_type == 2) {
                        $item->allow_owe = 0;
                    }
                    $item->total_price = @$item->payment_type == 1 ? @$item->COD + @$item->incurred :
                    @$item->price + @$item->incurred + @$item->COD;
                }
                if ($item->category == 'return') {
                    $item->allow_owe = 1;
                    $item->total_price = @$item->price + @$item->incurred;
                }
                if ($item->category == 'receive') {
                    if($item->payment_type == 2){
                        unset($item->allow_owe);
                    }
                    $item->total_price = @$item->payment_type == 1 ? @$item->price + @$item->incurred :
                    @$item->incurred;
                }
            }

            // $item->total_price = @$item->payment_type == 1 ? @$item->COD + @$item->incurred :
            //     @$item->price + @$item->incurred + @$item->COD;
            unset(
                $item->send_province_id, $item->send_district_id, $item->send_ward_id, $item->send_homenumber, $item->send_full_address,
                $item->send_name, $item->send_phone, $item->receive_name, $item->receive_phone, $item->receive_province_id,
                $item->receive_district_id, $item->receive_ward_id, $item->receive_homenumber, $item->receive_full_address,
                $item->send_lat, $item->send_lng, $item->receive_lat, $item->receive_lng, $item->booking,
                $item->send_address, $item->receive_address, $item->receive_homenumber, $item->receive_full_address);
            return $this->apiOk($item);
        } catch (\Exception $e) {
           
            return $this->apiError($e->getMessage());
        }
    }

    public function searchBook($id, Request $req)
    {
        $limit = $req->get('limit', 10);
        try {
            $rows = DB::table('bookings')->where('uuid', $id)->orWhere('send_phone', $id)->orWhere('receive_phone', $id)->orWhere('name', $id)->orderBy('id', 'desc')->paginate($limit);
            foreach ($rows->items() as $query) {
                $query->sender_info = [
                    'name' => $query->send_name,
                    'phone' => $query->send_phone,
                    'address' => [
                        'province_id' => $query->send_province_id,
                        'district_id' => $query->send_district_id,
                        'ward_id' => $query->send_ward_id,
                        'home_number' => $query->send_homenumber,
                        'full_address' => $query->send_full_address
                    ],
                    'location' => [
                        'lat' => $query->send_lat,
                        'lng' => $query->send_lng
                    ]
                ];
                $query->receiver_info = [
                    'name' => $query->receive_name,
                    'phone' => $query->receive_phone,
                    'address' => [
                        'province_id' => $query->receive_province_id,
                        'district_id' => $query->receive_district_id,
                        'ward_id' => $query->receive_ward_id,
                        'home_number' => $query->receive_homenumber,
                        'full_address' => $query->receive_full_address
                    ],
                    'location' => [
                        'lat' => $query->receive_lat,
                        'lng' => $query->receive_lng
                    ]
                ];
                unset(
                    $query->send_province_id, $query->send_district_id, $query->send_ward_id, $query->send_homenumber, $query->send_full_address,
                    $query->send_name, $query->send_phone, $query->receive_name, $query->receive_phone, $query->receive_province_id,
                    $query->receive_district_id, $query->receive_ward_id, $query->receive_homenumber, $query->receive_full_address,
                    $query->send_lat, $query->send_lng, $query->receive_lat, $query->receive_lng,
                    $query->send_address, $query->receive_address, $query->receive_homenumber, $query->receive_full_address);
            }
            return $this->apiOk($rows);
        } catch (\Exception $e) {
            return $this->apiError($e->getMessage());
        }
    }

    public function cancelBook($id, Request $req)
    {
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
        return $this->apiOk('success');
    }

    public function getCOD(Request $req)
    {
        // $total = User::find($req->user()->id);
        $total = Booking::where('sender_id', $req->user()->id)
                    ->where('COD_status', 'pending')
                    ->where('COD', '>', '0')
                    ->where('status', 'completed')
                    ->sum('COD');
        $received = DB::table('bookings')->where('sender_id', $req->user()->id)->where('COD', '>', 0)->where('COD_status', 'finish')->where('status', 'completed')->sum('COD');
        $data = [
            "total" => $total, //$total->total_COD
            "received" => $received
        ];
        return $this->apiOk($data);
    }

    public function getCODDetails(Request $req)
    {
        $limit = $req->get('limit', 10);
        $rows = DB::table('bookings')->where('sender_id', $req->user()->id)->where('COD', '>', 0)->where('status', 'completed');
        if (isset($req->status) && $req->status != null) {
            $rows = $rows->where('COD_status', $req->status);
        }
        $rows = $rows->paginate($limit);
        foreach ($rows->items() as $query) {
            $query->sender_info = [
                'name' => $query->send_name,
                'phone' => $query->send_phone,
                'address' => [
                    'province_id' => $query->send_province_id,
                    'district_id' => $query->send_district_id,
                    'ward_id' => $query->send_ward_id,
                    'home_number' => $query->send_homenumber,
                    'full_address' => $query->send_full_address
                ],
                'location' => [
                    'lat' => $query->receive_lat,
                    'lng' => $query->receive_lng
                ]
            ];
            $query->receiver_info = [
                'name' => $query->receive_name,
                'phone' => $query->receive_phone,
                'address' => [
                    'province_id' => $query->receive_province_id,
                    'district_id' => $query->receive_district_id,
                    'ward_id' => $query->receive_ward_id,
                    'home_number' => $query->receive_homenumber,
                    'full_address' => $query->receive_full_address
                ],
                'location' => [
                    'lat' => $query->receive_lat,
                    'lng' => $query->receive_lng
                ]
            ];
            unset($query->send_province_id, $query->send_district_id, $query->send_ward_id, $query->send_homenumber, $query->send_full_address, $query->send_lat, $query->send_lng,
                $query->receive_province_id, $query->receive_district_id, $query->receive_ward_id, $query->receive_homenumber, $query->receive_full_address, $query->receive_lat, $query->receive_lng,
                $query->send_name, $query->send_phone, $query->receive_name, $query->receive_phone);
        }
        return $this->apiOk($rows);
    }

    public function pricingBook(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'sender.address.province' => 'required|integer',
            'sender.address.district' => 'required|integer',
            'sender.address.ward' => 'required|integer',
            'sender.address.homenumber' => 'required',
            'sender.location.lat' => 'required',
            'sender.location.lng' => 'required',

            'receiver.address.province' => 'required|integer',
            'receiver.address.district' => 'required|integer',
            'receiver.address.ward' => 'required|integer',
            'receiver.address.homenumber' => 'required',
            'receiver.location.lat' => 'required',
            'receiver.location.lng' => 'required',

            'receive_type' => 'required|integer',
            'transport_type' => 'required|integer',
            'weight' => 'required|between:0,99.99',
        ]);

        if ($validator->fails()) {
            return $this->apiError($validator->errors()->first());
        }
        $result = Booking::Pricing($req,true);
        return  $this->apiRes(true, $result['msg'], $result['total'], null, null, 1);
    }

    public function checkProvince(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'send.province' => 'required|integer',
            'send.district' => 'required|integer',
            'receive.province' => 'required|integer',
            'receive.district' => 'required|integer',
        ]);
        $result = false;
        if ($validator->fails()) {
            return $this->apiError($validator->errors()->first());
        }
        if ($req->send['province'] == $req->receive['province']) {
            $check = Province::find($req->send['province']);
            if ($check->province_type == 1) {
                $result = true;
            } else {
                $district_send = District::find($req->send['district'])->district_type;
                $district_receive = District::find($req->receive['district'])->district_type;
                if ($district_send != 5 && $district_receive != 5) {
                    $result = true;
                }
            }
        }
        return $this->apiOk($result);
    }

    public function deleteBooking($id)
    {
        DB::beginTransaction();
        try {
            $booking = Booking::find($id);
            if ($booking != null) {
                if ($booking->status == 'new' || $booking->status == 'taking' || $booking->status == 'sending' || $booking->status == 'return') {
                    return $this->apiError('không thể xóa đơn hàng này');
                } else if ($booking->status == 'completed') {
                    if ($booking->COD > 0) {
                        if ($booking->COD_status == 'pending') {
                            return $this->apiError('không thể xóa đơn hàng này');
                        }
                    }
                }
                $booking->delete();
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
        }
        return $this->apiOk('success');
    }


    public function countBook(Request $req) {
        $data['all'] = 0;
        $data['received'] = 0;
        $data['sented'] = 0;
        $data['return'] = 0;
        $data['cancel'] = 0;
        $data['re-send'] = 0;
        $query = Booking::where('sender_id', $req->user()->id)
                    ->select('id', 'sender_id', 'status')
                    ->get();

        $data['all'] = count($query);
        if (!empty($query) && count($query) > 0) {
            foreach ($query as $item) {
                if ($item->status == 'sending') {
                    $data['received'] ++;

                } elseif ($item->status == 'completed') {
                    $data['sented']++;
                } elseif ($item->status == 'return') {
                    
                    if(!empty($item->returnDeliveries)){
                        $data['return']++;
                    }
                    if (!empty($item->requestDeliveries)) {
                        $data['re-send'] ++;
                    }
                } elseif ($item->status == 'cancel') {
                    $data['cancel']++;
                }
            }
        }
        return $this->apiOk($data);
    }

    private function addNotificationBook($booking, $title, $userIds = []) {
        $notification = new Notification();
        $notification->title = 'Đơn hàng [' . $booking['uuid'] . ']' . $title;
        $notification->booking_id = $booking['id'];
        $notification->type = 'book';
        $notification->save();

        if (count($userIds) > 0) {
            foreach ($userIds as $userId) {
                $notificationUser = new NotificationUser();
                $notificationUser->notification_id = $notification->id;
                $notificationUser->user_id = $userId;
                $notificationUser->is_readed = 0;
                $notificationUser->save();
            }
        }

        // $message = array(
        //     'notification_id' => intval($notification->id),
        //     'title' => $notification->title,
        //     'type' => $notification->type,
        //     'booking_id' => intval($booking['id']),
        //     'user_id' => intval($notificationUser->user_id),
        //     'body' => $notification->content,
        //     'booking_status' => $booking['status'],
        //     'amount' => intval($booking['price']),
        //     'badge' => intval($this->getUnreadCount($notificationUser->user_id))
        // );
        // if ($toObject == 'shipper') {
        //     $message['booking_id'] = isset($booking['book_delivery_id']) ? intval($booking['book_delivery_id']) : intval($booking['id']);
        // }

        // if (count($devices) > 0 && !empty($devices)) {
        //     foreach ($devices as $device) {
        //         // $this->send($device->device_token, $notification->title, $notification->title, $message, $device->device_type, $collapseKey);
        //         dispatch(new PushNotificationBook($device->device_token, $notification->title, $notification->title, $message, $device->device_type, $collapseKey));
        //     }
        // }
    

    }

    //----------------RAYMOND API-----------
    public function create(Request $req) {
        try {
            $user=Auth::user();
            $booking=Booking::create($req);
            if($booking==200)
            {
                return response()->json(['msg' => 'Bạn đã tạo đơn hàng thành công', 'code' => 200]);
            }else{
                return response()->json(['msg' => 'Vui lòng kiểm tra lại', 'code' => 200]);
            }
        } catch (\Exception $e) {
            return $e;
        }
    }
}


