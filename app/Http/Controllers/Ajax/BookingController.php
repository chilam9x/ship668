<?php

namespace App\Http\Controllers\Ajax;

use App\Models\Agency;
use App\Models\ReportImage;
use function count;
use function foo\func;
use Illuminate\Http\Request;
use App\Models\BookDelivery;
use App\Models\Booking;
use App\Models\Collaborator;
use App\Models\ManagementScope;
use App\Models\ManagementWardScope;
use App\Models\User;
use Carbon\Carbon;
use function dd;
use App\Http\Controllers\Controller;
use Auth, DB;
use function in_array;
use function microtime;
use function url;

class BookingController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin', ['only' => [
            'removeBookingByTime'
        ]]);
    }

    protected function getBookingScope()
    {
        $user_id = Auth::user()->id;
        $scope = Collaborator::where('user_id', $user_id)->pluck('agency_id');
        $ward = ManagementWardScope::whereIn('agency_id', $scope)->pluck('ward_id');
        return $ward;
    }

    public function newBooking()
    {
        if (Auth::user()->role == 'collaborators') {
            // $booking = Booking::where('status', 'new')->where('sub_status', 'none')->whereIn('send_ward_id', $this->getBookingScope())
            //     ->orwhere('status', 'taking')->where('sub_status', 'none')->whereIn('send_ward_id', $this->getBookingScope());
            $booking = Booking::where(function($q){
                $q->where(function($q1){
                    $q1->where('status', 'new')->where('sub_status', 'none')->whereIn('send_ward_id', $this->getBookingScope());
                });
                $q->orWhere(function($q2){
                    $q2->where('status', 'taking')->where('sub_status', 'none')->whereIn('send_ward_id', $this->getBookingScope());        
                });
            });
        } else {
            $booking = Booking::whereIn('status', ['new', 'taking'])->where('sub_status', 'none');
        }
        // $booking = $booking->orderBy('id', 'DESC')->orderBy('status', 'desc')->get();
        $booking = $booking->with(['deliveries']);
        return datatables()->of($booking)
            ->addColumn('action', function ($b) {
                $action = [];
                $check = BookDelivery::where('book_id', $b->id)->where('category', 'receive')->where('status', 'processing')->first();
                if (empty($check)) {
                    $action[] = '<div style="display: inline-flex"><a href="' . url('admin/booking/assign/' . $b->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-motorcycle"></i> Phân công</a>';
                } else {
                    $action[] = '<div style="display: inline-flex"><a href="' . url('admin/booking/reassign/taking/' . $b->id) . '" class="btn btn-xs btn-success"><i class="fa fa-motorcycle"></i> Phân công lại</a>';
                    if ($b->payment_type == 1) {
                        $action[] = '<a data-toggle="popover" data-placement="top" data-html="true" title="<p><b>Đã thanh toán</b></p>" 
                            data-content="<div style=\'display: inline-flex\'><input id=\'owe\' style=\'transform: scale(1.5);\' onclick=\'changeUrl()\' type=\'checkbox\'> 
                            <a id=\'owe_submit\' href=' . url('admin/booking/completed/receive/' . $b->id) . ' class=\'btn btn-xs btn-success\' style=\'background: green; margin-left: 10px\'>
                            <i class=\'fa fa-check\'></i> Thực hiện</a></div>" class="btn btn-xs btn-success" style="background: green">Đã lấy</a>';
                    } else {
                        $action[] = '<a href="' . url('admin/booking/completed/receive/' . $b->id) . '" class="btn btn-xs btn-success" style="background: green" ><i class="fa fa-check"></i> Đã lấy</a>';
                    }
                    $action[] = '<a style="background: pink" href="' . url('admin/booking/delay/receive/' . $b->id) . '" class="btn btn-xs btn-warning"><i class="fa fa-clock-o" aria-hidden="true"></i> Delay</a>';
                }
                $action[] = '<a style="background: rgba(131,1,7,0.98)" href="' . url('admin/booking/cancel/new/' . $b->id) . '" onclick="if(!confirm(\'Bạn chắc chắn muốn hủy đơn hàng này không ?\')) return false;" class="btn btn-xs btn-primary"><i class="fa fa-remove"></i> Hủy</a></div>';

                $action[] = '<div style="margin-top: 5px; display: inline-flex"><a href="' . url('admin/booking/print/new/' . $b->id) . '" class="btn btn-xs btn-info"><i class="fa fa-print" aria-hidden="true"></i> in hóa đơn</a>';
                $action[] = '<a style="background: rgba(159,158,25,0.81)" href="' . url('admin/booking/update/new/' . $b->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i> Sửa</a>';
                $action[] = '<a style="background: rgba(73,4,70,0.87)" href="' . url('admin/booking/delete/new/' . $b->id) . '" onclick="if(!confirm(\'Bạn chắc chắn muốn xóa đơn hàng này không ?\')) return false;" class="btn btn-xs btn-primary"><i class="fa fa-trash"></i> Xóa</a></div>';
                return implode(' ', $action);
            })
            ->addColumn('shipper', function ($b) {
                if ($b->status != null) {
                    if ($b->status == 'taking') {
                        return @BookDelivery::where('book_id', $b->id)->where('category', 'receive')->first()->shipper_name;
                    }
                }
                return '';
            })
            ->addColumn('report_image', function ($b) {
                $delivery = BookDelivery::where('book_id', $b->id)->where('category', 'receive')->where('status', 'processing')->select('id')->get();
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
            ->editColumn('status', function ($b) {
                return $b->status == 'new' ? 'Mới' : 'Đang lấy';
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
            ->editColumn('payment_type', function ($b) {
                return $b->payment_type == 1 ? 'Người gửi trả cước' : 'Người nhận trả cước';
            })
            ->editColumn('send_name', function ($b) {
                return $b->send_name . ($b->is_customer_new == 1 ? '<span class="badge badge-success"> Khách mới </span>' : '');
            })
            ->editColumn('user_create', function ($b) {
                return $b->sender->name . ' ' . $b->sender->phone_number;
            })
            ->editColumn('receive_created_at', function ($b) {
                $delivery = BookDelivery::where('book_id', $b->id)->where('category', 'receive')->where('status', 'processing')->where('sending_active', 1)->select('created_at as receive_created_at')->first();
                return !empty($delivery) ? $delivery->receive_created_at : '';
            })
            ->rawColumns(['report_image', 'action', 'send_name'])
            ->make(true);
    }

    public function receiveBooking()
    {
        if (Auth::user()->role == 'collaborators') {
            $user_id = Auth::user()->id;
            $agency_scope = Collaborator::where('user_id', $user_id)->pluck('agency_id');
            // $booking = Booking::where('status', 'sending')->where('sub_status', 'none')->whereHas('deliveries', function ($query) {
            //     $query->where('sending_active', 1);
            // });
            // $booking = $booking->whereIn('current_agency', $agency_scope)->orWhere('status', 'move')->where('sub_status', 'none')->whereIn('current_agency', $agency_scope)
            //     ->whereHas('deliveries', function ($query) {
            //         $query->where('category', 'move')->where('status', 'completed')->where('last_move', 1);
            //     });
            $booking = Booking::where(function($q) use ($agency_scope){
                $q->where(function($q1){
                    $q1->where('status', 'sending')->where('sub_status', 'none')->whereHas('deliveries', function ($query) {
                        $query->where('sending_active', 1);
                    });
                });
                $q->orWhere(function($q2) use ($agency_scope){
                    $q2->whereIn('current_agency', $agency_scope)->where('status', 'move')->where('sub_status', 'none')->whereIn('current_agency', $agency_scope)
                        ->whereHas('deliveries', function ($query) {
                            $query->where('category', 'move')->where('status', 'completed')->where('last_move', 1);
                        });
                });
            });
        } else {
            // $booking = Booking::where('status', 'sending')->where('sub_status', 'none')->whereHas('deliveries', function ($query) {
            //     $query->where('sending_active', 1);
            // });
            // $booking = $booking->orWhere('status', 'move')->where('sub_status', 'none')->whereHas('deliveries', function ($query) {
            //     $query->where('category', 'move')->where('status', 'completed');
            // });

            $booking = Booking::where(function($q){
                $q->where(function($q1){
                    $q1->where('status', 'sending')->where('sub_status', 'none')->whereHas('deliveries', function ($query) {
                        $query->where('sending_active', 1);
                    });
                });
                $q->orWhere(function($q2){
                    $q2->where('status', 'move')->where('sub_status', 'none')->whereHas('deliveries', function ($query) {
                        $query->where('category', 'move')->where('status', 'completed');
                    });
                });
            });
        }
        $booking = $booking->with(['firstAgencies', 'currentAgencies', 'deliveries','shipperSender', 'shipperRecivcier']);

        request()->session()->put('search_status', request()->search_status);
        request()->session()->put('search_shipper', request()->search_shipper);
        if (request()->session()->has('search_status') && request()->session()->get('search_status') == 'no_assign') {
            $booking = $booking->whereHas('deliveries', function($query){
                $query->where('status', 'completed')->where('category', 'receive')->where('sending_active', 1);
            });
        }
        if ( request()->session()->has('search_shipper') && !empty(request()->session()->get('search_shipper')) ) {
            $shipperIds = User::where('role', 'shipper')
                            ->where('status', 'active')
                            ->where('delete_status', 0)
                            ->where(function($query){
                                $query->where('name', 'LIKE', '%' . request()->session()->get('search_shipper') . '%')
                                    ->orWhere('phone_number', 'LIKE', '%' . request()->session()->get('search_shipper') . '%');
                            })
                            ->select('id')
                            ->pluck('id')->toArray();
            $booking = $booking->whereHas('deliveries', function($query) use ($shipperIds){
                $query->whereIn('user_id', $shipperIds);
            });
        }

        return datatables()->of($booking)
            ->addColumn('shipper_name', function (Booking $booking) {
                return $booking->shipperSender ? $booking->shipperSender->shipper_name : $booking->shipperRecivcier->shipper_name;
            })
            ->addColumn('first_agency_name', function (Booking $booking) {
                return $booking->firstAgencies ? $booking->firstAgencies->name : '';
            })
            ->addColumn('current_agency_name', function (Booking $booking) {
                return $booking->currentAgencies ? $booking->currentAgencies->name : '';
            })
            ->addColumn('action', function ($b) {
                $action = [];
                if ($b->action_status == 0) {
                    $action[] = '<div style="display: inline-flex"><a href="' . url('admin/booking/send_assign/' . $b->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-motorcycle" aria-hidden="true"></i> Phân công</a>';
                    $action[] = '<a class="btn btn-xs btn-warning" href="#" onclick="moveBooking(' . $b->id . ')"><i class="fa fa-share" aria-hidden="true"></i> Chuyển kho</a>';
                } else {
                    $action[] = '<div style="display: inline-flex"><a href="' . url('admin/booking/reassign/sending/' . $b->id) . '" class="btn btn-xs btn-success"><i class="fa fa-motorcycle"></i> Phân công lại</a>';
                    $action[] = '<a href="' . url('admin/booking/completed/send/' . $b->id) . '" class="btn btn-xs btn-success" style="background: green" ><i class="fa fa-check"></i> Đã giao</a>';
                    $action[] = '<a href="' . url('admin/booking/deny/' . $b->id) . '" class="btn btn-xs btn-danger"><i class="fa fa-retweet" aria-hidden="true"></i> Trả lại</a>';
                }
                $action[] = '<a style="background: rgba(131,1,7,0.98)" href="' . url('admin/booking/cancel/received/' . $b->id) . '" onclick="if(!confirm(\'Bạn chắc chắn muốn hủy đơn hàng này không ?\')) return false;" class="btn btn-xs btn-primary"><i class="fa fa-remove"></i> Hủy</a></div>';
                $action[] = '<div style="display: inline-flex; margin-top: 5px;)"><a href="' . url('admin/booking/print/send/' . $b->id) . '" class="btn btn-xs btn-info"><i class="fa fa-print" aria-hidden="true"></i> in hóa đơn</a>';
                $action[] = '<a style="background: rgba(159,158,25,0.81)" href="' . url('admin/booking/update/received/' . $b->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i> Sửa</a>';
                $action[] = '<a style="background: rgba(73,4,70,0.87)" href="' . url('admin/booking/delete/received/' . $b->id) . '" onclick="if(!confirm(\'Bạn chắc chắn muốn xóa đơn hàng này không ?\')) return false;" class="btn btn-xs btn-primary"><i class="fa fa-trash"></i> Xóa</a></div>';
                return implode(' ', $action);
            })
            ->editColumn('status', function ($b) {
                return 'Chưa giao';
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
                $delivery = BookDelivery::where('book_id', $b->id)->where('category', 'receive')->where('status', 'completed')
                    ->orWhere('book_id', $b->id)->where('category', 'send')->where('status', 'processing')
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
            ->editColumn('user_create', function ($b) {
                return $b->sender->name . ' ' . $b->sender->phone_number;
            })
            ->editColumn('send_created_at', function ($b) {
                $delivery = BookDelivery::where('book_id', $b->id)->where('category', 'send')->where('status', 'processing')->where('sending_active', 1)->select('created_at as send_created_at')->first();
                return !empty($delivery) ? $delivery->send_created_at : '';
            })
            ->rawColumns(['report_image', 'action'])
            ->make(true);
    }

    public function delayBooking()
    {
        $booking = Booking::where('sub_status', 'delay');
        if (Auth::user()->role == 'collaborators') {
            /*$scope = Collaborator::where('user_id', Auth::user()->id)->pluck('agency_id');
            $booking = $booking->whereIn('current_agency', $scope);*/
            $booking = $booking->whereIn('send_ward_id', $this->getBookingScope());
        }
        // $booking = $booking->orderBy('id', 'desc')->get();
        return datatables()->of($booking)
            ->addColumn('action', function ($b) {
                $action = [];
                $action[] = '<div style="display: inline-flex"><a href="' . url('admin/booking/continued/delay/' . $b->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-motorcycle"></i> Tiếp tục</a>';
                $action[] = '<a style="background: rgba(131,1,7,0.98)" href="' . url('admin/booking/cancel/delay/' . $b->id) . '" onclick="if(!confirm(\'Bạn chắc chắn muốn hủy đơn hàng này không ?\')) return false;" class="btn btn-xs btn-primary"><i class="fa fa-remove"></i> Hủy</a>';
                $action[] = '<a style="background: rgba(159,158,25,0.81)" href="' . url('admin/booking/update/delay/' . $b->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i> Sửa</a>';
                $action[] = '<a style="background: rgba(73,4,70,0.87)" href="' . url('admin/booking/delete/delay/' . $b->id) . '" onclick="if(!confirm(\'Bạn chắc chắn muốn xóa đơn hàng này không ?\')) return false;" class="btn btn-xs btn-primary"><i class="fa fa-trash"></i> Xóa</a></div>';

                return implode(' ', $action);
            })
            ->addColumn('shipper', function ($b) {
                $data = '';
                if ($b->status != null) {
                    if ($b->status != 'new') {
                        $data = @BookDelivery::where('book_id', $b->id)->where('status', 'delay')->first()->shipper_name;
                    }
                }
                return $data;
            })
            ->editColumn('status', function ($b) {
                return 'Tạm hoãn';
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
            ->addColumn('shipper_note', function ($b) {
                $note = '';
                $data = BookDelivery::where('book_id', $b->id)->where('status', 'delay')->first();
                if ($data != null) {
                    $note = $data->note != null ? $data->note : $note;
                }
                return $note;
            })
            ->addColumn('report_image', function ($b) {
                $delivery = BookDelivery::where('book_id', $b->id)->where('category', 'receive')->where('status', 'delay')
                    ->orWhere('book_id', $b->id)->where('category', 'return')->where('status', 'delay')
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
            ->editColumn('user_create', function ($b) {
                return $b->sender->name . ' ' . $b->sender->phone_number;
            })
            ->rawColumns(['report_image', 'action'])
            ->make(true);
    }

    public function cancelBooking()
    {
        $booking = Booking::where('status', 'cancel');
        if (Auth::user()->role == 'collaborators') {
            $booking = $booking->whereIn('send_ward_id', $this->getBookingScope());
        }
        // $booking = $booking->orderBy('id', 'desc')->get();
        return datatables()->of($booking)
            /*->addColumn('action', function ($b) {
                $action = [];
                if (Auth::user()->role == 'collaborators') {
                        $action[] = '<a href="' . url('admin/booking/continued/' . $b->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-motorcycle"></i> Tiếp tục</a>';
                }
                return implode(' ', $action);
            })*/
            ->editColumn('status', function ($b) {
                return 'Đã hủy';
            })
            ->addColumn('report_image', function ($b) {
                $delivery = BookDelivery::where('book_id', $b->id)->where('status', 'cancel')
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
            ->addColumn('shipper_note', function ($b) {
                $note = '';
                $data = BookDelivery::where('book_id', $b->id)->where('status', 'cancel')->first();
                if ($data != null) {
                    $note = $data->note != null ? $data->note : $note;
                }
                return $note;
            })
            ->editColumn('user_create', function ($b) {
                return $b->sender->name . ' ' . $b->sender->phone_number;
            })
            ->rawColumns(['report_image', 'action'])
            ->make(true);
    }

    public function sentBooking()
    {
        $booking = Booking::where('status', 'completed');
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
                                return $b->COD_status == 'finish' ? '<img src="' . asset('/img/corect.png') . '" width="30px"></img>' :
                                    '<img onclick="changeCODStatus(' . $b->id . ')" src="' . asset('/img/incorect.png') . '" width="30px"></img>';
                            } else {
                                return $b->COD_status == 'finish' ? '<img src="' . asset('/img/corect.png') . '" width="30px"></img>' :
                                    '<img src="' . asset('/img/incorect.png') . '" width="30px"></img>';
                            }
                        } else {
                            if (in_array($b->last_agency, $result)) {
                                return $b->COD_status == 'finish' ? '<img src="' . asset('/img/corect.png') . '" width="30px"></img>' :
                                    '<img onclick="changeCODStatus(' . $b->id . ')" src="' . asset('/img/incorect.png') . '" width="30px"></img>';
                            } else {
                                return $b->COD_status == 'finish' ? '<img src="' . asset('/img/corect.png') . '" width="30px"></img>' :
                                    '<img src="' . asset('/img/incorect.png') . '" width="30px"></img>';
                            }
                        }
                    } else {
                        return $b->COD_status == 'finish' ? '<img src="' . asset('/img/corect.png') . '" width="30px"></img>' :
                            '<img onclick="changeCODStatus(' . $b->id . ')" src="' . asset('/img/incorect.png') . '" width="30px"></img>';
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
            ->editColumn('user_create', function ($b) {
                return $b->sender->name . ' ' . $b->sender->phone_number;
            })
            ->rawColumns(['COD_status', 'report_image'])
            ->make(true);
    }

    public function denyBooking()
    {
        // $booking = DB::table('bookings')->join('book_deliveries', 'bookings.id', '=', 'book_deliveries.book_id')
        //     ->where('bookings.status', 'return')->where('bookings.sub_status', '!=', 'delay')
        //     ->where('book_deliveries.category', '=', 'return')
        //     ->orWhere('bookings.status', 'move')->where('bookings.sub_status', 'move_return')
        //     ->where('book_deliveries.category', '=', 'move')
        //     ->where('book_deliveries.status', 'completed')->where('book_deliveries.last_move', 1);

        $booking = DB::table('bookings')->join('book_deliveries', 'bookings.id', '=', 'book_deliveries.book_id')->where(function($q){
            $q->where(function($q1){
                $q1->where('bookings.status', 'return')->where('bookings.sub_status', '!=', 'delay')
                    ->where('book_deliveries.category', '=', 'return');
            });
            $q->orWhere(function($q2){
                $q2->where('bookings.status', 'move')->where('bookings.sub_status', 'move_return')
                    ->where('book_deliveries.category', '=', 'move')
                    ->where('book_deliveries.status', 'completed')->where('book_deliveries.last_move', 1);
            });
        });
        if (Auth::user()->role == 'collaborators') {
            $scope = Collaborator::where('user_id', Auth::user()->id)->pluck('agency_id');
            // $booking = DB::table('bookings')->join('book_deliveries', 'bookings.id', '=', 'book_deliveries.book_id')
            //     ->whereIn('bookings.current_agency', $scope)
            //     ->where('bookings.status', 'return')->where('bookings.sub_status', '!=', 'delay')
            //     ->where('book_deliveries.category', '=', 'return')
            //     ->orWhere('bookings.status', 'move')->where('bookings.sub_status', 'move_return')
            //     ->whereIn('bookings.current_agency', $scope)->where('book_deliveries.category', '=', 'move')
            //     ->where('book_deliveries.status', 'completed')->where('book_deliveries.last_move', 1);

            $booking = DB::table('bookings')->join('book_deliveries', 'bookings.id', '=', 'book_deliveries.book_id')->where(function($q){
                $q->where(function($q1){
                    $q1->whereIn('bookings.current_agency', $scope)
                        ->where('bookings.status', 'return')->where('bookings.sub_status', '!=', 'delay')
                        ->where('book_deliveries.category', '=', 'return');
                });
                $q->where(function($q2){
                    $q2->where('bookings.status', 'move')->where('bookings.sub_status', 'move_return')
                        ->whereIn('bookings.current_agency', $scope)->where('book_deliveries.category', '=', 'move')
                        ->where('book_deliveries.status', 'completed')->where('book_deliveries.last_move', 1);
                });
            });
        }
        $booking = $booking->join('users', 'bookings.sender_id', '=', 'users.id');
        $booking = $booking->select('bookings.*', 'book_deliveries.status', 'book_deliveries.user_id', 'book_deliveries.id', 'book_deliveries.book_id', 'users.phone_number as create_phone_number', 'users.name as create_name');
        return datatables()->of($booking)
            ->addColumn('action', function ($b) {
                $action = [];
                if ($b->sub_status == 'none' && $b->status == 'deny' || $b->sub_status == 'move_return' && $b->status == 'move') {
                    $action[] = '<div style="display: inline-flex"><a style="float: left" href="' . url('admin/booking/deny_assign/' . $b->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-motorcycle"></i> Phân công</a>';
//                    if($b->current_agency != $b->first_agency){
                    $action[] = '<a style="float: left" href="#" onclick="moveBooking(' . $b->id . ')" class="btn btn-xs btn-warning"><i class="fa fa-share"></i> Chuyển kho</a>';
                    $action[] = '<a style="background: rgba(131,1,7,0.98)" href="' . url('admin/booking/cancel/return/' . $b->id) . '" onclick="if(!confirm(\'Bạn chắc chắn muốn hủy đơn hàng này không ?\')) return false;" class="btn btn-xs btn-primary"><i class="fa fa-remove"></i> Hủy</a></div>';

                    $action[] = '<div style="display: inline-flex; margin-top: 5px"><a style="background: rgba(131,72,22,0.98)" href="' . url('admin/booking/continued/deny/' . $b->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-motorcycle"></i> Tiếp tục giao</a>';
                    $action[] = '<a style="background: rgba(159,158,25,0.81)" href="' . url('admin/booking/update/return/' . $b->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i> Sửa</a>';
                    $action[] = '<a style="background: rgba(73,4,70,0.87)" href="' . url('admin/booking/delete/return/' . $b->id) . '" onclick="if(!confirm(\'Bạn chắc chắn muốn xóa đơn hàng này không ?\')) return false;" class="btn btn-xs btn-primary"><i class="fa fa-trash"></i> Xóa</a></div>';
                    $action[] = '<a style="float: left; margin-top: 5px;" href="' . url('admin/booking/move-to-receive/' . $b->id) . '" class="btn btn-xs btn-default"><i class="fa fa-share"></i> Chuyển qua ĐH chưa giao</a>';
//                    }
                } else if ($b->sub_status == 'deny') {
                    $action[] = '<a disabled="" href="#" class="btn btn-xs btn-danger"><i class="fa fa-times" aria-hidden="true"></i> Từ chối</a></div>';
                } else if ($b->status == 'processing') {
                    $action[] = '<div style="display: inline-flex"><a href="' . url('admin/booking/reassign/deny/' . $b->id) . '" class="btn btn-xs btn-success"><i class="fa fa-motorcycle"></i> Phân công lại</a>';
                    $action[] = '<a href="#" data-toggle="popover" data-placement="top" data-html="true" title="<p><b>Đã thanh toán</b></p>" 
                        data-content="<div style=\'display: inline-flex\'><input id=\'owe\' style=\'transform: scale(1.5);\' onclick=\'changeUrl()\' type=\'checkbox\'> 
                        <a id=\'owe_submit\' href=' . url('admin/booking/completed/return/' . $b->book_id) . ' class=\'btn btn-xs btn-success\' style=\'background: green; margin-left: 10px\'>
                        <i class=\'fa fa-check\'></i> Thực hiện</a></div>" class="btn btn-xs btn-success" style="background: green">Đã trả lại</a>';
                    $action[] = '<a style="background: pink" href="' . url('admin/booking/delay/return/' . $b->book_id) . '" class="btn btn-xs btn-warning"><i class="fa fa-clock-o" aria-hidden="true"></i> Delay</a>';
                    $action[] = '<a style="background: rgba(131,1,7,0.98)" href="' . url('admin/booking/cancel/return/' . $b->id) . '" onclick="if(!confirm(\'Bạn chắc chắn muốn hủy đơn hàng này không ?\')) return false;" class="btn btn-xs btn-primary"><i class="fa fa-remove"></i> Hủy</a></div>';

                    $action[] = '<div style="display: inline-flex; margin-top: 5px"><a href="' . url('admin/booking/deny/' . @$b->book_id) . '" class="btn btn-xs btn-danger"><i class="fa fa-times" aria-hidden="true"></i> Từ chối</a>';
                    $action[] = '<a style="background: rgba(159,158,25,0.81)" href="' . url('admin/booking/update/return/' . $b->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i> Sửa</a>';
                    $action[] = '<a style="background: rgba(73,4,70,0.87)" href="' . url('admin/booking/delete/return/' . $b->id) . '" onclick="if(!confirm(\'Bạn chắc chắn muốn xóa đơn hàng này không ?\')) return false;" " class="btn btn-xs btn-primary"><i class="fa fa-trash"></i> Xóa</a></div>';
                } else if ($b->status == 'completed') {
                    $action[] = '<a disabled="" href="#" class="btn btn-xs btn-success" style="background: green" ><i class="fa fa-check"></i> Hoàn thành</a>';
                }
                return implode(' ', $action);
            })
            ->addColumn('shipper', function ($b) {
                return $b->user_id != 0 ? @BookDelivery::where('id', $b->id)->where('category', 'return')->first()->shipper_name : '';
            })
            ->editColumn('status', function ($b) {
                $status = '';
                if ($b->sub_status == 'none' && $b->status == 'deny') {
                    $status = 'Chờ xử lý';
                } else if ($b->sub_status == 'deny') {
                    $status = 'Từ chối trả lại';
                } else if ($b->status == 'processing') {
                    $status = 'Đang trả lại';
                } else if ($b->status == 'completed') {
                    $status = 'Đã trả lại';
                }
                return $status;
            })
            ->editColumn('payment_type', function ($b) {
                return $b->payment_type == 1 ? 'Người gửi trả cước' : 'Người nhận trả cước';
            })
            ->editColumn('current_agency', function ($b) {
                return $b->current_agency != null ? Agency::find($b->current_agency)->name : '';
            })
            ->editColumn('first_agency', function ($b) {
                return $b->first_agency != null ? Agency::find($b->first_agency)->name : '';
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
            ->addColumn('shipper_note', function ($b) {
                $note = '';
                $data = BookDelivery::where('book_id', $b->id)->where('category', 'return')->first();
                if ($data != null) {
                    $note = $data->note != null ? $data->note : $note;
                }
                return $note;
            })
            ->addColumn('report_image', function ($b) {
                $delivery = BookDelivery::where('book_id', $b->id)->where('category', 'return')
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
            ->editColumn('user_create', function ($b) {
                return $b->create_name . ' ' . $b->create_phone_number;
            })
            ->rawColumns(['report_image', 'action'])
            ->make(true);
    }

    public function moveBooking()
    {
        // $booking = DB::table('bookings')->join('book_deliveries', 'bookings.id', '=', 'book_deliveries.book_id')
        //     ->where('bookings.status', 'move')->where('book_deliveries.category', '=', 'move');
        if (Auth::user()->role == 'collaborators') {
            $user_id = Auth::user()->id;
            $scope = Collaborator::where('user_id', $user_id)->pluck('agency_id');
            // $booking = $booking->whereIn('book_deliveries.current_agency', $scope)
            //     ->orWhere('book_deliveries.category', 'move')->whereIn('book_deliveries.last_agency', $scope);

            // $booking = DB::table('bookings')->join('book_deliveries', 'bookings.id', '=', 'book_deliveries.book_id')
            // ->where(function($q) use ($scope){
            //     $q->where(function($q1){
            //         $q1->where('bookings.status', 'move')->where('book_deliveries.category', '=', 'move');
            //     });
            //     $q->orWhere(function($q2) use ($scope){
            //         $q2->whereIn('book_deliveries.current_agency', $scope)
            //             ->orWhere('book_deliveries.category', 'move')->whereIn('book_deliveries.last_agency', $scope);
            //     });
            // });
            $booking = Booking::where(function($q) use ($scope){
                $q->where(function($q1){
                    $q1->where('bookings.status', 'move');
                    $q1->whereHas('deliveries', function($query){
                        $query->where('category', 'move')->where('status', 'processing');
                    });
                });
                $q->orWhere(function($q2) use ($scope){
                    $q2->whereHas('deliveries', function($query) use ($scope){
                        $query->whereIn('current_agency', $scope);
                    });
                    $q2->orWhere(function($q) use ($scope){
                        $q->whereHas('deliveries', function($query) use ($scope){
                            $query->where('category', 'move')->where('status', 'processing');
                            $query->whereIn('last_agency', $scope);
                        });
                    });
                });
            });
        } else {
            // $booking = $booking->where('book_deliveries.status', '=', 'processing');

            //$booking = DB::table('bookings')->join('book_deliveries', 'bookings.id', '=', 'book_deliveries.book_id')
            $booking = Booking::where(function($q){
                $q->where(function($q1){
                    $q1->where('bookings.status', 'move')
                        // ->where('book_deliveries.category', '=', 'move');
                        ->whereHas('deliveries', function ($query) {
                            $query->where('category', 'move');
                        });
                });
                $q->where(function($q2){
                    // $q2->where('book_deliveries.status', '=', 'processing');
                    $q2->whereHas('deliveries', function ($query) {
                        $query->where('status', 'processing');
                    });
                });
            });
        }
        return datatables()->of($booking)
            ->addColumn('action', function ($b) {
                $action = [];
                $bookDelivery = BookDelivery::where('book_id', $b->id)->where('category', 'move')->first();
                if (Auth::user()->role == 'collaborators') {
                    $user_id = Auth::user()->id;
                    $scope = Collaborator::where('user_id', $user_id)->pluck('agency_id')->toArray();
                    if (in_array($bookDelivery->last_agency, $scope) && $bookDelivery->status == 'processing') {
                        $action[] = '<a style="float: left" href="' . url('admin/booking/moved/' . $bookDelivery->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-check-circle"></i> Đã nhận hàng</a>';
                    }
                } else if (Auth::user()->role == 'admin') {
                    $action[] = '<a style="float: left" href="' . url('admin/booking/moved/' . $bookDelivery->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-check-circle"></i> Đã nhận hàng</a>';
                }
                return implode(' ', $action);
            })
            ->editColumn('status', function ($b) {
                $status = '';
                $user_id = Auth::user()->id;
                $scope = Collaborator::where('user_id', $user_id)->pluck('agency_id')->toArray();
                $bookDelivery = BookDelivery::where('book_id', $b->id)->where('category', 'move')->first();
                if (in_array($bookDelivery->last_agency, $scope) && $bookDelivery->status == 'processing') {
                    $status = 'Đang chuyển đến';
                } else if (in_array($bookDelivery->last_agency, $scope) && $bookDelivery->status == 'completed') {
                    $status = 'Đã chuyển đến';
                } else if (in_array($bookDelivery->current_agency, $scope) && $bookDelivery->status == 'processing') {
                    $status = 'Đang chuyển đi';
                } else {
                    $status = 'Đã chuyển đi';
                }
                return $status;
            })
            ->editColumn('current_agency', function ($b) {
                $current_agency = '';
                if ($b->current_agency) {
                    $current_agency = Agency::find($b->current_agency)->name;
                }
                return $current_agency;
            })
            ->editColumn('last_agency', function ($b) {
                $last_agency = '';
                $bookDelivery = BookDelivery::where('book_id', $b->id)->where('category', 'move')->first();
                if ($bookDelivery->last_agency) {
                    $last_agency = Agency::find($bookDelivery->last_agency)->name;
                }
                return $last_agency;
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
            ->make(true);
    }

    public function changeCODStatus($id)
    {
        DB::beginTransaction();
        try {
            $delivery = Booking::find($id);
            $delivery->COD_status = 'finish';
            $delivery->payment_date = Carbon::now();
            $delivery->save();
            $user = User::where('id', $delivery->sender_id)->first();
            if (!empty($user)) {
                $user->total_COD = $user->total_COD - $delivery->COD;
                $user->save();
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
        return $delivery;
    }

    public function removeBookingByTime(Request $req)
    {
        DB::beginTransaction();
        try {
            $booking = Booking::where('status', $req->status)->whereDate('updated_at', '>=', $req->date_from)->whereDate('updated_at', '<=', $req->date_to);
            if (Auth::user()->role == 'collaborators') {
                $booking = $booking->whereIn('send_ward_id', $this->getBookingScope());
            }
            if ($req->status == 'completed') {
                $booking = $booking->where(function ($query) {
                    $query->where('COD', 0)->orWhere('COD_status', 'finish');
                });
            }
            if ($req->phone) {
                $booking = $booking->where(function ($query1) use ($req) {
                    $query1->where('send_phone', $req->phone)->orWhere('receive_phone', $req->phone);
                });
            }
            $booking->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
        }
        return 'success';
    }

    public function checkAgency(Request $request)
    {
        if ($request->type == 'deny') {
            $delivery = BookDelivery::find($request->id);
            $id = $delivery->book_id;
            $booking = Booking::find($id);
            $data = @$booking->first_agency;
            $current = @$booking->current_agency;
        } else {
            $id = $request->id;
            $booking = Booking::find($id);
            $ward_id = $booking->receive_ward_id;
            $current = @$booking->current_agency;
            $data = @ManagementWardScope::where('ward_id', $ward_id)->first()->agency_id;

        }
        return ['id' => $id, 'agency' => $data != null ? $data : -1, 'current' => $current != null ? $current : -1];
    }

    public function getQuickAssignReceive() {
        $booking = Booking::where(function($q){
            $q->where(function($q1){
                $q1->where('status', 'sending')->where('sub_status', 'none')->whereHas('deliveries', function ($query) {
                    $query->where('sending_active', 1);
                });
            });
            $q->orWhere(function($q2){
                $q2->where('status', 'move')->where('sub_status', 'none')->whereHas('deliveries', function ($query) {
                    $query->where('category', 'move')->where('status', 'completed');
                });
            });
        });
        $booking = $booking->with(['firstAgencies', 'currentAgencies', 'deliveries','shipperSender']);

        // đơn hàng chưa phân công
        if (request()->type_assign == 'no_assign') {
            $booking = $booking->whereHas('deliveries', function($query){
                $query->where('status', 'completed')->where('category', 'receive')->where('sending_active', 1);
            });
        }

        if (request()->province_id != -1) {
            $booking = $booking->where('receive_province_id', request()->province_id);
        }
        if (request()->district_id != -1) {
            $booking = $booking->where('receive_district_id', request()->district_id);
        }
        if (request()->ward_id != -1) {
            $booking = $booking->where('receive_ward_id', request()->ward_id);
        }
        if (!empty(request()->phone)) {
            $booking = $booking->where('receive_phone', 'LIKE', '%' . request()->phone . '%');
        }

        $booking = $booking->orderBy('created_at', 'DESC')->get();
        $shippers = User::where('role', 'shipper')
                        ->where('status', 'active')
                        ->where('delete_status', 0)
                        ->get();
        $data = [
            'province_id' => request()->province_id,
            'district_id' => request()->district_id,
            'ward_id' => request()->ward_id,
            'phone' => request()->phone,
            'books' => $booking,
            'shippers' => $shippers
        ];

        return $data;
    }

    public function postQuickAssignReceive() {
        $data['shipper_id'] = '';
        $data['book_ids'] = [];
        foreach (request()->inputs as $key => $value) {
            if ($value['name'] == 'shipper') {
                $data['shipper_id'] = $value['value'];
            } elseif ($value['name'] == 'books') {
                $data['book_ids'][] = $value['value'];
            }
        }

        if (empty($data['shipper_id'])) {
            return json_encode(['status' => 'Hãy chọn 1 shipper!']);
        }
        
        if (count($data['book_ids']) > 0) {
            if (request()->type_assign == 'no_assign') {
                foreach ($data['book_ids'] as $id) {
                    $booking = Booking::find($id);
                    $check = BookDelivery::where('book_id', $id)->where('category', 'send')->first();
                    if ($check == null) {
                        DB::beginTransaction();
                        try {
                            $booking->update(['status' => 'sending']);
                            BookDelivery::insert([
                                'user_id' => $data['shipper_id'],
                                'send_address' => $booking->send_full_address,
                                'receive_address' => $booking->receive_full_address,
                                'book_id' => $id,
                                'category' => 'send',
                                'sending_active' => 1,
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                            ]);
                            BookDelivery::where('book_id', $id)->where('category', '!=', 'send')->update(['sending_active' => 0]);
                            DB::commit();
                        } catch (\Exception $e) {
                            DB::rollBack();
                            return $e;
                        }
                    }
                }
            } else {
                DB::beginTransaction();
                try {
                    BookDelivery::whereIn('book_id', $data['book_ids'])
                                    ->where('sending_active', 1)
                                    ->update(['user_id' => $data['shipper_id']]);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    return $e;
                }
            }
        } else {
            return json_encode(['status' => 'Chọn ít nhất 1 đơn hàng!']);
        }

        return json_encode(['status' => 'success']);
    }

    public function getQuickAssignNew() {
        if (Auth::user()->role == 'collaborators') {
            // $booking = Booking::where('status', 'new')->where('sub_status', 'none')->whereIn('send_ward_id', $this->getBookingScope())
            //     ->orwhere('status', 'taking')->where('sub_status', 'none')->whereIn('send_ward_id', $this->getBookingScope());
            $booking = Booking::where(function($q){
                $q->where(function($q1){
                    $q1->where('status', 'new')->where('sub_status', 'none')->whereIn('send_ward_id', $this->getBookingScope());
                });
                $q->orWhere(function($q2){
                    $q2->where('status', 'taking')->where('sub_status', 'none')->whereIn('send_ward_id', $this->getBookingScope());        
                });
            });
        } else {
            $booking = Booking::whereIn('status', ['new', 'taking'])->where('sub_status', 'none');
        }

        // đơn hàng chưa phân công
        if (request()->type_assign == 'no_assign') {
            $booking = $booking->where('status', 'new');
        } else {
            $booking = $booking->where('status', 'taking');
        }

        if (request()->province_id != -1) {
            $booking = $booking->where('send_province_id', request()->province_id);
        }
        if (request()->district_id != -1) {
            $booking = $booking->where('send_district_id', request()->district_id);
        }
        if (request()->ward_id != -1) {
            $booking = $booking->where('send_ward_id', request()->ward_id);
        }
        if (!empty(request()->phone)) {
            $booking = $booking->where('send_phone', 'LIKE', '%' . request()->phone . '%');
        }

        $booking = $booking->with(['firstAgencies', 'currentAgencies', 'deliveries','shipperRecivcier'])
                        ->orderBy('created_at', 'DESC')
                        ->get();
        $shippers = User::where('role', 'shipper')
                        ->where('status', 'active')
                        ->where('delete_status', 0)
                        ->get();
        $data = [
            'province_id' => request()->province_id,
            'district_id' => request()->district_id,
            'ward_id' => request()->ward_id,
            'phone' => request()->phone,
            'books' => $booking,
            'shippers' => $shippers
        ];

        return $data;
    }

    public function postQuickAssignNew() {
        $data['shipper_id'] = '';
        $data['book_ids'] = [];
        foreach (request()->inputs as $key => $value) {
            if ($value['name'] == 'shipper') {
                $data['shipper_id'] = $value['value'];
            } elseif ($value['name'] == 'books') {
                $data['book_ids'][] = $value['value'];
            }
        }

        if (empty($data['shipper_id'])) {
            return json_encode(['status' => 'Hãy chọn 1 shipper!']);
        }
        
        if (count($data['book_ids']) > 0) {
            if (request()->type_assign == 'no_assign') {
                foreach ($data['book_ids'] as $id) {
                    $booking = Booking::find($id);
                    $check = BookDelivery::where('book_id', $id)->where('category', 'receive')->first();
                    if (empty($check)) {
                        DB::beginTransaction();
                        try {
                            $booking->update(['status' => 'taking']);
                            BookDelivery::insert([
                                'user_id' => $data['shipper_id'],
                                'send_address' => $booking->send_full_address,
                                'receive_address' => $booking->receive_full_address,
                                'book_id' => $id,
                                'category' => 'receive',
                                'sending_active' => 1,
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                            ]);
                            DB::commit();
                        } catch (\Exception $e) {
                            DB::rollBack();
                            return $e;
                        }
                    }
                }
            } else {
                DB::beginTransaction();
                try {
                    BookDelivery::whereIn('book_id', $data['book_ids'])
                                    ->where('sending_active', 1)
                                    ->update(['user_id' => $data['shipper_id']]);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    return $e;
                }
            }
        } else {
            return json_encode(['status' => 'Chọn ít nhất 1 đơn hàng!']);
        }

        return json_encode(['status' => 'success']);
    }
}
