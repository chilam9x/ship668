<?php

namespace App\Http\Controllers\Ajax;


use App\Models\QRCode;
use App\Models\Booking;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use function url;

class QRCodeController extends Controller
{
    public function qrcode()
    {
        $qrcode = QRCode::all();
        return datatables()->of($qrcode)
        ->editColumn('name', function ($q) {
            return \QrCode::size(100)->generate($q->name).'<br>'.$q->name;
        })
        ->editColumn('is_used', function ($q) {
            $is_used = '';
            if ($q->is_used == 0) {
                $is_used = '<span class="bg-warning"> Chưa sử dụng </span>';
            } else if ($q->is_used == 1) {
                $is_used = '<span class="bg-primary"> Đã sử dụng </span>';
            }
            return $is_used;
        })
        ->addColumn('id_booking', function ($q) {
            $booking=Booking::where('qrcode_id',$q->id)->select('id')->first();
            return $booking['id'];
        })
        ->rawColumns(['name','is_used','id_booking'])
        ->make(true);
    }

}
