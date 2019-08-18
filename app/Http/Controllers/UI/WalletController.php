<?php

namespace App\Http\Controllers\UI;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\BookingWallet;
use App\Models\Booking;
use App\Models\Collaborator;
use Auth;
use DB;

class WalletController extends Controller
{
    public function __construct() {
        $this->middleware('ui.auth');
    }

    public function getTotalPrice() {
        $booking = $this->totalPrice()['booking'];
        $listBookings = $this->totalPrice()['listBookings'];
        $totalPrice = round(($booking->sum('price') + $booking->sum('incurred')) - $booking->sum('paid'));    	
        return view('front-ent.element.wallets.index', [
        	'bookings' => $listBookings, 
        	'active' => 'totalPrice', 
        	'count' => $totalPrice
        ]);
    }

    public function getTotalCOD() {
    	$booking = $this->totalCOD()['booking'];
        $listBookings = $this->totalCOD()['listBookings'];
        $totalCOD = $booking->sum('COD');
        return view('front-ent.element.wallets.index', [
        	'bookings' => $listBookings, 
        	'active' => 'totalCOD', 
        	'count' => $totalCOD
        ]);
    }

    public function getWallet() {
    	$bookingTotalPrice = $this->totalPrice()['booking'];
    	$totalPrice = round(($bookingTotalPrice->sum('price') + $bookingTotalPrice->sum('incurred')) - $bookingTotalPrice->sum('paid'));    	
    	$bookingTotalCOD = $this->totalCOD()['booking'];
        $totalCOD = $bookingTotalCOD->sum('COD');
        $totalWallet = $totalCOD - $totalPrice;
        $wallets = Wallet::where('customer_id', Auth::user()->id)
        					->orderBy('created_at', 'DES')
        					->paginate(10);
        return view('front-ent.element.wallets.wallet', [
        	'wallets' => $wallets, 
        	'active' => 'wallet', 
        	'count' => $totalWallet,
            'countWallet' => $this->countWalletThisWeek()
        ]);
    }

    public function getListBook($walletId) {
    	$bookIds = BookingWallet::where('wallet_id', $walletId)->get()->pluck('booking_id')->toArray();
    	$db = Booking::whereIn('id', $bookIds);
    	$totalBook = $db->count();
    	$bookings = $db->orderBy('created_at', 'DESC')->paginate(10);
    	return view('front-ent.element.wallets.list_book', [
    		'active' => 'wallet', 
    		'bookings' => $bookings, 
        	'count' => $totalBook
    	]);
    }

    public function withDrawal() {
        if ($this->countWalletThisWeek() >= 3) {
            return redirect()->back();
        }

    	$wallet = 0;
        $cod = Booking::where('sender_id', Auth::user()->id)->where('status', 'completed')->where('COD', '>', 0)->where('COD_status', 'pending');
        if(Auth::user()->role == 'collaborators') {
            $scope = Collaborator::where('user_id', Auth::user()->id)->pluck('agency_id');
            $cod = $cod->whereIn('last_agency', $scope);
        }
        $codBookingIds = $cod->get()->pluck('id');
        $cod = $cod->sum('COD');

        // $booking = Booking::where('sender_id', Auth::user()->id)->where('owe', 0)->where(function ($query) {
        //     $query->where('status', 'return')->where('sub_status', 'none')->whereHas('deliveries', function($d) {
        //         $d->where('category', 'return')->where('status', 'completed');
        //     })
        //         ->orWhere('status', 'completed')->whereHas('deliveries', function ($d1){
        //             $d1->where('category', 'send');
        //         });
        // });
        // không tính tiền đơn hàng trả lại
        $booking = Booking::where('sender_id', Auth::user()->id)->where('owe', 0)->where(function ($query) {
            $query->where('status', 'completed')->whereHas('deliveries', function ($d1){
                    $d1->where('category', 'send');
                });
        });
        if (Auth::user()->role == 'collaborators') {
            $user_id = Auth::user()->id;
            $scope = Collaborator::where('user_id', $user_id)->pluck('agency_id');
            $booking = $booking->whereIn('last_agency', $scope);

        }
        $booking = $booking->select('bookings.*');
        $priceBookings = $booking->get();
        $price = round(($booking->sum('price') + $booking->sum('incurred')) - $booking->sum('paid'));

        $walletPrice = round($cod - $price);

        if ($walletPrice <= 0) {
            return redirect()->back();
        }
        
        DB::beginTransaction();
        try {
            $wallet = new Wallet;
            $wallet->customer_id = Auth::user()->id;
            $wallet->price = $walletPrice;
            $wallet->customer_name = Auth::user()->name;
            $wallet->customer_phone_number = Auth::user()->phone_number;
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
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }

        return redirect()->back();
    }


    private function totalPrice() {
    	// $booking = Booking::where('sender_id', Auth::user()->id)->where('owe', 0)->where(function ($query) {
     //        $query->where('status', 'return')->where('sub_status', 'none')->whereHas('deliveries', function($d) {
     //            $d->where('category', 'return')->where('status', 'completed');
     //        })
     //            ->orWhere('status', 'completed')->whereHas('deliveries', function ($d1){
     //                $d1->where('category', 'send');
     //            });
     //    });
        // không tính tiền đơn hàng trả lại
        $booking = Booking::where('sender_id', Auth::user()->id)->where('owe', 0)->where(function ($query) {
            $query->where('status', 'completed')->whereHas('deliveries', function ($d1){
                    $d1->where('category', 'send');
                });
        });
        $booking = $booking->select('bookings.*');
        $listBookings = $booking->paginate(10);
        return [
        	'booking' => $booking,
        	'listBookings' => $listBookings
        ];
    }

    private function totalCOD() {
    	$booking = Booking::where('sender_id', Auth::user()->id)
    					->where('status', 'completed')
    					->where('COD', '>', 0)
    					->where('COD_status', 'pending');
        $listBookings = $booking->paginate(10);
        return [
        	'booking' => $booking,
        	'listBookings' => $listBookings
        ];
    }

    // kiểm tra chỉ được rút tiền 3 lần/tuần
    private function countWalletThisWeek() {
        $date = date('Y-m-d');
        $fromDate = date("Y-m-d", strtotime('monday this week', strtotime($date))); //start this week
        $endDate = date("Y-m-d", strtotime('sunday this week', strtotime($date))); //end this week
        $wallets = Wallet::where('customer_id', Auth::user()->id)
                            ->whereDate('created_at', '>=', $fromDate)
                            ->whereDate('created_at', '<=', $endDate)
                            ->count();
        return $wallets;
    }
}
