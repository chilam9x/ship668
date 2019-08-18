<?php

namespace App\Http\Controllers\Admin\Wallet;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\BookingWallet;
use App\Models\Booking;
use Auth, Excel;

class WalletController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    protected $breadcrumb = ['Rút tiền'];

    public function getNonPayment() {
        $time_from = date('Y-m-d');
    	return view('admin.elements.wallets.non_payment', ['active' => 'non_payment', 'breadcrumb' => $this->breadcrumb, 'time_from' => $time_from]);
    }

    public function getPaymented() {
        $monday = date( 'Y-m-d', strtotime( 'monday this week' ) );
        $sunday = date( 'Y-m-d', strtotime( 'sunday this week' ) );
    	return view('admin.elements.wallets.paymented', ['active' => 'paymented', 'breadcrumb' => $this->breadcrumb]);
    }

    public function getUpdate($walletId) {
        $withdrawal_type = isset(request()->withdrawal_type) ? request()->withdrawal_type : 'cash';
        $wallet = Wallet::find($walletId);
        $wallet->withdrawal_type = $withdrawal_type;
        $wallet->payment_status = 1;
        $wallet->payment_date = date('Y-m-d H:i:s');
        // $wallet->payment_code = str_random(5) . $walletId;
        $wallet->save();
        return redirect()->back();
    }

    public function getUpdateStatus($walletId) {
        $wallet = Wallet::find($walletId);
        $wallet->payment_status = 0;
        $wallet->payment_date = null;
        $wallet->withdrawal_type = null;
        $wallet->save();
        return redirect()->back();
    }

    public function getBookings($walletId) {
        $active = isset(request()->active) ? request()->active : 'non_payment';
        return view('admin.elements.wallets.bookings', ['active' => $active, 'breadcrumb' => $this->breadcrumb, 'walletId' => $walletId]);
    }

    public function exportBooking($walletId) {
        $path = 'donhang.xlsx';
        $bookIds = BookingWallet::where('wallet_id', $walletId)->get()->pluck('booking_id')->toArray();
        $booking = Booking::whereIn('bookings.id', $bookIds);
        $booking = $booking->with(['deliveries', 'shipperSender', 'shipperRecivcier'])
                        ->orderBy('bookings.created_at', 'desc')
                        ->get();
        
        $result = [];
        foreach ($booking as $index => $b) {
            $data['Stt'] = $index + 1;
            $data['uuid'] = $b->uuid;
            if ($b->status == 'new') {
                $data['status'] = 'Mới';
            } else if ($b->status == 'return') {
                $data['status'] = 'Trả lại';
            } else if ($b->sub_status == 'delay') {
                $data['status'] = 'Delay';
            } else if ($b->status == 'cancel') {
                $data['status'] = 'Hủy';
            } else if ($b->status == 'taking') {
                $data['status'] = 'Đang đi lấy';
            } else {
                $data['status'] = $b->status  == 'sending' ? 'Đang giao hàng' : 'Đã giao hàng';
            }
            $data['receive_name'] = $b->receive_name;
            $data['receive_phone'] = $b->receive_phone;
            $data['receive_full_address'] = $b->receive_full_address;
            $data['price'] = $b->price;
            $data['owe'] = $b->owe == 1 ? 'Đã thanh toán' : 'Chưa thanh toán';
            $data['COD'] = $b->COD;
            $data['payment_type'] = $b->payment_type == 1 ? 'Người gửi trả cước' : 'Người nhận trả cước';
            $data['weight'] = $b->weight;
            if ($b->transport_type == 1) {
                $data['transport_type'] = 'Giao chuẩn';
            } else if ($b->transport_type == 2) {
                $data['transport_type'] = 'Giao tiết kiệm';
            } else if ($b->transport_type == 3) {
                $data['transport_type'] = 'Giao siêu tốc';
            } else if ($b->transport_type == 4) {
                $data['transport_type'] = 'Giao thu COD';
            }
            $data['incurred'] = $b->incurred;
            $data['COD_status'] = $b->COD_status == 'pending' ? 'Chưa thanh toán' : 'Đã thanh toán';
            $data['created_at'] = $b->created_at;
            $data['updated_at'] = $b->updated_at;
            $data['shipper_receive'] = @$b->shipperRecivcier->shipper_name;
            $data['shipper_send'] = @$b->shipperSender->shipper_name;

            $result[] = $data;
        }
        $file_path = public_path('/excel_temp/customers/' . $path);
        Excel::load($file_path, function ($reader) use ($result) {
            $reader->sheet('Sheet1', function ($sheet) use ($result) {
                $sheet->cell('A1', function ($cell) {
                    $titleExcel = 'DANH SÁCH ĐƠN HÀNG';
                    $cell->setValue($titleExcel);
                });
                $sheet->cell('R1', function ($cell) {
                    $cell->setValue('Ngày: ');
                });
                $sheet->fromArray($result, null, 'A6', true, false);
            });

        })->setFilename('DanhSachDonHangCuaKhachHang')->export('xlsx');
    }
}
