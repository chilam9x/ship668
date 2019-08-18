<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use App\Models\Notification;
use App\Models\NotificationUser;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use DB;
use App\Models\Booking;
use App\Models\Wallet;
use App\Models\BookingWallet;
use App\Models\Collaborator;
use App\Models\Setting;
use Auth;

class WalletController extends ApiController
{
    public function getTotalPrice() {
        $booking = $this->totalPrice()['booking'];
        $listBookings = $this->totalPrice()['listBookings'];
        $totalPrice = round(($booking->sum('price') + $booking->sum('incurred')) - $booking->sum('paid'));
        return $this->apiOk([
        	'bookings' => $listBookings,
        	'count' => $totalPrice
        ]);
    }

    public function getTotalCOD() {
    	$booking = $this->totalCOD()['booking'];
        $listBookings = $this->totalCOD()['listBookings'];
        $totalCOD = $booking->sum('COD');
        return $this->apiOk([
        	'bookings' => $listBookings,
        	'count' => $totalCOD
        ]);
    }

    public function getWallet() {
    	$bookingTotalPrice = $this->totalPrice()['booking'];
    	$totalPrice = round(($bookingTotalPrice->sum('price') + $bookingTotalPrice->sum('incurred')) - $bookingTotalPrice->sum('paid'));    	
    	$bookingTotalCOD = $this->totalCOD()['booking'];
        $totalCOD = $bookingTotalCOD->sum('COD');
        $totalWallet = $totalCOD - $totalPrice;
        $wallets = Wallet::where('customer_id', request()->user()->id)
        					->orderBy('created_at', 'DES')
        					->paginate(10);
        return $this->apiOk([
        	'wallets' => $wallets,
        	'count' => $totalWallet
        ]);
    }

    public function getListBook($walletId) {
    	$bookIds = BookingWallet::where('wallet_id', $walletId)->get()->pluck('booking_id')->toArray();
    	$db = Booking::whereIn('id', $bookIds);
    	$totalBook = $db->count();
    	$bookings = $db->select('bookings.id', 'bookings.uuid', 'bookings.name', 'bookings.send_full_address', 'bookings.receive_full_address', 'bookings.created_at', 'bookings.price', 'bookings.incurred', 'bookings.COD')
                        ->orderBy('created_at', 'DESC')->paginate(10);
    	return $this->apiOk([
    		'bookings' => $bookings, 
        	'count' => $totalBook
    	]);
    }

    public function withDrawal() {
        if ($this->countWalletThisWeek() >= 3) {
            return $this->apiError('Giới hạn số lần rút tiền 3 lần/tuần');
        }
    	$wallet = 0;
        $cod = Booking::where('sender_id', request()->user()->id)->where('status', 'completed')->where('COD', '>', 0)->where('COD_status', 'pending');
        if(request()->user()->role == 'collaborators') {
            $scope = Collaborator::where('user_id', request()->user()->id)->pluck('agency_id');
            $cod = $cod->whereIn('last_agency', $scope);
        }
        $codBookingIds = $cod->get()->pluck('id');
        $cod = $cod->sum('COD');

        // $booking = Booking::where('sender_id', request()->user()->id)->where('owe', 0)->where(function ($query) {
        //     $query->where('status', 'return')->where('sub_status', 'none')->whereHas('deliveries', function($d) {
        //         $d->where('category', 'return')->where('status', 'completed');
        //     })
        //         ->orWhere('status', 'completed')->whereHas('deliveries', function ($d1){
        //             $d1->where('category', 'send');
        //         });
        // });
        // không tính tiền đơn hàng trả lại
        $booking = Booking::where('sender_id', request()->user()->id)->where('owe', 0)->where(function ($query) {
            $query->where('status', 'completed')->whereHas('deliveries', function ($d1){
                    $d1->where('category', 'send');
                });
        });
        if (request()->user()->role == 'collaborators') {
            $user_id = request()->user()->id;
            $scope = Collaborator::where('user_id', $user_id)->pluck('agency_id');
            $booking = $booking->whereIn('last_agency', $scope);

        }
        $booking = $booking->select('bookings.*');
        $priceBookings = $booking->get();
        $price = round(($booking->sum('price') + $booking->sum('incurred')) - $booking->sum('paid'));

        $walletPrice = round($cod - $price);

        if ($walletPrice <= 0) {
            return $this->apiError('Không thể rút tiền nhỏ hơn hoặc bằng 0');
        }
        
        DB::beginTransaction();
        try {
            $wallet = new Wallet;
            $wallet->customer_id = request()->user()->id;
            $wallet->price = $walletPrice;
            $wallet->customer_name = request()->user()->name;
            $wallet->customer_phone_number = request()->user()->phone_number;
            $wallet->save();
            Wallet::where('id', $wallet->id)->update(['payment_code' => str_random(5) . $wallet->id]);

            // thanh toán COD đơn hàng
            Booking::whereIn('id', $codBookingIds)->update(['COD_status' => 'finish', 'payment_date' => date('Y-m-d H:i:s')]);

            // thanh toán cước đơn hàng
            $tmpBookIds = [];
            $tmpBookIds = $codBookingIds->toArray();

            foreach ($priceBookings as $booking) {
                Booking::where('id', $booking->id)->update(['owe' => 1, 'paid' => $booking->price + $booking->incurred]);
                
                if (!in_array($booking->id, $tmpBookIds)) {
                    $tmpBookIds[] = $booking->id;
                }
            }

            foreach ($tmpBookIds as $id) {
                $arr = [
                    'wallet_id' => $wallet->id,
                    'booking_id' => $id
                ];
                $tmp[] = $arr;
            }
            DB::table('bookings_wallets')->insert($tmp);

            DB::commit();
            $msg = Setting::where('key', 'msg_after_withdrawal')->first();
            $msg = empty($msg) ? 'Rút tiền thành công' : $msg->name;
            return $this->apiOk($msg);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->apiError($e);
        }
    }


