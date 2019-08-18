<?php

namespace App\Http\Controllers\API\Shipper;

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

    //cap nhat ghi chu khach hang
    public function updateNote($id, Request $req) {
        $bookDelivery = BookDelivery::where(['book_id' => $id, 'user_id' => $req->user()->id])->whereIn('category', ['receive', 'receive-and-send'])->first();

        if (empty($bookDelivery)) {
            return $this->apiErrorWithCode('Không tìm thấy đơn hàng này', 404);
        }
        $validator = Validator::make($req->all(), [
                'other_note' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->apiError($validator->errors()->first());
        }
        $book = $bookDelivery->booking;
        if (empty($req->other_note)) {
            $book->other_note = $req->other_note;
        } else {
            $book->other_note .= '.' . $req->other_note;
        }
        DB::beginTransaction();
        try {
            if ($book->save()) {
                return $this->apiOk('success');
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->apiError($e->getMessage());
        }
        return $this->apiOk('success');
    }

    public function getListBook(Request $req) {
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
                $query->where('bookings.sub_status', '!=', 'deny')->where('bookings.status', '!=', 'completed');
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
                    $query->where('book_deliveries.category', '=', 'send')
                        ->whereHas('deliveries',function($q) {
                            $q->where('book_deliveries.category', 'receive');
                            $q->where('book_deliveries.status', 'completed');
                        })
                        ->where('book_deliveries.sending_active', '=', 1)->where('book_deliveries.status', 'processing');
                });

                $query->where('bookings.status', '!=', 'completed');
                $query->whereNotIn('bookings.sub_status', ['deny']);
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
                
                $query->where('bookings.sub_status','!=','deny');
                $query->where(function ($query) {
                    $query->where(function($q) {
                        $q->where('bookings.status', 'return');
                        $q->where('book_deliveries.category', '=', 'return');
                        $q->where('book_deliveries.status', 'processing');
                    });
                    $query->orWhere(function($q) {
                        // don hang giao / tra lai
                        $q->where('book_deliveries.category', '=', 're-send');
                        $q->where('book_deliveries.status', 'deny');
                    });
                });
                $query->where('bookings.status', '!=', 'completed');
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

    function orderDetail($item) {
        $item->transactionService;
        $item->returnBookingInfo;
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
        $item->total_price = $item->payment_type == 1 ? @$item->price + @$item->incurred :
            @$item->incurred;
        unset(
            $item->send_province_id, $item->send_district_id, $item->send_ward_id, $item->send_homenumber, $item->send_full_address, $item->send_name, $item->send_phone, $item->receive_name, $item->receive_phone, $item->receive_province_id, $item->receive_district_id, $item->receive_ward_id, $item->receive_homenumber, $item->receive_full_address, $item->send_lat, $item->send_lng, $item->receive_lat, $item->receive_lng, $item->send_address, $item->receive_address, $item->receive_homenumber, $item->receive_full_address);
        return $this->apiOk($item);
    }

    public function updateWeightPrice($id, Request $req) {
        $validator = Validator::make($req->all(), [
                'weight' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->apiError($validator->errors()->first());
        }

        $delivery = BookDelivery::where('id', $id)->first();
        if (empty($delivery)) {
            return $this->apiError('Không tìm thấy task này');
        }
        $booking = Booking::where('id', $delivery->book_id)->select('*', 'id as book_id')->with('transactionTypeService.service')->first();

        if (empty($booking)) {
            return $this->apiError('Không tìm thấy đơn hàng này');
        }

        if ($booking->status != 'taking') {
            return $this->apiError('Trạng thái này không được hỗ trợ =');
        }
        $booking->weight = $req->weight;
        $price = $booking->prePricing();

        $booking->price = $price;
        try {
            $booking->save();
            DB::commit();
            return $this->orderDetail($booking);
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->apiError($e->getMessage());
        }
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
                    $q->where('bookings.status', 'new');
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
            $rows->with('transactionTypeService.service');
            $rows = $rows->select('status', 'id', 'uuid', 'sender_id', 'created_at', 'send_name', 'send_phone', 'send_full_address', 'send_province_id', 'send_district_id', 'send_ward_id', 'send_homenumber', 'is_prioritize', 'name', DB::raw('count(sender_id) as total_book'))
                ->orderBy('is_prioritize', 'DESC')
                ->orderBy('bookings.created_at', 'desc')
                ->paginate($limit);

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
                ->leftJoin('users', 'bd.user_id', '=', 'users.id')
                ->leftJoin('agencies', 'bookings.agency_confirm_id', '=', 'agencies.id')
                ->where(function($q) {
                    $q->where(function($q1) {
                        
                        $q1->where('bookings.status', 'sending')
                        ->where('bd.status', 'completed')
                        ->where('bd.category', 'receive');
                    });
                    $q->orWhere(function($q2) {
                        
                        $q2->where('bookings.status', 'return')
                        ->whereIn('bd.status', ['delay'])
                        ->where('bd.category', 'return');
                    });
                    $q->orWhere(function($q) {
                        $q->where('bookings.status', 're-send');
                        $q->where('bd.user_id', 0);
//                        $q->where('bd.status', '=', 'deny');
//                        $q->where('bd.category', 're-send');
                    });
                })
                ->where('bookings.status', '!=', 'cancel')
                ->where('bookings.status', '!=', 'completed')
                ->where('bookings.sub_status', '!=', 'deny')
                ->where('bd.sending_active', 1)
                ->whereIn('receive_ward_id', $this->getShipperScope());
            if (isset($req->district_id) && !empty($req->district_id)) {
                $rows = $rows->where('receive_district_id', $req->district_id);
            }
            if (isset($req->ward_id) && !empty($req->ward_id)) {
                $rows = $rows->where('receive_ward_id', $req->ward_id);
            }
            if (isset($req->keyword) && !empty($req->keyword)) {
                $rows = $rows->where(function($q) use($req) {
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
                $receviceAddress = str_replace($item->receive_homenumber . ', ', '', $item->receive_full_address);
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
                $q->where('bookings.status', 'new');
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
            ->leftJoin('users', 'bd.user_id', '=', 'users.id')
            ->leftJoin('agencies', 'bookings.agency_confirm_id', '=', 'agencies.id')
            ->where(function($q) {
                $q->where(function($q1) {
                    $q1->where('bookings.status', 'sending')
                    ->where('bd.status', 'completed')
                    ->where('bd.category', 'receive');
                });
                $q->orWhere(function($q2) {
                    $q2->where('bookings.status', 're-send')
                    ->whereIn('bd.status', ['delay'])
                    ->where('bd.category', 'return');
                });
                $q->orWhere(function($q3) {
                    $q3->where('bookings.status', 're-send');
                    $q3->where('book_deliveries.user_id', 0);
//                    $q3->where('book_deliveries.status', '=', 'deny');
//                    $q3->where('book_deliveries.category', 're-send');

                });
            })
            ->leftJoin('book_deliveries', 'bookings.id', '=', 'book_deliveries.book_id')
            ->where('bookings.sub_status', '!=', 'deny')
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

    private function getShipperScope() {
        $wards = ManagementWardScope::where('shipper_id', request()->user()->id)->pluck('ward_id');
        return $wards;
    }

    public function updateBookShipper($id, Request $req) {
        $validator = Validator::make($req->all(), [
                'status' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->apiError($validator->errors()->first());
        }
        if (!in_array($req->status, ['processing', 'completed', 'delay', 'return', 'cancel', 'deny','re-send'])) {
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
                    if ($delivery->category == 're-send') {
                        $delivery->category == 'return';
                        $booking->sub_status = 'completed';
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
                            $booking->sub_status = 'none';
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
                case 're-send' :
                    
                    if ($delivery->category == 'send') {
                        if ($delivery->status == 'completed') {
                            if ($booking->COD > 0) {
                                $user = User::find($booking->sender_id);
                                $user->total_COD -= $booking->COD;
                                $user->save();
                            }
                        }
                        $delivery->category = 're-send';
                        $delivery->status =
                        // $delivery->user_id = 0;
                        $booking->status = 're-send';
                        $booking->sub_status = 'none';
                    } else if ($delivery->category == 'return' && $delivery->user_id != 0) {
                        $booking->sub_status = 'deny';
                    }
                    $delivery->status = 'deny';

                    // thông báo đơn hàng giao lại/trả lại
                    dispatch(new NotificationJob($bookingTmp, 'customer', ' đã được giao lại', 'push_order_change'));
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
            
            $query = Booking::where('sender_id', request()->sender_id)
                ->where(function($q) {
                    $q->where('bookings.status', 'new');
                    $q->orWhere(function($q) {
                        $q->where('bookings.status', 'taking');
                        $q->where('bookings.sub_status', 'delay');
                        $q->whereHas('deliveries', function($q) {
                            $q->where('book_deliveries.status', '!=', 'completed');
                            $q->where('book_deliveries.category', 'receive');
                        });
                    });
                    $q->orWhere(function($q) {
                        $q->where('bookings.status', 're-send');
                        $q->where('book_deliveries.user_id', 0);
                        $q->where('bookings.sub_status', "!=", 'deny');
//                        $q->where('book_deliveries.status', '=', 'deny');
//                        $q->where('book_deliveries.category', 're-send');
                    });
                })
                ->leftJoin('book_deliveries', 'bookings.id', '=', 'book_deliveries.book_id')
                ->with('transactionTypeService.service');
                $query ->select('bookings.id','bookings.id as book_id', 'bookings.uuid', 'bookings.send_full_address',
                    'bookings.sender_id', 'bookings.send_province_id',
                    'bookings.send_district_id', 'bookings.send_ward_id',
                    'bookings.send_name', 'bookings.send_phone', 'bookings.other_note',
                    'bookings.note', 'bookings.created_at', 'bookings.updated_at',
                    'bookings.completed_at', 'bookings.is_prioritize');
    
                $bookings = $query->paginate($limit);

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
                    
                    $bookDelivery->created_at = Carbon::now();
                    $bookDelivery->updated_at = Carbon::now();
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
                        'created_at' => Carbon::now(),
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


    
}
