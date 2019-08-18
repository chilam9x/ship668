<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Requests\ShipperRequest;
use App\Mail\ShipperMail;
use App\Models\Agency;
use App\Models\ManagementWardScope;
use App\Models\ManagementProvinceScope;
use App\Models\ManagementScope;
use App\Models\Shipper;
use App\Models\ShipperRevenue;
use App\Models\User;
use App\Models\ShipperLocation;
use App\Models\District;
use App\Models\Ward;
use App\Models\Province;
use App\Models\BookDelivery;
use App\Models\Booking;
use function dd;
use function redirect;
use \Validator, Excel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ShipperController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected $breadcrumb = ['Quản lý thành viên', 'shipper'];

    public function exportBooking(Request $request)
    {
        $path = 'donhang_giao.xlsx';
        $booking = DB::table('bookings')->join('book_deliveries', 'bookings.id', '=', 'book_deliveries.book_id');
        if ($request->status == 'send') {
            $path = 'donhang_giao.xlsx';
            $booking = $booking->where('bookings.status', 'sending')->where('bookings.sub_status', 'none')->where('book_deliveries.sending_active', 1)
                ->whereDate('book_deliveries.updated_at', $request->date_assign)->where('book_deliveries.user_id', $request->shipper)
                ->where('book_deliveries.category', 'send')->where('book_deliveries.status', 'processing')
                ->orWhere('bookings.status', 'taking')->where('bookings.sub_status', 'none')->where('book_deliveries.sending_active', 1)
                ->whereDate('book_deliveries.updated_at', $request->date_assign)->where('book_deliveries.user_id', $request->shipper)
                ->where('book_deliveries.category', 'send')->where('book_deliveries.status', 'processing');
        }
        if ($request->status == 'receive') {
            $path = 'donhang_nhan.xlsx';
            $booking = $booking->where('bookings.status', 'taking')->where('bookings.sub_status', 'none')->whereDate('book_deliveries.updated_at', $request->date_assign)
                ->where('book_deliveries.user_id', $request->shipper)->where('book_deliveries.category', 'receive')->where('book_deliveries.status', 'processing');
        }
        if ($request->status == 'deny') {
            $path = 'donhang_tralai.xlsx';
            $booking = $booking->where('bookings.status', 'return')->where('bookings.sub_status', 'none')->where('book_deliveries.user_id', $request->shipper)
                ->whereDate('book_deliveries.updated_at', $request->date_assign)->where('book_deliveries.category', 'return')->where('book_deliveries.status', 'processing');
        }
        if ($request->status == 'all') {
            $path = 'donhang_tatca.xlsx';
            $booking = $booking->where('bookings.status', '!=', 'cancel')
                        ->where('book_deliveries.user_id', $request->shipper)
                        ->whereDate('book_deliveries.updated_at', '>=', $request->date_assign)
                        ->whereDate('book_deliveries.updated_at', '<=', $request->date_assign_to);
        }
        $booking = $booking->select('bookings.*', 'book_deliveries.created_at as time_assign', 'book_deliveries.status as book_deliveries_status', 'book_deliveries.category', 'book_deliveries.completed_at as book_deliveries_compeleted', 'book_deliveries.sending_active')->orderBy('time_assign', 'desc')->get();
        $result = [];
        $num = 1;
        foreach ($booking as $b) {
            $total = 0;
            $user_name = '';
            $user_phone = '';
            $user_full_address = '';

            /* $payment_type = '';
            if ($b->payment_type == 1) {
                $payment_type = 'Người gửi trả cước';
            } else if ($b->payment_type == 2) {
                $payment_type = 'Người nhận trả cước';
            }*/

            if ($b->category == 'receive') {
                $user_name = $b->send_name;
                $user_phone = $b->send_phone;
                $user_full_address = $b->send_full_address;
                $total = $b->payment_type == 1 ? $b->price + $b->incurred : 0;
            } else if ($b->category == 'send') {
                $user_name = $b->receive_name;
                $user_phone = $b->receive_phone;
                $user_full_address = $b->receive_full_address;
                $total = $b->payment_type == 1 ? $b->COD : $b->price + $b->incurred + $b->COD;
            } else if ($b->category == 'return') {
                $user_name = $b->send_name;
                $user_phone = $b->send_phone;
                $user_full_address = $b->send_full_address;
                $total = $b->payment_type == 1 ? $b->incurred : $b->price + $b->incurred;
            }
            $data['Stt'] = $num;
            $data['uuid'] = $b->uuid;
            $data['user_name'] = $user_name;
            $data['user_phone'] = $user_phone;
            $data['user_full_address'] = $user_full_address;
            $data['weight'] = $b->weight;
            $data['total'] = $total;

            $status = '';
            if ($b->status == 'completed') {
                if ($b->book_deliveries_status == 'completed' && $b->category == 'receive') {
                    $status = 'Đã lấy';
                } else {
                    $status = 'Đã giao';
                }
            } elseif ($b->status == 'return') {
                if ($b->book_deliveries_status == 'deny' && $b->category == 'return') {
                    $status = 'Từ chối trả hàng';
                } elseif ($b->book_deliveries_status == 'processing' && $b->category == 'return') {
                    $status = 'Đang trả';
                } elseif ($b->book_deliveries_status == 'completed' && $b->category == 'return') {
                    $status = 'Đã trả';
                } else {
                    $status = 'Đã lấy';
                }
            } elseif ($b->status == 'sending') {
                if ($b->book_deliveries_status == 'processing' && $b->category == 'send' && $b->sending_active == 1) {
                    $status = 'Đang giao';
                } else {
                    $status = 'Đã lấy';
                }
            } elseif ($b->status == 'taking') {
                $status = 'Đi lấy hàng';
            }
            if ($request->status == 'all') {
                $data = array();
                $data['Stt'] = $num;
                $data['uuid'] = $b->uuid;
                $data['time_assign'] = $b->time_assign;
                $data['completed_at'] = $b->book_deliveries_compeleted;
                $data['date_change_status'] = $b->updated_at;
                $data['status'] = $status;
                $data['cod'] = ($b->category == 'receive') ? '' : $b->COD;
                $data['price'] = ($b->owe == 1) ? $b->price : '';
                $data['payment_type'] = ($b->payment_type == 1) ? 'Người gửi trả cước' : 'Người nhận trả cước';
                $data['receive_phone'] = $b->receive_phone;
                $data['receive_full_address'] = $b->receive_full_address;
            }

            $result[] = $data;
            $num++;
        }
        $file_path = public_path('/excel_temp/shipper/' . $path);
        Excel::load($file_path, function ($reader) use ($result, $request) {
            $reader->sheet('Sheet1', function ($sheet) use ($result, $request) {
                $cellDate = ($request->status == 'all') ? 'K1' : 'G1';
                $sheet->cell($cellDate, function ($cell) use ($request) {
                    if ($request->status == 'all') {
                        $cell->setValue('Ngày: ' . $request->date_assign . ' - ' . $request->date_assign_to);   
                    } else {
                        $cell->setValue('Ngày: ' . $request->date_assign);   
                    }
                });
                $sheet->fromArray($result, null, 'A6', true, false);
            });

        })->setFilename('DanhSachDonHangCuaShipper')->export('xlsx');
    }

    public function paidBooking(Request $request)
    {
        DB::beginTransaction();
        try {
            $revenue = ShipperRevenue::where('shipper_id', $request->user_id)->first();
            if ($revenue != null){
                if ($request->type == 'COD_paid'){
                    $revenue->COD_paid += $request->paid;
                }
                if ($request->type == 'price_paid'){
                    $revenue->price_paid += $request->paid;
                }
                $revenue->save();
            }
            DB::commit();
            return redirect()->back()->with('success', 'Thanh toán hoàn tất');
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
        }
    }

    public function maps()
    {
        return view('admin.elements.users.shipper.maps', ['active' => 'shipper', 'breadcrumb' => $this->breadcrumb]);
    }

    public function index()
    {
        $shipperOnline = User::leftJoin('shipper_locations as SL', 'users.id', '=', 'SL.user_id')
                    ->where('role', 'shipper')
                    ->where('status', 'active')
                    ->where('delete_status', 0)
                    ->where('SL.online', 1)
                    ->select('SL.user_id', 'SL.online', 'users.name', 'users.username')
                    ->get();
        $shippers = User::where('role', 'shipper')
                    ->where('status', 'active')
                    ->where('delete_status', 0)
                    ->get();
        return view('admin.elements.users.shipper.index', ['active' => 'shipper', 'breadcrumb' => $this->breadcrumb, 'shipperOnline' => $shipperOnline, 'shippers' => $shippers]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (Auth::user()->role == 'admin') {
            $agency = Agency::all();
        } else {
            $agency = Agency::with('collaborators')->whereHas('collaborators', function ($query) {
                $query->where('user_id', Auth::user()->id);
            })->get();
        }
        return view('admin.elements.users.shipper.add', ['agency' => $agency, 'active' => 'shipper', 'breadcrumb' => $this->breadcrumb]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(ShipperRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = new User();
            $data->name = $request->name;
            $data->password = Hash::make($request->password);
            $data->email = $request->email;
            $data->birth_day = $request->birth_day;
            $data->province_id = $request->province_id;
            $data->district_id = $request->district_id;
            $data->ward_id = $request->ward_id;
            $data->home_number = $request->home_number;
            $data->phone_number = $request->phone_number;
            $data->id_number = $request->id_number;
            $data->role = 'shipper';
            $data->bank_account = $request->bank_account;
            $data->bank_account_number = $request->bank_account_number;
            $data->bank_name = $request->bank_name;
            $data->bank_branch = $request->bank_branch;
            $data->work_type = $request->work_type;
            if ($request->hasFile('avatar')) {
                $file = $request->avatar;
                $filename = date('Ymd-His-') . $file->getFilename() . '.' . $file->extension();
                $filePath = 'img/avatar/';
                $movePath = public_path($filePath);
                $file->move($movePath, $filename);
                $data->avatar = $filePath . $filename;
            }
            $data->save();
            $uuid = User::find($data->id);
            $uuid->uuid = str_random(5) . $uuid->id;
            $uuid->save();
            Shipper::insert([
                'user_id' => $data->id,
                'agency_id' => $request->agency,
                'created_at' => $data['created_at'],
                'updated_at' => $data['created_at']
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
        }
//        Mail::to($request->email)->send(new ShipperMail($request->name, $uuid->uuid, $request->password));
        return redirect(url('admin/shippers'))->with('success', 'Thêm mới Shipper thành công');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $shipper = User::find($id);
        $agency = Agency::orderBy('name', 'asc')->get();
        return view('admin.elements.users.shipper.details', ['agency' => $agency, 'user' => $shipper, 'active' => 'shipper', 'breadcrumb' => $this->breadcrumb]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $shipper = User::find($id);
        $selected = Shipper::where('user_id', $id)->first();
        if (Auth::user()->role == 'admin') {
            $agency = Agency::all();
        } else {
            $agency = Agency::with('collaborators')->whereHas('collaborators', function ($query) {
                $query->where('user_id', Auth::user()->id);
            })->get();
        }
        return view('admin.elements.users.shipper.add', ['user' => $shipper, 'agency' => $agency, 'selected' => @$selected->agency_id, 'active' => 'shipper', 'breadcrumb' => $this->breadcrumb]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(ShipperRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = User::find($id);
            $data->name = $request->name;
            if ($data->uuid == null) {
                $data->uuid = str_random(5) . $data->id;
            }
            if ($request->password != null) {
                $data->password = Hash::make($request->password);
            }
            $data->email = $request->email;
            $data->birth_day = $request->birth_day;
            $data->province_id = $request->province_id;
            $data->district_id = $request->district_id;
            $data->ward_id = $request->ward_id;
            $data->home_number = $request->home_number;
            $data->phone_number = $request->phone_number;
            $data->id_number = $request->id_number;
            $data->bank_account = $request->bank_account;
            $data->bank_account_number = $request->bank_account_number;
            $data->bank_name = $request->bank_name;
            $data->bank_branch = $request->bank_branch;
            $data->work_type = $request->work_type;
            if ($request->hasFile('avatar')) {
                $file = $request->avatar;
                $filename = date('Ymd-His-') . $file->getFilename() . '.' . $file->extension();
                $filePath = 'img/avatar/';
                $movePath = public_path($filePath);
                $file->move($movePath, $filename);
                $data->avatar = $filePath . $filename;
            }
            $data->save();
            Shipper::where('user_id', $id)->delete();
            Shipper::insert([
                'user_id' => $data->id,
                'agency_id' => $request->agency,
                'created_at' => $data['created_at'],
                'updated_at' => $data['created_at']
            ]);

            /*if ($request->password != null) {
                Mail::to($request->email)->send(new ShipperMail($request->name, $data->uuid, $request->password, true));
            }*/
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
        }
        return redirect(url('admin/shippers'))->with('success', 'Chỉnh sửa Shipper thành công');
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
            $shipper = User::find($id);
            if ($shipper->avatar != null) {
                if (file_exists($shipper->avatar)) {
                    @unlink('file_path');
                }
            }
            $shipper_check = Shipper::where('user_id', $id)->first();
            if (!empty($shipper_check)) {
                Shipper::where('user_id', $id)->delete();
            }
            $shipper->delete_status = 1;
            $shipper->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
        return redirect(url('admin/shippers'))->with('delete', 'Xóa Shipper thành công');
    }

    public function getDetailTotalCOD($shipperId) {
        $type = isset(request()->type) ? request()->type : 'cod';
        $name = isset(request()->name) ? request()->name : '';
        $db = DB::table('book_deliveries as BD')
                ->join('bookings', 'BD.book_id', '=', 'bookings.id')
                ->where('BD.user_id', $shipperId);
        if ($type == 'cod') {
            $db = $db->where('BD.sending_active', 1)
                    ->where('BD.category', 'send')
                    ->where('BD.status', 'completed');
        } else {
            // whereIn('BD.category', ['receive', 'return', 'send'])
            $db = $db->where('BD.status', 'completed')
                    ->whereIn('BD.category', ['receive', 'return', 'send']);
        }
        $bookUsers = $db->select('BD.book_id', 'BD.user_id', 'BD.sending_active', 'BD.status as bd_status', 'BD.category', 'bookings.*')->get();
        if ($type == 'ship') {
            $tmpBookUsers = [];
            foreach ($bookUsers->toArray() as $item) {
                if ($item->category == 'send' && $item->payment_type == 2) {
                    $tmpBookUsers[] = $item;
                } else {
                    if ($item->category == 'receive' ||  $item->category == 'return') {
                        if ($item->owe == 1) {
                            $tmpBookUsers[] = $item;
                        }
                    }
                }
            }
            $bookUsers = $tmpBookUsers;
        }
        return view('admin.elements.users.shipper.detail_total_cod', array('active' => 'shipper', 'breadcrumb' => $this->breadcrumb, 'bookUsers' => $bookUsers, 'name' => $name, 'type' => $type));
    }

    public function refreshBook($shipperId) {
        // đi lấy
        $receive = BookDelivery::where('user_id', $shipperId)
                            ->where('status', 'processing')
                            ->where('category', 'receive')
                            ->where('sending_active', 1);
        $receiveBookIds = $receive->pluck('book_id');
        $receiveDeliveries = $receive->delete();
        Booking::whereIn('id', $receiveBookIds)->update(['status' => 'new']);

        // đi giao
        $send = BookDelivery::where('user_id', $shipperId)
                            ->where('status', 'processing')
                            ->where('category', 'send')
                            ->where('sending_active', 1);
        $sendBookIds = $send->pluck('book_id');
        Booking::whereIn('id', $sendBookIds)->update(['status' => 'sending']);
        $sendDeliveries = $send->delete();
        BookDelivery::whereIn('book_id', $sendBookIds)
            ->where('status', 'completed')
            ->where('category', 'receive')
            ->where('sending_active', 0)
            ->update(['sending_active' => 1]);

        // đi trả   
        $return_processing = BookDelivery::where('user_id', $shipperId)
            ->where('status', 'processing')
            ->where('category', 'return')
            ->where('sending_active', 1);
        $return_processing->update([
            'user_id' => 0,
            'status' => 'deny',
            'category' => 'return',
            'sending_active' => 1
        ]);
//        $return_processing = BookDelivery::where('user_id', $shipperId)
//                            ->where('status', 'processing')
//                            ->where('category', 'return')
//                            ->where('sending_active', 1);
        
        //$return_processing->delete();
        
        $re_send = BookDelivery::where('user_id', $shipperId)
            ->where('status', 'deny')
            ->where('category', 're-send')
            ->where('sending_active', 1);

//        $sub_book_request = Booking::where(['sub_status' => 'request-return'])
//            ->whereIn('id', function($q) use ($shipperId) {
//            $q->from('book_deliveries');
//            $q->select('book_id');
//            $q->where(['user_id' => $shipperId]);
//        });

//        $sub_book_request->update([
//            'sub_status' => 'none'
//        ]);
 
        // $re_send->delete();
        
        $re_send->update([
            'user_id'=>0,
            'status' => 'deny',
            'category' => 're-send',
            'sending_active' => 1
        ]);
            
        return redirect()->back()->with('success', 'Làm mới phân công cho shipper thành công');
    }

    public function manageScope($shipperId) {
        $shipper = User::find($shipperId);
        if (request()->isMethod('post')) {
            DB::beginTransaction();
            try {
                $shipper->auto_receive = isset(request()->auto_receive) ? request()->auto_receive : 0;
                $shipper->auto_send = isset(request()->auto_send) ? request()->auto_send : 0;
                $shipper->save();

                if (request()->type == 2) {
                    ManagementProvinceScope::where('shipper_id', $shipperId)->delete();
                    ManagementScope::where('shipper_id', $shipperId)->delete();
                    ManagementWardScope::where('shipper_id', $shipperId)->delete();
                    if (!empty(request()->province_scope) && count(request()->province_scope) > 0) {
                        foreach (request()->province_scope as $s) {
                            ManagementProvinceScope::insert([
                                'shipper_id' => $shipperId,
                                'province_id' => $s,
                                'agency_id' => 0
                            ]);
                        }

                        $arrDistrictId = [];
                        if (!isset(request()->scope) || request()->scope == null) {
                            $district = District::whereIn('provinceId', request()->province_scope)->select('id', 'provinceId')->get();
                            foreach ($district as $s) {
                                ManagementScope::insert([
                                    'shipper_id' => $shipperId,
                                    'district_id' => $s->id,
                                    'agency_id' => 0,
                                    'province_id' => $s->provinceId
                                ]);
                                $arrDistrictId[] = $s->id;
                            }
                        } else {
                            $district = District::whereIn('id', request()->scope)->select('id', 'provinceId')->get();
                            foreach ($district as $s) {
                                ManagementScope::insert([
                                    'shipper_id' => $shipperId,
                                    'district_id' => $s->id,
                                    'agency_id' => 0,
                                    'province_id' => $s->provinceId
                                ]);
                                $arrDistrictId[] = $s->id;
                            }
                        }
                        
                        if (!isset(request()->ward_scope) || request()->ward_scope == null) {
                            $ward = Ward::whereIn('districtId', $arrDistrictId)->select('id', 'districtId', 'provinceId')->get();
                            foreach ($ward as $w) {
                                ManagementWardScope::insert([
                                    'shipper_id' => $shipperId,
                                    'ward_id' => $w->id,
                                    'agency_id' => 0,
                                    'district_id' => $w->districtId,
                                    'province_id' => $w->provinceId
                                ]);
                            }
                        } else {
                            $ward = Ward::whereIn('id', request()->ward_scope)->select('id', 'districtId', 'provinceId')->get();
                            foreach ($ward as $ws) {
                                ManagementWardScope::insert([
                                    'shipper_id' => $shipperId,
                                    'ward_id' => $ws->id,
                                    'agency_id' => 0,
                                    'district_id' => $ws->districtId,
                                    'province_id' => $ws->provinceId
                                ]);
                            }
                        }
                    }
                }
                
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return $e;
            }
            return redirect(url('admin/shippers'))->with('success', 'Cập nhật khu vực thành công');
        }
        $scope = ManagementScope::where('shipper_id', $shipperId)->pluck('district_id');
        $ward_scope = ManagementWardScope::where('shipper_id', $shipperId)->pluck('ward_id');
        $province_scope = ManagementProvinceScope::where('shipper_id', $shipperId)->pluck('province_id');
        $districtScopes = District::whereIn('id', $scope)->get();
        $wardScopes = Ward::whereIn('id', $ward_scope)->get();
        $provinceScopes = Province::whereIn('id', $province_scope)->get();
        return view('admin.elements.users.shipper.manage_scope', [
            'selected_col' => [], 
            'active' => 'shipper', 
            'breadcrumb' => $this->breadcrumb,
            'shipperId' => $shipperId,
            'scope' => $scope,
            'ward_scope' => $ward_scope,
            'districtScopes' => $districtScopes,
            'wardScopes' => $wardScopes,
            'provinceScopes' => $provinceScopes,
            'shipper' => $shipper
        ]);
    }
}
