<?php

namespace App\Models;

use App\Jobs\NotificationJob;
use App\Models\BookDelivery;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class QRCode
{
    public static function getList()
    {
        $res = DB::table('qrcode as q')->leftJoin('bookings as b', 'b.qrcode_id', '=', 'q.id')->select('q.*', 'b.id as booking_id')->orderBy('id', 'desc')->paginate(20);
        return $res;
    }
    public static function find($data)
    {
        $res = DB::table('qrcode as q')
            ->leftJoin('bookings as b', 'b.qrcode_id', '=', 'q.id')
            ->select('q.*', 'b.id as booking_id')
            ->where('q.name', 'LIKE', '%' . $data . '%')
            ->orderBy('q.id', 'desc')->paginate(20);
        return $res;
    }
    public static function getQRCodeListUnused()
    {
        $count = DB::table(config('constants.QRCODE_TABLE'))->where('status', 0)->get();
        return $count;
    }
    public static function countQrcodeUsed()
    {
        $count = DB::table('qrcode')->where('is_used', 1)->count();
        return $count;
    }
    public static function countQrcodeUnused()
    {
        $count = DB::table('qrcode')->where('is_used', 0)->count();
        return $count;
    }
    public static function postCreate($data)
    {
        try {
            $array = array();
            for ($i = 1; $i <= $data; $i++) {
                $name = str_random(10);
                $checkcode = DB::table('qrcode')->where('name', $name)->count();
                if ($checkcode < 1) {
                    DB::table('qrcode')->insert(
                        [
                            'name' => $name,
                            'created_at' => date('Y-m-d h:i:s'),
                            'is_used' => 0,
                        ]
                    );
                    $array[] = $name;
                }
            }
            return $array;
        } catch (\Exception $e) {
            return $e;
        }
    }
    public static function changeStatus($id) //thay doi status chua su dung ->dang su dung
    {
        try {
            DB::table(config('constants.QRCODE_TABLE'))
                ->where('id', $id)
                ->update([
                    'status' => 1,
                ]);
            return 200;
        } catch (\Exception $e) {
            return $e;
        }
    }

    //---------API------
    //kiểm tra qr code có tồn tại không
    public static function findQRCode($qrcode)
    {
        $res = DB::table('qrcode')->where('name', $qrcode)->first();
        return $res;
    }
    //1.check qrcode đã được sử dụng chưa
    public static function findQRCode_OrderNew($qrcode)
    {
        $res = DB::table('qrcode')->where('name', $qrcode)->where('is_used', 0)->first();
        return $res;
    }
    //2.check qrcode có phải của đơn hàng mới?
    public static function checkQRCode_OrderNew($qrcode)
    {
        $res = DB::table('bookings as b')
            ->join('qrcode as q', 'q.id', '=', 'b.qrcode_id')
            ->where('q.name', $qrcode)->where('q.is_used', 1)->where('b.status', 'new')->first();
        return $res;
    }
    //3.check qrcode có phải của đơn hàng đã lấy?
    public static function checkQRCode_OrderTaking($qrcode)
    {
        $res = DB::table('bookings as b')
            ->join('qrcode as q', 'q.id', '=', 'b.qrcode_id')
            ->where('q.name', $qrcode)->where('q.is_used', 1)->where('b.status', 'taking')->first();
        return $res;
    }
    //5.check qrcode có phải của đơn hàng lấy đi giao?
    public static function checkQRCode_OrderSending($qrcode)
    {
        $res = DB::table('bookings as b')
            ->join('qrcode as q', 'q.id', '=', 'b.qrcode_id')
            ->where('q.name', $qrcode)->where('q.is_used', 1)->where('b.status', 'sending')->first();
        return $res;
    }
    //4.check qrcode đơn hàng mới đã nhập kho?
    public static function checkQRCode_OrderTaking_intoWarehouse($qrcode)
    {
        $res = DB::table('bookings as b')
            ->join('qrcode as q', 'q.id', '=', 'b.qrcode_id')
            ->where('q.name', $qrcode)->where('q.is_used', 1)->where('b.status', 'taking')->where('b.warehouse_into_id', '!=', null)->first();
        return $res;
    }
    //2.1phân công lấy đơn hàng
    public static function receiveOrder($qrcode)
    {
        $shipper_id = Auth::user()->id;
        $booking = DB::table('bookings')->where('uuid', $qrcode)->first();
        $booking = Booking::find($booking->id);
        $check = BookDelivery::where('book_id', $booking->id)->first();
        if ($check == null) {
            DB::beginTransaction();
            try {
                BookDelivery::insert([
                    'user_id' => $shipper_id,
                    'send_address' => $booking->send_full_address,
                    'receive_address' => $booking->receive_full_address,
                    'book_id' => $booking->id,
                    'category' => 'receive',
                    'sending_active' => 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                $booking->status = 'taking';
                $booking->save();
                DB::commit();
                $bookingDelivery = BookDelivery::where('book_id', $booking->id)->where('user_id', $shipper_id)->where('sending_active', 1)->first();
                //gửi thông báo tới shipper khi được phân công
                // dd($booking);
                $bookingTmp = $booking->toArray();
                $bookingTmp['shipper_id'] = $shipper_id;
                $bookingTmp['book_delivery_id'] = $bookingDelivery->id;

                // echo '<pre>';print_r($bookingTmp);die;
                // $notificationHelper = new NotificationHelper();
                // $notificationHelper->notificationBooking($bookingTmp, 'shipper', ' vừa được phân công cho bạn', 'push_order_assign');
                dispatch(new NotificationJob($bookingTmp, 'shipper', ' vừa được phân công cho bạn', 'push_order_assign'));
                return 200;
            } catch (\Exception $e) {
                DB::rollBack();
                return $e;
            }
        }
    }
    //4.1 phân công giao đơn hàng
    public static function senderOrder($qrcode)
    {
        $shipper_id = Auth::user()->id;
        $booking = DB::table('bookings')->where('uuid', $qrcode)->first();
        $booking = Booking::find($booking->id);
        $check = BookDelivery::where('book_id', $booking->id)->where('category', 'send')->first();
        if ($check == null) {
            DB::beginTransaction();
            try {
                $booking->update(['status' => 'sending']);
                BookDelivery::insert([
                    'user_id' => $shipper_id,
                    'send_address' => $booking->send_full_address,
                    'receive_address' => $booking->receive_full_address,
                    'book_id' => $booking->id,
                    'category' => 'send',
                    'sending_active' => 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                BookDelivery::where('book_id', $booking->id)->where('category', '!=', 'send')->update(['sending_active' => 0]);
                DB::commit();
                return 200;
            } catch (\Exception $e) {
                DB::rollBack();
                return $e;
            }
        }
    }
    //3.1 nhập đơn mới vào kho
    public static function intoWarehouse($data)
    {
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $warehouse_id = Auth::user()->id;
        DB::table('bookings')->where('uuid', $data->qrcode)->update([
            'warehouse_into_id' => $warehouse_id,
            'into_at' => date('Y-m-d H:i:s'),
        ]);
        return 200;
    }
    //5.1 nhập đơn huy vào kho
    public static function failWarehouse($data)
    {
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $warehouse_id = Auth::user()->id;
        DB::table('bookings')->where('uuid', $data->qrcode)->update([
            'warehouse_fail_id' => $warehouse_id,
            'fail_at' => date('Y-m-d H:i:s'),
        ]);
        return 200;
    }

}
