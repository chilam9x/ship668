<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Models\Booking;
use function dd;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth, Excel;
use function in_array;

class ExportBookingController extends Controller
{
    protected function getBookingScope()
    {
        $user_id = Auth::user()->id;
        $scope = Collaborator::where('user_id', $user_id)->pluck('agency_id');
        $ward = ManagementWardScope::whereIn('agency_id', $scope)->pluck('ward_id');
        return $ward;
    }

    //function export booking buy time from time to
    public function exportBookingByTime(Request $req)
    {
        $booking = Booking::where('status', $req->status);
        if ($req->status == 'completed') {
            $booking = $booking->whereBetween('completed_at', [$req->date_from, $req->date_to]);
        } else {
            $booking = $booking->whereDate('updated_at', '>=', $req->date_from)->whereDate('updated_at', '<=', $req->date_to);
        }
        
        if (Auth::user()->role == 'collaborators') {
            $booking = $booking->whereIn('send_ward_id', $this->getBookingScope());
        }
        if ($req->phone) {
            $booking = $booking->where(function ($query) use ($req) {
                $query->where('send_phone', $req->phone)->orWhere('receive_phone', $req->phone);
            });
        }
        $booking = $booking->orderBy('id', 'asc')->get();
        $result = [];
        $num = 1;
        foreach ($booking as $b) {
            $data['Stt'] = $num;
            $data['uuid'] = $b->uuid;
            $data['name'] = $b->name != null ? $b->name : '';
            $data['send_name'] = $b->send_name;
            $data['send_phone'] = $b->send_phone;
            $data['send_full_address'] = $b->send_full_address;
            $data['receive_name'] = $b->receive_name;
            $data['receive_phone'] = $b->receive_phone;
            $data['receive_full_address'] = $b->receive_full_address;
            $data['weight'] = $b->weight;
            $data['price'] = $b->price + $b->incurred;
            $data['COD'] = $b->COD;
            if ($b->transport_type == 1) {
                $data['transport_type'] = 'Giao chuẩn';
            } else if ($b->transport_type == 2) {
                $data['transport_type'] = 'Giao tiết kiệm';
            } else if ($b->transport_type == 3) {
                $data['transport_type'] = 'Giao siêu tốc';
            } else if ($b->transport_type == 4) {
                $data['transport_type'] = 'Giao thu COD';
            }
            if ($b->receive_type == 1) {
                $data['receive_type'] = 'Nhận hàng tại nhà';
            } else if ($b->receive_type == 2) {
                $data['receive_type'] = 'Nhận hàng tại bưu cục';
            }
            if ($b->payment_type == 1) {
                $data['payment_type'] = 'Người gửi trả cước';
            } else if ($b->payment_type == 2) {
                $data['payment_type'] = 'Người nhận trả getAdcước';
            }
            $data['other_note'] = $b->other_note;
            $data['COD_status'] = $b->COD > 0 ? $b->COD_status : '';
            $data['payment_date'] = $b->payment_date != null ? $b->payment_date : '';
            $result[] = $data;
            $num++;
        }
        $file_path = public_path('excel_temp/sent_booking.xlsx');
        Excel::load($file_path, function ($reader) use ($result, $req) {
            $reader->skipRows(3);

            $reader->sheet('list_booking', function ($sheet) use ($result, $req) {
                $sheet->cell('D1', function ($cell) use ($req) {
                    $cell->setValue($req->date_from);
                });
                $sheet->cell('D2', function ($cell) use ($req) {
                    $cell->setValue($req->date_to);
                });
                $sheet->fromArray($result, null, 'B6', false, false);
            });

        })->setFilename($req->status == 'cancel' ? 'CancelBooking' : 'CompletedBooking')->export('xlsx');
    }

