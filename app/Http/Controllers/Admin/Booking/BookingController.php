<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Requests\AssignRequest;
use App\Http\Requests\CreateBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Models\BookDelivery;
use App\Models\Booking;
use App\Models\Agency;
use App\Models\ManagementScope;
use App\Models\Setting;
use App\Models\Shipper;
use App\Models\District;
use App\Models\Province;
use App\Models\ShipperRevenue;
use App\Models\User;
use App\Models\Ward;
use App\Models\Notification;
use App\Models\NotificationUser;
use function back;
use Book;
use Carbon\Carbon;
use function dd;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Collaborator;
use App\Models\ManagementWardScope;
use Auth, Excel;
use Illuminate\Support\Facades\DB;
use function in_array;
use function is;
use function redirect;
use function url;
use function view;
use App\Helpers\NotificationHelper;
use App\Jobs\NotificationJob;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected $breadcrumb = ['Quản lý đơn hàng'];

    protected function getBookingScope()
    {
        $user_id = Auth::user()->id;
        $scope = Collaborator::where('user_id', $user_id)->pluck('agency_id');
        $ward = ManagementWardScope::whereIn('agency_id', $scope)->pluck('ward_id');
        return $ward;
    }

    protected function getAddress($province, $district, $ward, $home_number)
    {
        $province_name = Province::find($province)->name;
        $district_name = District::find($district)->name;
        $ward_name = Ward::find($ward)->name;;
        return $home_number . ', ' . $ward_name . ', ' . $district_name . '. ' . $province_name;
    }

    protected function getProperties()
    {
        $user_id = Auth::user()->id;
        $scope = Collaborator::where('user_id', $user_id)->pluck('agency_id');
        $shipper = User::Where('role', 'shipper')->where('delete_status', 0)->where('status', 'active');
        if (Auth::user()->role == 'collaborators') {
            $shipper = $shipper->with('shipper')->whereHas('shipper', function ($query) use ($scope) {
                $query->whereIn('agency_id', $scope);
            });
        }
        return $shipper->get();
    }

    public function printBooking($type, $id)
    {
        $booking = Booking::find($id);
        $agency = null;
        switch ($booking->status) {
            case 'taking':
                $shipper_id = BookDelivery::where('book_id', $booking->id)->where('category', 'receive')->where('status', 'processing')->first()->user_id;
                $agency_id = Shipper::where('user_id', $shipper_id)->first();
                if (!empty($agency_id)) {
                    $agency = Agency::find($agency_id->agency_id);
                }
                break;
            case 'sending':
                $check = BookDelivery::where('book_id', $booking->id)->where('category', 'send')->where('status', 'processing')->first();
                if (!empty(($check))) {
                    $shipper_id = $check->user_id;
                } else {
                    $shipper_id = BookDelivery::where('book_id', $booking->id)->where('category', 'receive')->where('status', 'completed')->first()->user_id;
                }
                $agency_id = Shipper::where('user_id', $shipper_id)->first();
                if (!empty($agency_id)) {
                    $agency = Agency::find($agency_id->agency_id);
                }
                break;
            case 'completed':
                $shipper_id = BookDelivery::where('book_id', $booking->id)->where('category', 'send')->where('status', 'completed')->first()->user_id;
                $agency_id = Shipper::where('user_id', $shipper_id)->first();
                if (!empty($agency_id)) {
                    $agency = Agency::find($agency_id->agency_id);
                }
                break;
            default:
                $agency = Agency::where('ward_id', $booking->send_ward_id)->first();
        }
        $user = null;
        if ($agency != null) {
            $collaborator = Collaborator::where('agency_id', $agency->id)->with('users')->first();
            if (!empty($collaborator)) {
                $user = $collaborator->users;
            }
        }
        return view('admin.elements.booking.print', ['booking' => $booking, 'agency' => $agency, 'collaborator' => $user, 'type' => $type]);
    }

    //create booking
    public function createBooking()
    {
        $this->breadcrumb[] = 'tạo đơn hàng thủ công';
        return view('admin.elements.booking.create.add', ['active' => 'create', 'breadcrumb' => $this->breadcrumb]);
    }

    public function postCreateBooking(CreateBookingRequest $req)
    {
        DB::beginTransaction();
        try {
            $sender_id = null;
            $receiver_id = null;
            $sender_check = User::where('phone_number', $req->phone_number_fr)->where('role', 'customer')->where('delete_status', 0)->first();
            $receiver_check = User::where('phone_number', $req->phone_number_to)->where('role', 'customer')->where('delete_status', 0)->first();
            if (!empty($sender_check)) {
                $sender_id = $sender_check->id;
            } else {
                $check_sender_delete = User::where('phone_number', $req->phone_number_fr)->where('role', 'customer')->where('delete_status', 1)->first();
                if (!empty($check_sender_delete)) {
                    $check_sender_delete->delete_status = 0;
                    $check_sender_delete->save();
                    $sender_id = $check_sender_delete->id;
                } else {
                    $user = new User();
                    $user->phone_number = $req->phone_number_fr;
                    $user->save();
                    $sender_id = $user->id;
                }
            }
            if (!empty($receiver_check)) {
                $receiver_id = $receiver_check->id;
            } else {
                $user = new User();
                $user->phone_number = $req->phone_number_to;
                $user->save();
                $receiver_id = $user->id;
            }
            $booking = new Booking();
            $booking->sender_id = $sender_id;
            $booking->receiver_id = $receiver_id;
            $booking->name = $req->name;
            $booking->send_name = $req->name_fr;
            $booking->send_phone = $req->phone_number_fr;
            $booking->send_province_id = $req->province_id_fr;
            $booking->send_district_id = $req->district_id_fr;
            $booking->send_ward_id = $req->ward_id_fr;
            $booking->send_homenumber = $req->home_number_fr;
            $booking->send_full_address = $this->getAddress($req->province_id_fr, $req->district_id_fr, $req->ward_id_fr, $req->home_number_fr);
            $booking->receive_name = $req->name_to;
            $booking->receive_phone = $req->phone_number_to;
            $booking->receive_province_id = $req->province_id_to;
            $booking->receive_district_id = $req->district_id_to;
            $booking->receive_ward_id = $req->ward_id_to;
            $booking->receive_homenumber = $req->home_number_to;
            $booking->receive_full_address = $this->getAddress($req->province_id_to, $req->district_id_to, $req->ward_id_to, $req->home_number_to);
            $booking->receive_type = $req->receive_type;
            $booking->price = $req->price;
            $booking->weight = $req->weight;
            $booking->transport_type = $req->transport_type;
            $booking->payment_type = $req->payment_type;
            $booking->COD = $req->cod;
            $booking->other_note = $req->other_note;
            $booking->status = 'new';

            // kiểm tra khách lần đầu tiên sử dụng hệ thống (khách mới)
            $check = Booking::where('sender_id', $req->user()->id)->count();
            if ($check == 0) {
                $booking->is_customer_new = 1;
            }
            
            $booking->save();
            $uuid = Booking::find($booking->id);
            $uuid->uuid = str_random(5) . $uuid->id;
            $uuid->save();
            DB::commit();

            // Thông báo tới admin có đơn hàng mới
            $bookingTmp = $booking->toArray();
            $bookingTmp['uuid'] = $uuid->uuid;
            // $notificationHelper = new NotificationHelper();
            // $notificationHelper->notificationBooking($bookingTmp, 'admin', ' vừa được tạo', 'push_order');
            dispatch(new NotificationJob($bookingTmp, 'admin', ' vừa được tạo', 'push_order'));
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
        }
        return redirect(url('admin/booking/new'))->with('success', 'tạo mới đơn hàng thành công');
    }

    //updaye booking
    public function updateBooking($active, $id)
    {
        if ($active == 'return') {
            $check = BookDelivery::where('id', $id)->first();
            if ($check != null) {
                if ($check->book_id != null) {
                    $id = $check->book_id;
                } else {
                    return redirect()->back()->with('danger', 'Đơn hàng không tồn tại');
                }
            }
        }
        $this->breadcrumb[] = 'Chỉnh sửa đơn hàng';
        $booking = Booking::where('id', $id)->first();
        return view('admin.elements.booking.create.edit', ['active' => $active, 'breadcrumb' => $this->breadcrumb, 'booking' => $booking]);
    }

    public function postUpdateBooking(UpdateBookingRequest $req, $active, $id)
    {
        DB::beginTransaction();
        try {
            $sender_id = null;
            $receiver_id = null;
            // $sender_check = User::where('phone_number', $req->phone_number_fr)->where('role', 'customer')->where('delete_status', 0)->first();
            $receiver_check = User::where('phone_number', $req->phone_number_to)->where('role', 'customer')->where('delete_status', 0)->first();
            // if (!empty($sender_check)) {
            //     $sender_id = $sender_check->id;
            // } else {
            //     $check_sender_delete = User::where('phone_number', $req->phone_number_fr)->where('role', 'customer')->where('delete_status', 1)->first();
            //     if (!empty($check_sender_delete)) {
            //         $check_sender_delete->delete_status = 0;
            //         $check_sender_delete->save();
            //         $sender_id = $check_sender_delete->id;
            //     } else {
            //         $user = new User();
            //         $user->phone_number = $req->phone_number_fr;
            //         $user->save();
            //         $sender_id = $user->id;
            //     }
            // }
            // $sender_id = Auth::user()->id;
            if (!empty($receiver_check)) {
                $receiver_id = $receiver_check->id;
            } else {
                $user = new User();
                $user->phone_number = $req->phone_number_to;
                $user->save();
                $receiver_id = $user->id;
            }
            $booking = Booking::find($id);
            // $booking->sender_id = $sender_id;
            $booking->receiver_id = $receiver_id;
            $booking->name = $req->name;
            $booking->send_name = $req->name_fr;
            $booking->send_phone = $req->phone_number_fr;
            $booking->send_province_id = $req->province_id_fr;
            $booking->send_district_id = $req->district_id_fr;
            $booking->send_ward_id = $req->ward_id_fr;
            $booking->send_homenumber = $req->home_number_fr;
            $booking->send_full_address = $this->getAddress($req->province_id_fr, $req->district_id_fr, $req->ward_id_fr, $req->home_number_fr);
            $booking->receive_name = $req->name_to;
            $booking->receive_phone = $req->phone_number_to;
            $booking->receive_province_id = $req->province_id_to;
            $booking->receive_district_id = $req->district_id_to;
            $booking->receive_ward_id = $req->ward_id_to;
            $booking->receive_homenumber = $req->home_number_to;
            $booking->receive_full_address = $this->getAddress($req->province_id_to, $req->district_id_to, $req->ward_id_to, $req->home_number_to);
            $booking->receive_type = $req->receive_type;
            $booking->price = $req->price;
            $booking->incurred = $req->incurred;
            $booking->paid = $req->paid;
            $booking->weight = $req->weight;
            $booking->transport_type = $req->transport_type;
            $booking->payment_type = $req->payment_type;
            $booking->COD = $req->cod;
            $booking->other_note = $req->other_note;
            $booking->note = $req->note;
            $booking->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
        }
        return redirect(url('admin/booking/' . $active))->with('success', 'cập nhật đơn hàng thành công');
    }

    //delete booking
    public function deleteBooking($active, $id)
    {
        if ($active == 'return') {
            $check = BookDelivery::where('id', $id)->first();
            if ($check != null) {
                if ($check->book_id != null) {
                    $id = $check->book_id;
                } else {
                    return redirect()->back()->with('danger', 'Đơn hàng không tồn tại');
                }
            }
        }
        DB::beginTransaction();
        try {
            BookDelivery::where('book_id', $id)->delete();
            Booking::find($id)->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
        }
        return redirect()->back()->with('success', 'Xóa đơn hàng thành công');

    }

    //cancel booking
    public function cancelBooking($active, $id)
    {
        if ($active == 'return') {
            $check = BookDelivery::where('id', $id)->first();
            if ($check != null) {
                if ($check->book_id != null) {
                    $id = $check->book_id;
                } else {
                    return redirect()->back()->with('danger', 'Đơn hàng không tồn tại');
                }
            }
        }
        DB::beginTransaction();
        try {
            BookDelivery::where('book_id', $id)->update(['status' => 'cancel']);
            Booking::find($id)->update(['status' => 'cancel', 'sub_status' => 'none']);
            DB::commit();

            // thông báo tới admin, customer, shipper khi hủy đơn hàng
            $notificationHelper = new NotificationHelper();
            $bookingTmp = Booking::find($id);
            $bookingTmp = $bookingTmp->toArray();
            $bookDeliveryTmp = BookDelivery::where('book_id', $id)->first();
            if ($bookDeliveryTmp && !empty($bookDeliveryTmp)) {
                $bookingTmp['shipper_id'] = $bookDeliveryTmp->user_id;
                $bookingTmp['book_delivery_id'] = $bookDeliveryTmp->id;
                // $notificationHelper->notificationBooking($bookingTmp, 'shipper', ' vừa được hủy', 'push_order_change');
                dispatch(new NotificationJob($bookingTmp, 'shipper', ' vừa được hủy', 'push_order_change'));
            }
            // $notificationHelper->notificationBooking($bookingTmp, 'admin', ' vừa được hủy', 'push_order_change');
            // $notificationHelper->notificationBooking($bookingTmp, 'customer', ' vừa được hủy', 'push_order_change');
            dispatch(new NotificationJob($bookingTmp, 'admin', ' vừa được hủy', 'push_order_change'));
            dispatch(new NotificationJob($bookingTmp, 'customer', ' vừa được hủy', 'push_order_change'));
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
        }
        return redirect()->back()->with('success', 'Hủy đơn hàng thành công');

    }

    //start index route function
    public function newBooking(Request $request)
    {
        $this->updateNotificationReaded($request);

        $this->breadcrumb[] = 'đơn hàng mới';
        if (Auth::user()->role == 'collaborators') {
            $time = Booking::whereIn('send_ward_id', $this->getBookingScope())->where('sub_status', 'none')->whereIn('status', ['new', 'taking'])->min('created_at');
        } else {
            $time = Booking::Where('sub_status', 'none')->where(function ($query) {
                $query->where('status', 'new')->orWhere('status', 'taking');
            })->min('created_at');
        }
        $time_from = $time != null ? date("Y-m-d", strtotime($time)) : Carbon::today()->toDateString();
        return view('admin.elements.booking.new-booking.index', ['active' => 'new_booking', 'breadcrumb' => $this->breadcrumb, 'time_from' => $time_from]);
    }

    public function receiveBooking()
    {
        if (Auth::user()->role == 'collaborators') {
            $agency_id = @Collaborator::where('user_id', Auth::user()->id)->pluck('agency_id');
            $agency = Agency::whereNotIn('id', $agency_id)->where('status', 'active')->orderBy('name', 'asc')->pluck('name', 'id');
            $time = Booking::whereIn('send_ward_id', $this->getBookingScope())->where('sub_status', 'none')->where('status', 'sending')->min('created_at');
        } else {
            $agency = Agency::orderBy('name', 'asc')->where('status', 'active')->pluck('name', 'id');
            $time = Booking::Where('sub_status', 'none')->where('status', 'sending')->min('created_at');
        }
        $time_from = $time != null ? date("Y-m-d", strtotime($time)) : Carbon::today()->toDateString();
        $this->breadcrumb[] = 'đơn đã nhận';
        return view('admin.elements.booking.received-booking.index', ['agency' => $agency, 'active' => 'received', 'breadcrumb' => $this->breadcrumb, 'time_from' => $time_from]);
    }

    public function getDelayBooking()
    {
        if (Auth::user()->role == 'collaborators') {
            $time = Booking::whereIn('send_ward_id', $this->getBookingScope())->where('sub_status', 'delay')->min('created_at');
        } else {
            $time = Booking::Where('sub_status', 'delay')->min('created_at');
        }
        $time_from = $time != null ? date("Y-m-d", strtotime($time)) : Carbon::today()->toDateString();
        $this->breadcrumb[] = 'đơn hàng tạm hoãn';
        return view('admin.elements.booking.delay-booking.index', ['active' => 'delay', 'breadcrumb' => $this->breadcrumb, 'time_from' => $time_from]);
    }

    public function moveBooking()
    {
        if (Auth::user()->role == 'collaborators') {
            $time = Booking::whereIn('send_ward_id', $this->getBookingScope())->where('status', 'move')->min('created_at');
        } else {
            $time = Booking::Where('status', 'move')->min('created_at');
        }
        $time_from = $time != null ? date("Y-m-d", strtotime($time)) : Carbon::today()->toDateString();
        $this->breadcrumb[] = 'đơn hàng chuyển kho';
        return view('admin.elements.booking.move.index', ['active' => 'move_booking', 'breadcrumb' => $this->breadcrumb, 'time_from' => $time_from]);
    }

    public function getCancelBooking()
    {
        $this->breadcrumb[] = 'đơn hàng đã hủy';
        $this->breadcrumb[] = 'đơn hàng đã hoàn thành';
        if (Auth::user()->role == 'collaborators') {
            $time = Booking::where('status', 'cancel')->whereIn('send_ward_id', $this->getBookingScope())->min('updated_at');
        } else {
            $time = Booking::where('status', 'cancel')->min('updated_at');
        }
        $time_from = $time != null ? date("Y-m-d", strtotime($time)) : Carbon::today()->toDateString();
        return view('admin.elements.booking.cancel.index', ['active' => 'cancel', 'breadcrumb' => $this->breadcrumb, 'time_from' => $time_from]);
    }

    public function getSentBooking()
    {
        $this->breadcrumb[] = 'đơn hàng đã hoàn thành';
        if (Auth::user()->role == 'collaborators') {
            $time = Booking::where('status', 'completed')->where('sub_status', 'none')->whereIn('send_ward_id', $this->getBookingScope())->min('updated_at');
        } else {
            $time = Booking::where('status', 'completed')->where('sub_status', 'none')->min('updated_at');
        }
        $time_from = $time != null ? date("Y-m-d", strtotime($time)) : Carbon::today()->toDateString();
        return view('admin.elements.booking.sent.index', ['active' => 'sent', 'breadcrumb' => $this->breadcrumb, 'time_from' => $time_from]);
    }

    public function assign($id)
    {
        $this->breadcrumb[] = 'đơn hàng mới';
        return view('admin.elements.booking.new-booking.assign', ['id' => $id, 'shipper' => $this->getProperties(), 'active' => 'new_booking', 'breadcrumb' => $this->breadcrumb]);
    }

    public function sendAssign($id)
    {
        $this->breadcrumb[] = 'đơn chưa giao';
        return view('admin.elements.booking.received-booking.assign', ['id' => $id, 'shipper' => $this->getProperties(), 'active' => 'received', 'breadcrumb' => $this->breadcrumb]);
    }

    public function continued($cate, $id)
    {
        $selected = BookDelivery::where('book_id', $id)->where('status', 'delay')->first();
        $selected = $selected != null ? $selected->user_id : null;
        $this->breadcrumb[] = 'đơn hàng tạm hoãn';
        return view('admin.elements.booking.continued', ['id' => $id, 'shipper' => $this->getProperties(), 'selected' => $selected, 'active' => $cate, 'breadcrumb' => $this->breadcrumb]);
    }

    public function getReturnBooking()
    {
        if (Auth::user()->role == 'collaborators') {
            $agency_id = @Collaborator::where('user_id', Auth::user()->id)->pluck('agency_id');
            $agency = Agency::whereNotIn('id', $agency_id)->where('status', 'active')->orderBy('name', 'asc')->pluck('name', 'id');
            $time = Booking::whereIn('send_ward_id', $this->getBookingScope())->Where('sub_status', '!=', 'delay')->where('status', 'return')->min('created_at');
        } else {
            $agency = Agency::orderBy('name', 'asc')->where('status', 'active')->pluck('name', 'id');
            $time = Booking::Where('sub_status', '!=', 'delay')->where('status', 'return')->min('created_at');
        }
        $time_from = $time != null ? date("Y-m-d", strtotime($time)) : Carbon::today()->toDateString();
        $this->breadcrumb[] = 'đơn hàng trả lại';
        return view('admin.elements.booking.deny.index', ['agency' => $agency, 'shipper' => $this->getProperties(), 'active' => 'deny', 'breadcrumb' => $this->breadcrumb, 'time_from' => $time_from]);
    }

    public function denyAssign($id)
    {
        $book_id = BookDelivery::find($id)->book_id;
        $incurred = Booking::find($book_id)->price;
        $discount = Setting::where('key', 'booking_incurred')->first();
        if ($discount != null) {
            $discount = $discount->value;
            $incurred = ($incurred * abs($discount)) / 100;
        }
        $this->breadcrumb[] = 'đơn hàng trả lại';
        return view('admin.elements.booking.deny.assign', ['id' => $id, 'shipper' => $this->getProperties(), 'incurred' => $incurred, 'active' => 'deny', 'breadcrumb' => $this->breadcrumb]);
    }

    public function reAssign($cate, $id)
    {
        $view = '';
        switch ($cate) {
            case 'taking':
                $this->breadcrumb[] = 'đơn hàng mới';
                $view = view('admin.elements.booking.new-booking.assign', ['id' => $id, 'shipper' => $this->getProperties(), 'active' => 'new_booking', 'breadcrumb' => $this->breadcrumb, 'reAssign' => true, 'cate' => $cate]);
                break;
            case 'sending':
                $this->breadcrumb[] = 'đơn chưa giao';
                $view = view('admin.elements.booking.received-booking.assign', ['id' => $id, 'shipper' => $this->getProperties(), 'active' => 'received', 'breadcrumb' => $this->breadcrumb, 'reAssign' => true, 'cate' => $cate]);
                break;
            case 'deny':
                $book_id = BookDelivery::find($id)->book_id;
                $incurred = Booking::find($book_id)->incurred;
                $this->breadcrumb[] = 'đơn hàng trả lại';
                $view = view('admin.elements.booking.deny.assign', ['id' => $id, 'shipper' => $this->getProperties(), 'incurred' => $incurred, 'active' => 'deny', 'breadcrumb' => $this->breadcrumb, 'reAssign' => true, 'cate' => $cate]);
                break;
            default:
                $view = redirect()->back();
        }
        return $view;
    }
    //end index route function
    //start function feature
    //assign
    public function postAssign($id, AssignRequest $req)
    {
        $booking = Booking::find($id);
        $check = BookDelivery::where('book_id', $id)->first();
        if ($check == null) {
            DB::beginTransaction();
            try {
                if ($req->category == 'r-and-s') {
                    BookDelivery::insert([[
                        'user_id' => $req->shipper,
                        'send_address' => $booking->send_full_address,
                        'receive_address' => $booking->receive_full_address,
                        'book_id' => $id,
                        'category' => 'receive',
                        'sending_active' => 0,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ], [
                        'user_id' => $req->shipper,
                        'send_address' => $booking->send_full_address,
                        'receive_address' => $booking->receive_full_address,
                        'book_id' => $id,
                        'category' => 'send',
                        'sending_active' => 1,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]]);
                } else {
                    BookDelivery::insert([
                        'user_id' => $req->shipper,
                        'send_address' => $booking->send_full_address,
                        'receive_address' => $booking->receive_full_address,
                        'book_id' => $id,
                        'category' => $req->category,
                        'sending_active' => 1,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }
                $booking->status = 'taking';
                $booking->save();
                DB::commit();

                $bookingDelivery = BookDelivery::where('book_id', $id)->where('user_id', $req->shipper)->where('sending_active', 1)->first();
                //gửi thông báo tới shipper khi được phân công
                $bookingTmp = $booking->toArray();
                $bookingTmp['shipper_id'] = $req->shipper;
                $bookingTmp['book_delivery_id'] = $bookingDelivery->id;
                // echo '<pre>';print_r($bookingTmp);die;
                // $notificationHelper = new NotificationHelper();
                // $notificationHelper->notificationBooking($bookingTmp, 'shipper', ' vừa được phân công cho bạn', 'push_order_assign');
                dispatch(new NotificationJob($bookingTmp, 'shipper', ' vừa được phân công cho bạn', 'push_order_assign'));
            } catch (\Exception $e) {
                DB::rollBack();
                return $e;
            }
        }
        return redirect(url('admin/booking/new'));
    }

    public function postSendAssign($id, Request $req)
    {
        $booking = Booking::find($id);
        $check = BookDelivery::where('book_id', $id)->where('category', 'send')->first();
        if ($check == null) {
            DB::beginTransaction();
            try {
                $booking->update(['status' => 'sending']);
                BookDelivery::insert([
                    'user_id' => $req->shipper,
                    'send_address' => $booking->send_full_address,
                    'receive_address' => $booking->receive_full_address,
                    'book_id' => $id,
                    'category' => $req->category,
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
        return redirect(url('admin/booking/received'));
    }

    public function postReAssign($cate, $id, AssignRequest $req)
    {
        DB::beginTransaction();
        try {
            $check = null;
            if ($cate == 'taking') {
                $check = BookDelivery::where('book_id', $id)->where('category', 'receive')->where('status', 'processing')->first();
                if ($check != null) {
                    $check_ras = BookDelivery::where('book_id', $id)->where('category', 'send')->where('status', 'processing')->where('user_id', $check->user_id)->first();
                    if ($check_ras != null) {
                        $check_ras->user_id = $req->shipper;
                        $check_ras->save();
                    }
                    $check->user_id = $req->shipper;
                    $check->save();
                }
                $url = 'new';

            }
            if ($cate == 'sending') {
                $check = BookDelivery::where('book_id', $id)->where('category', 'send')->where('status', 'processing')->first();
                if ($check != null) {
                    $check->user_id = $req->shipper;
                    $check->save();
                }
                $url = 'received';
            }
            if ($cate == 'deny') {
                $delivery = BookDelivery::find($id);
                if ($delivery != null) {
                    $delivery->user_id = $req->shipper;
                    $delivery->save();
                    Booking::where('id', $delivery->book_id)->update(['incurred' => $req->incurred]);
                }
                $url = 'return';
            }
            DB::commit();
            return redirect(url('admin/booking/' . $url));
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
    }

    //delay
    public function delay($category, $id)
    {
        DB::beginTransaction();
        try {
            $delivery = BookDelivery::where('book_id', $id)->where('category', $category)->first();
            if ($delivery->delay_total >= 2) {
                $delivery->delay_total += 1;
                $delivery->status = 'cancel';
                Booking::where('id', $delivery->book_id)->update(['status' => 'cancel', 'sub_status' => 'none']);
            } else {
                $delivery->status = 'delay';
                $delivery->delay_total += 1;
                Booking::where('id', $delivery->book_id)->update(['sub_status' => 'delay']);
            }
            $delivery->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
        return redirect(url('admin/booking/delay'));
    }

    public function postContinued(Request $req, $cate, $id)
    {
        DB::beginTransaction();
        try {
            $delivery = BookDelivery::where([]);
            if ($cate == 'deny') {
                $delivery = $delivery->where('id', $id);
            } else {
                $delivery = $delivery->where('book_id', $id)->where('status', 'delay');
            }
            $delivery = $delivery->first();
            if ($delivery != null) {
                $query = ['sub_status' => 'none'];
                if ($cate == 'deny') {
                    $delivery->category = 'send';
                    $query['status'] = 'sending';
                }
                $delivery->status = 'processing';
                $delivery->user_id = $req->shipper;
                $delivery->save();
                Booking::find($delivery->book_id)->update($query);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
        return redirect(url($cate == 'deny' ? '/admin/booking/return' : '/admin/booking/' . $cate))->with('success', 'Tiếp tục giao đơn hàng thành công');
    }

    //move
    public function postMoveBooking(Request $req)
    {
        if ($req->agency == null) {
            return redirect()->back()->with('delete', 'Không có đại lý nào được chọn');
        } else {
            DB::beginTransaction();
            try {
                $booking = Booking::find($req->booking);
                if ($booking->status == 'return') {
                    $booking->sub_status = 'move_return';
                }
                $booking->status = 'move';
                $agency = null;

                $delivery = BookDelivery::where('book_id', $booking->id)
                                ->where('category', 'move')
                                ->first();
                if (empty($delivery)) {
                    $delivery = new  BookDelivery();
                }
                $delivery->user_id = 0;
                $delivery->send_address = $booking->send_full_address;
                $delivery->receive_address = $booking->receive_full_address;
                $delivery->book_id = $req->booking;
                $delivery->category = 'move';
                $delivery->last_agency = $req->agency;
                $delivery->last_move = 1;
                $delivery->status = 'processing';

                if ($booking->current_agency == null) {
                    $agency = ManagementWardScope::where('ward_id', $booking->send_ward_id)->first();
                    if ($agency == null) {
                        $agency = ManagementScope::where('district_id', $booking->send_district_id)->first();
                    }
                    $booking->current_agency = $agency->agency_id;
                    $delivery->current_agency = $agency->agency_id;
                    $delivery->save();
                } else {
                    $delivery->current_agency = $booking->current_agency;
                    $delivery->save();
                    BookDelivery::where('category', 'move')->where('book_id', $booking->id)->where('id', '!=', $delivery->id)->update(['last_move' => 0]);
                }
                $booking->save();
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return $e;
            }
            return redirect(url('admin/booking/move_booking'));
        }
    }

    public function movedBooking($id)
    {
        DB::beginTransaction();
        try {
            $agency = BookDelivery::find($id);
            $agency->status = 'completed';
            $agency->save();
            $booking = Booking::find($agency->book_id);
            $booking->current_agency = $agency->last_agency;
            if ($booking->sub_status == 'move_return') {
                $booking->status = 'return';
                $booking->sub_status = 'none';
            }
            $booking->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
        return redirect(url('admin/booking/move_booking'))->with('success', 'Đã nhận đơn hàng chuyển đến');
    }

    //completed
    public function completed($category, $id, Request $request)
    {
        DB::beginTransaction();
        try {
            if (in_array($category, ['send', 'receive', 'return', 'move'])) {
                $delivery = BookDelivery::where('book_id', $id)->where('category', $category)->first();
                if ($delivery->status != 'completed') {
                    $agency_check = Shipper::where('user_id', $delivery->user_id)->first();
                    $agency_id = $agency_check != null ? $agency_check->agency_id : 1;
                    $booking = Booking::find($delivery->book_id);
                    $revenue = ShipperRevenue::where('shipper_id', $delivery->user_id)->first();
                    if ($revenue == null) {
                        $revenue = new ShipperRevenue();
                    }
                    $delivery->status = 'completed';
                    $bookingTmp = $booking->toArray();
                    $bookingTmp['book_delivery_id'] = $delivery->id;
                    if ($category == 'return') {
                        $booking->last_agency = $agency_id;
                        if ($booking->owe == 1) {
                            if ($booking->incurred > 0) {
                                if ($request->owe == 0 || !$request->owe) {
                                    $booking->owe = 0;
                                } else {
                                    $revenue->shipper_id = $delivery->user_id;
                                    $revenue->total_price += $booking->incurred;
                                    $booking->paid += $booking->incurred;
                                }
                            }
                        } else {
                            if (isset($request->owe)) {
                                if ($request->owe == 1) {
                                    $revenue->shipper_id = $delivery->user_id;
                                    $revenue->total_price += ($booking->price + $booking->incurred);
                                    $booking->paid += ($booking->price + $booking->incurred);
                                    $booking->owe = 1;
                                }
                            }
                        }
                        // thông báo tới khách hàng là đơn hàng đã được trả lại
                        dispatch(new NotificationJob($bookingTmp, 'customer', ' đã được trả lại', 'push_order_change'));
                    } else {
                        if ($category == 'receive') {
                            //Tạo và gửi thông báo tới customer là: đã thanh toán tiền nợ
                            if ($request->owe && $request->owe == 1) {
                                dispatch(new NotificationJob($bookingTmp, 'customer', ' đã được thanh toán nợ', 'push_customer_owe'));
                            }

                            // thông báo tới khách hàng là đơn hàng đã được lấy
                            dispatch(new NotificationJob($bookingTmp, 'customer', ' đã được lấy', 'push_order_change'));

                            if ($booking->payment_type == 1) {
                                if (isset($request->owe) && $request->owe == 1) {
                                    $booking->paid = $booking->price;
                                    // $revenue->shipper_id = $delivery->user_id;
                                    $revenue->total_price += $booking->price;
                                    $booking->owe = 1;
                                }
                            } else {
                                // $booking->paid = $booking->price;
                                // $revenue->shipper_id = $delivery->user_id;
                                // $revenue->total_price += $booking->price;
                                // $booking->owe = 1;
                            }
                            // $booking->owe = 1;
                            $revenue->shipper_id = $delivery->user_id;
                            $booking->status = 'sending';
                            $booking->first_agency = $agency_id;
                            $booking->current_agency = $agency_id;
                        } else if ($category == 'send') {
                            $check = BookDelivery::where('book_id', $delivery->book_id)->where('category', 'receive')->where('status', 'processing')->first();
                            if ($check != null) {
                                $check->status = 'completed';
                                $check->save();
                            }
                            if ($booking->COD > 0 && $booking->status != 'completed') {
                                $user = User::find($booking->sender_id);
                                $user->total_COD += $booking->COD;
                                $user->save();
                            }
                            if ($booking->payment_type == 2) {
                                $booking->paid = $booking->price;
                                // $revenue->shipper_id = $delivery->user_id;
                                $revenue->total_price += $booking->price;
                                $booking->owe = 1;
                            }
                            $revenue->shipper_id = $delivery->user_id;
                            $booking->last_agency = $agency_id;
                            $revenue->total_COD += $booking->COD;
                            $booking->status = 'completed';

                            // thông báo tới khách hàng là đơn hàng đã được giao
                            dispatch(new NotificationJob($bookingTmp, 'customer', ' đã được giao', 'push_order_change'));
                        } else {
                            $booking->status = 'completed';
                        }
                        $booking->sub_status = 'none';
                    }
                    $booking->completed_at = Carbon::now();
                    $booking->save();
                    $delivery->completed_at = Carbon::now();
                    $delivery->save();
                    $revenue->save();
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }

        return redirect()->back();
    }

//deny
    public function deny($id)
    {
        DB::beginTransaction();
        try {
            $booking = Booking::where('id', $id)->first();
            $delivery = BookDelivery::where('book_id', $id);
            $bookingTmp = $booking->toArray();
            
            if ($booking->status == 'return') {
                $delivery = $delivery->where('category', 'return')->first();
                if ($delivery->user_id != 0) {
                    $booking->sub_status = 'deny';
                    $delivery->status = 'deny';
                    $delivery->save();
                }
            } else if ($booking->status == 'sending') {
                $delivery = $delivery->where('category', 'send')->where('sending_active', 1)->first();
                $delivery->category = 'return';
                // $delivery->user_id = 0;
                $delivery->status = 'deny';
                $booking->status = 'return';
                $booking->sub_status = 'none';
                $delivery->save();
            }
            $bookingTmp['book_delivery_id'] = $delivery->id;
            // thông báo tới khách hàng là đơn hàng đã được giao
            dispatch(new NotificationJob($bookingTmp, 'customer', ' đã được giao lại/trả lại', 'push_order_change'));
            
            $booking->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
        }
        return redirect(url('admin/booking/return'));
    }

    public function postDenyAssign($id, AssignRequest $req)
    {
        DB::beginTransaction();
        try {
            $delivery = BookDelivery::find($id);
            $delivery->user_id = $req->shipper;
            $delivery->status = 'processing';
            $delivery->save();
            Booking::where('id', $delivery->book_id)->update(['incurred' => $req->incurred]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
        return redirect(url('admin/booking/return'))->with('success', 'Phân công trả lại đơn hàng thành công!');
    }

    private function updateNotificationReaded($request) {
        if ($request->has('notification_id') && $request->has('is_readed') && $request->is_readed == 1) {
            $notificationUser = NotificationUser::where('notification_id', $request->notification_id)
                                    ->where('user_id', Auth::user()->id)
                                    ->update(array('is_readed' => 1));
        }
    }

    // chuyển đơn hàng từ chối qua lại đơn hàng chưa giao để tiếp tục giao
    // đồng thời xóa bỏ shipper cũ, phân công lại
    public function moveToReceive($bookDeliveryId) {
        DB::beginTransaction();
        try {
            $delivery = BookDelivery::find($bookDeliveryId);
            $book_id = $delivery->book_id;
            $delivery->delete();

            Booking::where('id', $book_id)->update(['status' => 'sending']);
            BookDelivery::where('book_id', $book_id)->update(['sending_active' => 1]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
        return redirect()->back();
    }

//end function assgin
}
