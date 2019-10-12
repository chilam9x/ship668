<?php

namespace App\Http\Controllers\Ajax;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Wallet;
use Form, DB;
use App\User;
use App\Models\Booking;
use Auth;
use App\Models\BookDelivery;
use App\Models\Agency;
use App\Models\ReportImage;
use App\Models\BookingWallet;

class WalletController extends Controller
{
    public function getNonPayment() {
    	$results = Wallet::where('payment_status', 0)->with(['user']);
        return datatables()->of($results)
            ->addColumn('bank_account', function($res){
                return $res->user->bank_account;
            })
            ->addColumn('bank_account_number', function($res){
                return $res->user->bank_account_number;
            })
            ->addColumn('bank_name', function($res){
                return $res->user->bank_name;
            })
            ->addColumn('bank_branch', function($res){
                return $res->user->bank_branch;
            })
            ->editColumn('price', function($res){
                return number_format($res->price);
            })
            ->addColumn('action', function ($res) {
                $action = [];
                $action[] = '<a href="' . url('admin/wallet/update/' . $res->id . '?withdrawal_type=cash') . '" class="btn btn-xs btn-primary" onclick="return confirm(\'Bạn có chắc chắn muốn thanh toán tiền mặt?\')"><span class="glyphicon glyphicon-usd" aria-hidden="true"></span> Tiền mặt</a>';
                $action[] = '<a href="' . url('admin/wallet/update/' . $res->id . '?withdrawal_type=transfer') . '" class="btn btn-xs btn-success" onclick="return confirm(\'Bạn có chắc chắn muốn thanh toán chuyển khoản?\')"><span class="glyphicon glyphicon-usd" aria-hidden="true"></span> Chuyển khoản</a>';
                $action[] = '<br><a href="' . url('admin/wallet/bookings/' . $res->id) . '?active=non_payment' . '" class="btn btn-xs btn-default"><i class="glyphicon glyphicon-edit"></i> Xem DS đơn hàng</a>';
                return implode(' ', $action);
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function getPaymented() {
    	$results = Wallet::where('payment_status', 1)->with(['user']);
        return datatables()->of($results)
            ->addColumn('bank_account', function($res){
                return $res->user->bank_account;
            })
            ->addColumn('bank_account_number', function($res){
                return $res->user->bank_account_number;
            })
            ->addColumn('bank_name', function($res){
                return $res->user->bank_name;
            })
            ->addColumn('bank_branch', function($res){
                return $res->user->bank_branch;
            })
            ->editColumn('price', function($res){
                return number_format($res->price);
            })
            ->addColumn('action', function ($res) {
                $action = [];
                $action[] = '<a href="' . url('admin/wallet/update-status/' . $res->id . '?active=paymented') . '" class="btn btn-xs btn-primary"><span class="glyphicon glyphicon-repeat" aria-hidden="true"></span> Hoàn tác thanh toán</a>';
                $action[] = '<a href="' . url('admin/wallet/bookings/' . $res->id . '?active=paymented') . '" class="btn btn-xs btn-default"><i class="glyphicon glyphicon-edit"></i> Xem DS đơn hàng</a>';
                return implode(' ', $action);
            })
            ->editColumn('withdrawal_type', function($res){
            	return $res->withdrawal_type == 'cash' ? 'Tiền mặt' : 'Chuyển khoản';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function getBookings($walletId) {
    	$bookIds = BookingWallet::where('wallet_id', $walletId)->get()->pluck('booking_id')->toArray();
    	$booking = Booking::whereIn('id', $bookIds);
        $scope = null;
        if (Auth::user()->role == 'collaborators') {
            $user_id = Auth::user()->id;
            $scope = Collaborator::where('user_id', $user_id)->pluck('agency_id');
            $booking = $booking->whereIn('first_agency', $scope)->orWhere('last_agency', $scope);
        }
        $result = $scope != null ? $scope->toArray() : [];
        // $booking = $booking->orderBy('id', 'desc')->get();
        return datatables()->of($booking)
            ->addColumn('receiveShipper', function ($b) {
                if ($b->status != null) {
                    if ($b->status != 'new') {
                        return @BookDelivery::where('book_id', $b->id)->where('category', 'receive')->where('status', 'completed')->first()->shipper_name;
                    }
                }
                return '';
            })
            ->addColumn('sendShipper', function ($b) {
                if ($b->status != null) {
                    if ($b->status != 'new') {
                        return @BookDelivery::where('book_id', $b->id)->where('category', 'send')->where('status', 'completed')->first()->shipper_name;
                    }
                }
                return '';
            })
            ->editColumn('COD_status', function ($b) use ($result) {
                if ($b->COD > 0) {
                    if (Auth::user()->role == 'collaborators') {
                        if ($b->payment_type == 1) {
                            if (in_array($b->first_agency, $result)) {
                                return $b->COD_status == 'finish' ? '<img src="' . asset('public/img/corect.png') . '" width="30px"></img>' :
                                    '<img onclick="changeCODStatus(' . $b->id . ')" src="' . asset('public/img/incorect.png') . '" width="30px"></img>';
                            } else {
                                return $b->COD_status == 'finish' ? '<img src="' . asset('public/img/corect.png') . '" width="30px"></img>' :
                                    '<img src="' . asset('public/img/incorect.png') . '" width="30px"></img>';
                            }
                        } else {
                            if (in_array($b->last_agency, $result)) {
                                return $b->COD_status == 'finish' ? '<img src="' . asset('public/img/corect.png') . '" width="30px"></img>' :
                                    '<img onclick="changeCODStatus(' . $b->id . ')" src="' . asset('/publicimg/incorect.png') . '" width="30px"></img>';
                            } else {
                                return $b->COD_status == 'finish' ? '<img src="' . asset('public/img/corect.png') . '" width="30px"></img>' :
                                    '<img src="' . asset('public/img/incorect.png') . '" width="30px"></img>';
                            }
                        }
                    } else {
                        return $b->COD_status == 'finish' ? '<img src="' . asset('public/img/corect.png') . '" width="30px"></img>' :
                            '<img onclick="changeCODStatus(' . $b->id . ')" src="' . asset('public/img/incorect.png') . '" width="30px"></img>';
                    }
                }
                return '';
            })
            ->editColumn('payment_date', function ($b) {
                return $b->payment_date != null ? $b->payment_date : '';
            })
            ->editColumn('payment_type', function ($b) {
                return $b->payment_type == 1 ? 'Người gửi trả cước' : 'Người nhận trả cước';
            })
            ->editColumn('transport_type', function ($b) {
                $tran = '';
                if ($b->transport_type == 1) {
                    $tran = 'Giao chuẩn';
                } else if ($b->transport_type == 2) {
                    $tran = 'Giao tiết kiệm';
                } else if ($b->transport_type == 3) {
                    $tran = 'Giao siêu tốc';
                } else {
                    $tran = 'Giao thu COD';
                }
                return $tran;
            })
            ->addColumn('report_image', function ($b) {
                $delivery = BookDelivery::where('book_id', $b->id)->where('category', 'send')->where('status', 'completed')
                    ->select('id')->get();
                $image = '';
                if (isset($delivery)) {
                    foreach ($delivery as $d) {
                        $url = ReportImage::where('task_id', $d->id)->get();
                        if (isset($url)) {
                            foreach ($url as $k => $u) {
                                if ($k == 0) {
                                    $image .= '<a href="' . asset('/' . $u->image) . '" data-lightbox="' . $b->id . '"><button class="btn btn-xs btn-info">Hiển thị</button></a>';
                                }
                                if ($k > 0) {
                                    $image .= '<a href="' . asset('/' . $u->image) . '" data-lightbox="' . $b->id . '"></a>';
                                }
                            }
                        }
                    }
                }
                return $image;
            })
            ->rawColumns(['COD_status', 'report_image'])
            ->make(true);
    }

    public function getQuickAssign() {
        $db = Wallet::where('payment_status', 0)->with(['user']);

        if (!empty(request()->date_from)) {
            $db = $db->whereDate('created_at', '>=', request()->date_from);
        }
        if (!empty(request()->date_to)) {
            $db = $db->whereDate('created_at', '<=', request()->date_to);
        }
        if (!empty(request()->phone)) {
            $db = $db->where('customer_phone_number', 'LIKE', '%' . request()->phone . '%');
        }

        $wallets = $db->orderBy('created_at', 'DESC')->get();
        return json_encode($wallets);
    }

    public function postQuickAssign() {
        $data['wallet_ids'] = [];
        $data['withdrawal_type'] = '';
        foreach (request()->inputs as $key => $value) {
            if ($value['name'] == 'withdrawal_type') {
                $data['withdrawal_type'] = $value['value'];
            } elseif ($value['name'] == 'wallets') {
                $data['wallet_ids'][] = $value['value'];
            }
        }

        if (empty($data['withdrawal_type'])) {
            return json_encode(['status' => 'Chọn phương thức thanh toán!']);
        }

        if (count($data['wallet_ids']) <= 0) {
            return json_encode(['status' => 'Chọn ít nhất 1 yêu cầu rút tiền!']);
        }
        
        DB::beginTransaction();
        try {
            Wallet::whereIn('id', $data['wallet_ids'])->update([
                'payment_status' => 1,
                'payment_date' => date('Y-m-d H:m:s'),
                'withdrawal_type' => $data['withdrawal_type']
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }

        return json_encode(['status' => 'success']);
    }
}
