<?php

namespace App\Http\Controllers\Ajax;

use App\Models\BookDelivery;
use App\Models\Booking;
use App\Models\Collaborator;
use App\Models\District;
use App\Models\ManagementScope;
use App\Models\ManagementWardScope;
use App\Models\Province;
use App\Models\User;
use App\Models\Ward;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use function number_format;

class CODController extends Controller
{

    public function totalCOD()
    {
        if(Auth::user()->role == 'collaborators'){
            $user_id = Auth::user()->id;
            $scope = Collaborator::where('user_id', $user_id)->pluck('agency_id');
            $booking = Booking::whereIn('last_agency', $scope)->where('status', 'completed')->where('COD', '>', 0)->where('COD_status', 'pending')->pluck('sender_id');
            $user = User::where('total_COD', '>', '0')->whereIn('id', $booking)->get();
        }else{
            $user = User::where('total_COD', '>', '0')->get();
        }
        return datatables()->of($user)
            ->editColumn('name', function ($u) {
                return $u->name != null ? $u->name : '';
            })
            ->editColumn('email', function ($u) {
                return $u->email != null ? $u->email : '';
            })
            ->editColumn('total_COD', function ($u) {
                $cod = $u->total_COD;
                if(Auth::user()->role == 'collaborators') {
                    $scope = Collaborator::where('user_id', Auth::user()->id)->pluck('agency_id');
                    $cod = Booking::where('sender_id', $u->id)->whereIn('last_agency', $scope)->where('status', 'completed')->where('COD', '>', 0)->where('COD_status', 'pending')->sum('COD');
                }
                    return number_format($cod);
            })
            ->addColumn('full_address', function ($u) {
                if ($u->province_id != null && $u->district_id != null && $u->ward_id != null) {
                    $province_name = Province::find($u->province_id)->name;
                    $district_name = District::find($u->district_id)->name;
                    $ward_name = Ward::find($u->ward_id)->name;
                    if ($u->home_number != null) {
                        return $u->home_number . ', ' . $ward_name . ', ' . $district_name . ', ' . $province_name;
                    }
                    return $ward_name . ', ' . $district_name . ', ' . $province_name;
                }
                return '';
            })
            ->addColumn('receiver_total', function ($u) {
                $total = '';
                if ($u->id != null) {
                    $total = number_format(Booking::where('sender_id', $u->id)->where('status', 'completed')->where('payment_type', 2)->where('COD', '>', 0)->where('COD_status', 'pending')->sum('price', '+', 'incurred'));
                }
                return $total;
            })
            ->addColumn('send_total', function ($u) {
                $total = '';
                if ($u->id != null) {
                    $total = number_format(Booking::where('sender_id', $u->id)->where('status', 'completed')->where('payment_type', 1)->where('COD', '>', 0)->where('COD_status', 'pending')->sum('price', '+', 'incurred'));
                }
                return $total;
            })
            ->addColumn('action', function ($u) {
                $action = [];
                $action[] = '<div style="display: inline-flex"><a href="' . url('admin/COD_details/' . $u->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-eye"></i> Chi tiết</a>';
                $action[] = '<a href="' . url('admin/paid_COD/' . $u->id) . '" onclick="if(!confirm(\'Bạn chắc chắn đã thanh toán toàn bộ tiền thu hộ của đơn hàng này?\')) return false;" class="btn btn-xs btn-danger"><i class="fa fa-refresh"></i> Thanh toán toàn bộ</a></div';
                return implode(' ', $action);
            })
            ->rawColumns(['COD_status', 'action'])
            ->make(true);
    }

    public function codDetails($id){
        $booking = Booking::where('sender_id', $id)->where('status', 'completed')->where('COD', '>', 0)->where('COD_status', 'pending');
        if(Auth::user()->role == 'collaborators'){
            $user_id = Auth::user()->id;
            $scope = Collaborator::where('user_id', $user_id)->pluck('agency_id');
            $booking = $booking->whereIn('last_agency', $scope);
        }
        $booking = $booking->get();
        return datatables()->of($booking)
            ->addColumn('shipper', function ($b) {
                if ($b->status != 'new') {
                    return BookDelivery::where('book_id', $b->id)->first()->shipper_name;
                }
                return '';
            })
            ->editColumn('COD_status', function ($b) {
                if ($b->COD > 0) {
                    return $b->COD_status == 'finish' ? '<img src="' . asset('public/img/corect.png') . '" width="30px"></img>' :
                        '<img onclick="changeCODStatus(' . $b->id . ')" src="' . asset('public/img/incorect.png') . '" width="30px"></img>';
                }
                return '';
            })
            ->editColumn('payment_date', function ($b) {
                return $b->payment_date != null ? $b->payment_date : '';
            })
            ->editColumn('COD', function ($b) {
                return number_format($b->COD);
            })
            ->editColumn('payment_type', function ($b) {
                return $b->payment_type == 1 ? 'Người gửi trả cước' : 'Người nhận trả cước';
            })
            ->rawColumns(['COD_status'])
            ->make(true);
    }
}