    public function exportBookingAdvance(Request $req)
    {
        $file_name = 'Don-hang-moi';
        $req->province_id=50;
        $booking = Booking::whereIn('status', $req->status)->whereIn('sub_status', $req->sub_status)->whereDate('updated_at', '>=', $req->date_from)->whereDate('updated_at', '<=', $req->date_to);
        if (Auth::user()->role == 'collaborators') {
            $booking = $booking->whereIn('send_ward_id', $this->getBookingScope());
        }

        if (in_array('return', $req->status)) {
            $booking = $booking->with(['deliveries' => function ($query) {
                $query->select('book_id', 'category', 'status')->where('category', 'return');
            }]);
        }
        if ($req->phone) {
            $booking = $booking->where(function ($query) use ($req) {
                $query->where('send_phone', $req->phone)->orWhere('receive_phone', $req->phone);
            });
        }
        if ($req->province_id != -1) {
            $booking = $booking->where(function ($query) use ($req) {
                $query->where('send_province_id', $req->province_id)->orWhere('receive_province_id', $req->province_id);
            });
        }
        if ($req->district_id != -1) {
            $booking = $booking->where(function ($query) use ($req) {
                $query->where('send_district_id', $req->district_id)->orWhere('receive_district_id', $req->district_id);
            });
        }
        if ($req->ward_id != -1) {
            $booking = $booking->where(function ($query) use ($req) {
                $query->where('send_ward_id', $req->ward_id)->orWhere('receive_ward_id', $req->ward_id);
            });
        }
        $booking = $booking->orderBy('status', 'asc')->get();
        $result = [];
        $num = 1;
        foreach ($booking as $b) {
            $status = '';
            if ($b->sub_status == 'delay') {
                $file_name = 'Don-hang-delay';
                $status = 'Delay';
            } else {
                switch ($b->status) {
                    case 'new' :
                        $status = 'Mới';
                        break;
                    case 'taking' :
                        $status = 'Đang lấy';
                        break;
                    case 'sending' :
                        $file_name = 'Don-hang-di-giao';
                        $status = 'Đang giao';
                        break;
                    case 'return' :
                        $file_name = 'Don-hang-tra-lai';
                        if ($b->sub_status == 'deny') {
                            $status = 'Từ chối trả lại';
                        }else{
                            if (isset($b->deliveries)) {
                                if (!empty($b->deliveries)) {
                                    if ($b->deliveries[0]->status == 'deny') {
                                        $status = 'Chờ xử lý';
                                    } else if ($b->deliveries[0]->status == 'processing') {
                                        $status = 'Đang trả lại';
                                    } else if ($b->deliveries[0]->status == 'completed') {
                                        $status = 'Đã trả lại';
                                    }
                                }
                            } else {
                                $status = 'Trả lại';
                            }
                        }
                        break;
                    case 'move' :
                        $file_name = 'Don-hang-chuyen-kho';
                        $status = 'Chuyển kho';
                        break;
                    default:
                        $status = '';
                }
            }
            $data['Stt'] = $num;
            $data['uuid'] = $b->uuid;
            $data['name'] = $b->name != null ? $b->name : '';
            $data['send_name'] = $b->send_name;
            $data['send_phone'] = $b->send_phone;
            $data['send_full_address'] = $b->send_full_address;
            $data['receive_name'] = $b->receive_name;
            $data['receive_phone'] = $b->receive_phone;
            $data['receive_full_address'] = $b->receive_full_address;
            $data['weight'] = $b->weight;
            $data['price'] = $b->price + $b->incurred;
            $data['COD'] = $b->COD;
            if ($b->transport_type == 1) {
                $data['transport_type'] = 'Giao chuẩn';
            } else if ($b->transport_type == 2) {
                $data['transport_type'] = 'Giao tiết kiệm';
            } else if ($b->transport_type == 3) {
                $data['transport_type'] = 'Giao siêu tốc';
            } else if ($b->transport_type == 4) {
                $data['transport_type'] = 'Giao thu COD';
            }
            if ($b->receive_type == 1) {
                $data['receive_type'] = 'Nhận hàng tại nhà';
            } else if ($b->receive_type == 2) {
                $data['receive_type'] = 'Nhận hàng tại bưu cục';
            }
            if ($b->payment_type == 1) {
                $data['payment_type'] = 'Người gửi trả cước';
            } else if ($b->payment_type == 2) {
                $data['payment_type'] = 'Người nhận trả getAdcước';
            }
            $data['other_note'] = $b->other_note;
            $data['COD_status'] = $b->COD > 0 ? $b->COD_status : '';
            $data['payment_date'] = $b->payment_date != null ? $b->payment_date : '';
            $data['status'] = $status;
            $result[] = $data;
            $num++;
        }
        $file_path = public_path('excel_temp/booking/booking.xlsx');
        Excel::load($file_path, function ($reader) use ($result, $req) {
            $reader->skipRows(3);
            $reader->sheet('list_booking', function ($sheet) use ($result, $req) {
                $sheet->cell('D1', function ($cell) use ($req) {
                    $cell->setValue($req->date_from);
                });
                $sheet->cell('D2', function ($cell) use ($req) {
                    $cell->setValue($req->date_to);
                });
                $sheet->fromArray($result, null, 'B6', false, false);
            });

        })->setFilename($file_name)->export('xlsx');
    }

}
