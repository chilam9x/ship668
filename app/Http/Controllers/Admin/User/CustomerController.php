<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Requests\CustomerRequest;
use App\Models\Booking;
use App\Models\Collaborator;
use App\Models\DeliveryAddress;
use App\Models\District;
use App\Models\Province;
use App\Models\User;
use App\Models\Ward;
use function dd;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use function redirect;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Wallet;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin', ['except' => ['getDelivery', 'deleteDelivery', 'index', 'create', 'store', 'getOwe', 'paidAll']]);
    }

    protected $breadcrumb = ['Quản lý đối tác & khách hàng', 'khách hàng'];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getDelivery($id)
    {
        return view('admin.elements.users.customer.delivery-address.index', ['id' => $id, 'active' => 'customer', 'breadcrumb' => $this->breadcrumb]);
    }

    public function deleteDelivery($id)
    {
        DB::beginTransaction();
        try {
            $delivery = DeliveryAddress::find($id);
            if ($delivery->default == 1) {
                return redirect()->back()->with('delete', 'Không thể xóa địa chỉ mặc định');
            }
            $delivery->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
        return redirect()->back()->with('success', 'Xóa địa chỉ thành công');
    }

    public function getOwe($id)
    {
        $booking = Booking::where('sender_id', $id)->where('owe', 0)->where(function ($query) {
            $query->where('status', 'return')->where('sub_status', 'none')->whereHas('deliveries', function($d) {
                $d->where('category', 'return')->where('status', 'completed');
            })
                ->orWhere('status', 'completed')->whereHas('deliveries', function ($d1){
                    $d1->where('category', 'send');
                });
        });
        if (Auth::user()->role == 'collaborators') {
            $user_id = Auth::user()->id;
            $scope = Collaborator::where('user_id', $user_id)->pluck('agency_id');
            $booking = $booking->whereIn('last_agency', $scope);
        }
        $data = $booking->count();
        $time_from = date('Y-m') . '-01';
        return view('admin.elements.users.customer.owe', ['id' => $id, 'active' => 'customer', 'count' => $data, 'breadcrumb' => $this->breadcrumb, 'time_from' => $time_from]);
    }

    public function paidAll($id)
    {

        $booking = Booking::where('sender_id', $id)->where('owe', 0)->where(function ($query) {
            $query->where('status', 'return')->where('sub_status', 'none')->whereHas('deliveries', function($d) {
                $d->where('category', 'return')->where('status', 'completed');
            })
                ->orWhere('status', 'completed')->whereHas('deliveries', function ($d1){
                    $d1->where('category', 'send');
                });
            });
        if (Auth::user()->role == 'collaborators') {
            $user_id = Auth::user()->id;
            $scope = Collaborator::where('user_id', $user_id)->pluck('agency_id');
            $booking = $booking->whereIn('last_agency', $scope);
        }
        $booking->update(['owe' => 1, 'paid' => (DB::raw('price + incurred'))]);
        return redirect()->back()->with('success', 'thanh toán tất cả đơn hàng nợ thành công');
    }

    public function index()
    {
        \Log::info(env('FCM_SERVER_KEY'));
        \Log::info(env('FCM_SENDER_ID'));
        return view('admin.elements.users.customer.index', ['active' => 'customer', 'breadcrumb' => $this->breadcrumb]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.elements.users.customer.add', ['active' => 'customer', 'breadcrumb' => $this->breadcrumb]);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(CustomerRequest $request)
    {
        /*$validator = $this->createdValidate($request, 'customer');
        if (!empty($validator->errors()->first())) {
            return redirect('admin/customers/create')
                ->withErrors($validator)
                ->withInput();
        }*/
        DB::beginTransaction();
        try {
            $data = new User();
            $data->name = $request->name;
            $data->email = $request->email;
            $data->birth_day = $request->birth_day;
            $data->province_id = $request->province_id;
            $data->district_id = $request->district_id;
            $data->ward_id = $request->ward_id;
            $data->home_number = $request->home_number;
            $data->phone_number = $request->phone_number;
            $data->id_number = $request->id_number;
            $data->role = 'customer';
            $data->bank_account = $request->bank_account;
            $data->bank_account_number = $request->bank_account_number;
            $data->bank_name = $request->bank_name;
            $data->bank_branch = $request->bank_branch;
            if ($request->hasFile('avatar')) {
                $file = $request->avatar;
                $filename = date('Ymd-His-') . $file->getFilename() . '.' . $file->extension();
                $filePath = 'img/avatar/';
                $movePath = public_path($filePath);
                $file->move($movePath, $filename);
                $data->avatar = $filePath . $filename;
            }
            $data->is_vip = isset($request->is_vip) ? $request->is_vip : 0;
            $data->is_advance_money = isset($request->is_advance_money) ? $request->is_advance_money : 0;
            $data->save();
            $delivery = new DeliveryAddress();
            $delivery->user_id = $data->id;
            $delivery->province_id = $request->province_id;
            $delivery->district_id = $request->district_id;
            $delivery->ward_id = $request->ward_id;
            $delivery->home_number = $request->home_number;
            $delivery->default = 1;
            $delivery->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
        return redirect(url('admin/customers'))->with('success', 'Thêm mới khách hàng thành công');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $customer = User::find($id);
        $result = [];
        $delivery = DeliveryAddress::where('user_id', $id)->get();
        if (isset($delivery)) {
            foreach ($delivery as $d) {
                $province_name = Province::find($d->province_id)->name;
                $district_name = District::find($d->district_id)->name;
                $ward_name = Ward::find($d->ward_id)->name;
                $data = [
                    'name' => $d->home_number . ', ' . $ward_name . ', ' . $district_name . ', ' . $province_name,
                    'id' => $d->id];
                $result[] = $data;
            }
        }
        $selected = DeliveryAddress::where('user_id', $id)->where('default', 1)->first();
        return view('admin.elements.users.customer.add', ['customer' => $customer, 'delivery' => $result,
            'selected' => @$selected->id, 'active' => 'customer', 'breadcrumb' => $this->breadcrumb]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(CustomerRequest $request, $id)
    {
        /* $validator = $this->updatedValidate($request, $id, 'customer');
         if (!empty($validator->errors()->first())) {
             return redirect('admin/customers/'.$id.'/edit')
                 ->withErrors($validator)
                 ->withInput();
         }*/
        DB::beginTransaction();
        try {
            $delivery = DeliveryAddress::find($request->delivery_address);
            $delivery->default = 1;
            $delivery->save();
            $data = User::find($id);
            $data->name = $request->name;
            $data->email = $request->email;
            $data->birth_day = $request->birth_day;
            $data->province_id = $delivery->province_id;
            $data->district_id = $delivery->district_id;
            $data->ward_id = $delivery->ward_id;
            $data->home_number = $delivery->home_number;
            $data->phone_number = $request->phone_number;
            $data->id_number = $request->id_number;
            $data->bank_account = $request->bank_account;
            $data->bank_account_number = $request->bank_account_number;
            $data->bank_name = $request->bank_name;
            $data->bank_branch = $request->bank_branch;
            $data->password_code = $request->password_code;
            if ($request->hasFile('avatar')) {
                $file = $request->avatar;
                $filename = date('Ymd-His-') . $file->getFilename() . '.' . $file->extension();
                $filePath = 'img/avatar/';
                $movePath = public_path($filePath);
                $file->move($movePath, $filename);
                $data->avatar = $filePath . $filename;
            }
            $data->is_vip = isset($request->is_vip) ? $request->is_vip : 0;
            $data->is_advance_money = isset($request->is_advance_money) ? $request->is_advance_money : 0;
            $data->save();
            DeliveryAddress::where('id', '!=', $request->delivery_address)->update(['default' => 0]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
        return redirect(url('admin/customers'))->with('success', 'Cập nhật khách hàng thành công');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $user = User::find($id);
            if ($user->avatar != null) {
                if (file_exists($user->avatar)) {
                    @unlink('file_path');
                }
            }
            $delivery = DeliveryAddress::where('user_id', $id)->first();
            if (!empty($col_check)) {
                DeliveryAddress::where('user_id', $id)->delete();
            }
            $user->delete_status = 1;
            $user->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
        return redirect(url('admin/customers'))->with('delete', 'Xóa khách hàng thành công');
    }

    public function exportPrintOwe(Request $req, $id) {
        $booking = Booking::where('sender_id', $id)->where('owe', 0)
                ->whereDate('created_at', '>=', $req->input('date_from'))
                ->whereDate('created_at', '<=', $req->input('date_to'))
                ->where(function ($query) {
                $query->where('status', 'return')->where('sub_status', 'none')->whereHas('deliveries', function($d) {
                    $d->where('category', 'return')->where('status', 'completed');
                })
                ->orWhere('status', 'completed')->whereHas('deliveries', function ($d1){
                    $d1->where('category', 'send');
                });
        });
        if (!empty($req->input('phone'))) {
            $booking = $booking->where('send_phone', $req->input('phone'))->orWhere('receive_phone', $req->input('phone'));
        }
        $booking = $booking->orderBy('id', 'asc')->get();

        if ($req->has('export')) {
            $result = [];
            foreach ($booking as $index => $b) {
                $data['Stt'] = $index + 1;
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
                    $data['transport_type'] = 'Giao thường';
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
                $data['payment_date'] = $b->payment_date != null ? date('d/m/Y', strtotime($b->payment_date)) : '';
                $result[] = $data;
            }
            $file_path = public_path('excel_temp/customer_owe.xlsx');
            return Excel::load($file_path, function($reader) use($result, $req) {
                $reader->skipRows(3);
                $reader->sheet('list_booking', function ($sheet) use ($result, $req) {
                    $sheet->cell('D1', function ($cell) use ($req) {
                        $cell->setValue(date('d/m/Y', strtotime($req->date_from)));
                    });
                    $sheet->cell('D2', function ($cell) use ($req) {
                        $cell->setValue(date('d/m/Y', strtotime($req->date_to)));
                    });
                    $sheet->fromArray($result, null, 'B6', false, false);
                });

            })->setFilename('Chi_Tiet_No_Khach_Hang')->export('xls');
        }

        if ($req->has('print')) {
            return view('admin.elements.users.customer.print', array('booking' => $booking, 'date_from' => $req->date_from, 'date_to' => $req->date_to, 'phone' => $req->phone));
        }
    }

    public function exportBooking(Request $request) {
        $path = 'donhang.xlsx';
        $booking = Booking::where('bookings.sender_id', $request->customer_id)
                    ->whereDate('bookings.created_at', '>=', $request->date_assign)
                    ->whereDate('bookings.created_at', '<=', $request->date_assign_to);
        if ($request->status != 'all') {
            $booking = $booking->where('status', $request->status);
        }
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
        Excel::load($file_path, function ($reader) use ($result, $request) {
            $reader->sheet('Sheet1', function ($sheet) use ($result, $request) {
                $sheet->cell('A1', function ($cell) use ($request) {
                    $titleExcel = 'DANH SÁCH ĐƠN HÀNG';
                    if ($request->status == 'new') {
                        $titleExcel .= ' MỚI';
                    } else if ($request->status == 'return') {
                        $titleExcel .= ' TRẢ LẠI';
                    } else if ($request->status == 'delay') {
                        $titleExcel .= ' DELAY';
                    } else if ($request->status == 'cancel') {
                        $titleExcel .= ' HỦY';
                    } else if ($request->status == 'taking') {
                        $titleExcel .= ' ĐANG ĐI LẤY';
                    } else if ($request->status == 'sending') {
                        $titleExcel .= ' ĐANG GIAO HÀNG';
                    } else if ($request->status == 'all') {
                        $titleExcel = 'DANH SÁCH TẤT CẢ ĐƠN HÀNG';
                    } else {
                        $titleExcel .= ' ĐÃ GIAO HÀNG';
                    }
                    $cell->setValue($titleExcel);
                });
                $sheet->cell('R1', function ($cell) use ($request) {
                    $cell->setValue('Ngày: ' . $request->date_assign . ' - ' . $request->date_assign_to);
                });
                $sheet->fromArray($result, null, 'A6', true, false);
            });

        })->setFilename('DanhSachDonHangCuaKhachHang')->export('xlsx');
    }

    public function withDrawal($customerId) {
        $wallet = 0;
        $cod = Booking::where('sender_id', $customerId)->where('status', 'completed')->where('COD', '>', 0)->where('COD_status', 'pending');
        if(Auth::user()->role == 'collaborators') {
            $scope = Collaborator::where('user_id', Auth::user()->id)->pluck('agency_id');
            $cod = $cod->whereIn('last_agency', $scope);
        }
        $codBookingIds = $cod->get()->pluck('id');
        $cod = $cod->sum('COD');

        // $booking = Booking::where('sender_id', $customerId)->where('owe', 0)->where(function ($query) {
        //     $query->where('status', 'return')->where('sub_status', 'none')->whereHas('deliveries', function($d) {
        //         $d->where('category', 'return')->where('status', 'completed');
        //     })
        //         ->orWhere('status', 'completed')->whereHas('deliveries', function ($d1){
        //             $d1->where('category', 'send');
        //         });
        // });
        // không tính tiền đơn hàng trả lại
        $booking = Booking::where('sender_id', $customerId)->where('owe', 0)->where(function ($query) {
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
            $customer = User::find($customerId);
            $wallet = new Wallet;
            $wallet->customer_id = $customerId;
            $wallet->price = $walletPrice;
            $wallet->customer_name = $customer->name;
            $wallet->customer_phone_number = $customer->phone_number;
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
}