    private function totalPrice() {
        $limit = request()->input('limit', 10);
    	// $booking = Booking::where('sender_id', request()->user()->id)->where('owe', 0)->where(function ($query) {
     //        $query->where('status', 'return')->where('sub_status', 'none')->whereHas('deliveries', function($d) {
     //            $d->where('category', 'return')->where('status', 'completed');
     //        })
     //            ->orWhere('status', 'completed')->whereHas('deliveries', function ($d1){
     //                $d1->where('category', 'send');
     //            });
     //    });
        // không tính tiền đơn hàng trả lại
        $booking = Booking::where('sender_id', request()->user()->id)->where('owe', 0)->where(function ($query) {
            $query->where('status', 'completed')->whereHas('deliveries', function ($d1){
                    $d1->where('category', 'send');
                });
        });
        $booking = $booking->select('bookings.id', 'bookings.uuid', 'bookings.name', 'bookings.send_full_address', 'bookings.receive_full_address', 'bookings.created_at', 'bookings.price', 'bookings.incurred', 'bookings.COD');
        $listBookings = $booking->paginate($limit);
        return [
        	'booking' => $booking,
        	'listBookings' => $listBookings
        ];
    }

    private function totalCOD() {
        $limit = request()->input('limit', 10);
    	$booking = Booking::where('sender_id', request()->user()->id)
    					->where('status', 'completed')
    					->where('COD', '>', 0)
    					->where('COD_status', 'pending');
        $booking = $booking->select('bookings.id', 'bookings.uuid', 'bookings.name', 'bookings.send_full_address', 'bookings.receive_full_address', 'bookings.created_at', 'bookings.price', 'bookings.incurred', 'bookings.COD');
        $listBookings = $booking->paginate($limit);
        return [
        	'booking' => $booking,
        	'listBookings' => $listBookings
        ];
    }

    public function getWalletDescription() {
        // $description['description'] = 'Đây là đoạn văn bản mô tả, hướng dẫn ví tiền.';
        // return $this->apiOk($description);
    }

    public function getTotalSummary() {
        $booking = $this->totalPrice()['booking'];
        $totalPrice = round(($booking->sum('price') + $booking->sum('incurred')) - $booking->sum('paid'));
        $bookingCOD = $this->totalCOD()['booking'];
        $totalCOD = $bookingCOD->sum('COD');
        $data['total_price'] = $totalPrice;
        $data['total_cod'] = $totalCOD;
        $data['total_wallet'] = $totalCOD - $totalPrice;
        $data['description'] = 'Ví tiền = Tổng tiền thu hộ COD - Tổng tiền cước';
        return $this->apiOk($data);
    }

    // kiểm tra chỉ được rút tiền 3 lần/tuần
    private function countWalletThisWeek() {
        $date = date('Y-m-d');
        $fromDate = date("Y-m-d", strtotime('monday this week', strtotime($date))); //start this week
        $endDate = date("Y-m-d", strtotime('sunday this week', strtotime($date))); //end this week
        $wallets = Wallet::where('customer_id', request()->user()->id)
                            ->whereDate('created_at', '>=', $fromDate)
                            ->whereDate('created_at', '<=', $endDate)
                            ->count();
        return $wallets;
    }
}
