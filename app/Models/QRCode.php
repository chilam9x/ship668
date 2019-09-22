<?php

namespace App\Models;
use App\Models\BookDelivery;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;
use App\Helpers\NotificationHelper;
use App\Jobs\NotificationJob;
use App\Models\Notification;
use App\Models\NotificationUser;
use Carbon\Carbon;
use Auth;

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
        dd($qrcode);
        $res = DB::table('qrcode')->where('name', $qrcode)->first();
        return $res;
    }
    //check qrcode đã được sử dụng chưa
    public static function findQRCode_OrderNew($qrcode)
    {
        $res = DB::table('qrcode')->where('name', $qrcode)->where('is_used', 1)->first();
        return $res;
    }
    //check qrcode có phải của đơn hàng mới?
    public static function checkQRCode_OrderNew($qrcode)
    {
        $res = DB::table('booking as b')
        ->join('qrcode as q','q.id','=','b.qrcode_id')
        ->where('q.name', $qrcode)->where('q.is_used', 1)->where('b.status','new')->first();
        return $res;
    }
    //phân công lấy đơn hàng
    public static function takeOrder($qrcode)
    { 
        $shipper_id = Auth::user()->id;

        $booking=DB::table('bookings')->where('uuid',$qrcode)->first();

        $booking = Booking::find($id);
        $check = BookDelivery::where('book_id', $id)->first();
        if ($check == null) {
            DB::beginTransaction();
            try {
                    BookDelivery::insert([
                        'user_id' => $shipper_id,
                        'send_address' => $booking->send_full_address,
                        'receive_address' => $booking->receive_full_address,
                        'book_id' => $id,
                        'category' => 'receive',
                        'sending_active' => 1,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                $booking->status = 'taking';
                $booking->save();
                DB::commit();

                $bookingDelivery = BookDelivery::where('book_id', $id)->where('user_id', $shipper_id)->where('sending_active', 1)->first();
                //gửi thông báo tới shipper khi được phân công
                $bookingTmp = $booking->toArray();
                $bookingTmp['shipper_id'] = $shipper_id;
                $bookingTmp['book_delivery_id'] = $bookingDelivery->id;
                // echo '<pre>';print_r($bookingTmp);die;
                // $notificationHelper = new NotificationHelper();
                // $notificationHelper->notificationBooking($bookingTmp, 'shipper', ' vừa được phân công cho bạn', 'push_order_assign');
             //   dispatch(new NotificationJob($bookingTmp, 'shipper', ' vừa được phân công cho bạn', 'push_order_assign'));
                
            } catch (\Exception $e) {
                DB::rollBack();
                return $e;
            }
    }
}
