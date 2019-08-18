<?php

namespace App\Http\Controllers\Admin\Report;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\Booking;
use App\Models\Collaborator;
use App\Models\ManagementWardScope;
use App\Models\Revenue;
use App\Models\Shipper;
use App\Models\User;
use Carbon\Carbon;
use DB, Excel;
use function dd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use function number_format;
use App\Helpers\NotificationHelper;

class ReportController extends Controller
{
    protected $breadcrumb = ['Thống kê'];

    protected function getBookingScope()
    {
        $user_id = Auth::user()->id;
        $scope = Collaborator::where('user_id', $user_id)->pluck('agency_id');
        $ward = ManagementWardScope::whereIn('agency_id', $scope)->pluck('ward_id');
        return $ward->toArray();
    }

    public function index()
    {
        $this->breadcrumb[] = 'thống kê';
        $booking_counts = Booking::count();
        $user_counts = User::where('role', '=', 'customer')->where('status', '=', 'active')->where('delete_status', 0)->count();
        $shipper_counts = User::where('role', '=', 'shipper')->where('status', '=', 'active')->where('delete_status', 0);
        $agency_counts = Agency::where('status', '=', 'active')->count();
        $sumary = Revenue::where([]);
        $sum_booking = Booking::whereNotNull('created_at');
        $new_booking = Booking::whereNotNull('created_at')->where('status', 'new');
        $complete_booking = Booking::whereNotNull('created_at')->where('status', 'completed');
        $cancel_booking = Booking::whereNotNull('created_at')->where('status', 'cancel');
        $bookCompleteToday = Booking::whereDate('completed_at', date('Y-m-d'))->where('status', 'completed');
        $bookCompleteTodayArr = [
            'count' => $bookCompleteToday->count(),
            'amount' => $bookCompleteToday->sum('price')
        ];
        $totalCODToday = Booking::whereDate('completed_at', date('Y-m-d'))->where('status', 'completed');
        if (Auth::user()->role != 'admin') {
            $agency = Collaborator::where('user_id', Auth::user()->id)->pluck('agency_id');
            $shipper = Shipper::whereIn('agency_id', $agency)->pluck('user_id');
            $shipper_counts = $shipper_counts->whereIn('id', $shipper);
            $sumary = $sumary->whereIn('agency_id', $agency);
            $sum_booking = $sum_booking->whereIn('send_ward_id', $this->getBookingScope());
            $new_booking = $new_booking->whereIn('send_ward_id', $this->getBookingScope());
            $complete_booking = $complete_booking->whereIn('send_ward_id', $this->getBookingScope());
            $cancel_booking = $cancel_booking->whereIn('send_ward_id', $this->getBookingScope());
            $bookCompleteToday = $bookCompleteToday->whereIn('send_ward_id', $this->getBookingScope());
            $bookCompleteTodayArr = [
                'count' => $bookCompleteToday->count(),
                'amount' => $bookCompleteToday->sum('price')
            ];
            $totalCODToday = $totalCODToday->whereIn('send_ward_id', $this->getBookingScope());
        }
        $totalCODToday = $totalCODToday->sum('COD');
        return view('admin.elements.report.index', ['active' => 'report',
            'sumary' => Auth::user()->role != 'admin' ? $sumary->sum('agency_discount') : $sumary->sum('booking_revenue'),
            'breadcrumb' => $this->breadcrumb, 'booking_counts' => $booking_counts,
            'user_counts' => $user_counts, 'agency_counts' => $agency_counts,
            'shipper_counts' => $shipper_counts->count(), 'sum_booking' => $sum_booking->count(), 'new_booking' => $new_booking->count(),
            'complete_booking' => $complete_booking->count(), 'cancel_booking' => $cancel_booking->count(),
            'bookCompleteTodayArr' => $bookCompleteTodayArr,
            'totalCODToday' => $totalCODToday
        ]);
    }

    public function booking_chart(Request $request)
    {
        $today = Carbon::now()->addDay()->format('Y/m/d');
        $yesterday = Carbon::now()->subDays(1)->format('Y/m/d');
        $this_week = Carbon::now()->subDays(7)->format('Y/m/d');
        $this_month = Carbon::now()->subDays(30)->format('Y/m/d');
        $this_quarter = Carbon::now()->quarter;
        $this_year = Carbon::now()->year;
        $date_from_bookings = $request->input('date_from_bookings'). ' 00:00:00';
        $date_to_bookings = $request->input('date_to_bookings'). ' 23:59:59';
        $show_by_bookings = $request->input('show_by_bookings');

        if ($show_by_bookings == 1) {
            $data_booking = Booking::whereNotNull('created_at')
                ->whereBetween('created_at', [$this_week, $today])
                ->groupBy('day')
                ->get([
                    DB::raw('DATE(created_at) as day'),
                    DB::raw('count(*) as bookings_counts'),
                ]);
        }
        if ($show_by_bookings == 'range_date') {
            // Count bookings & group by day
            $data_booking = Booking::whereNotNull('created_at')
                ->whereBetween('created_at', [$date_from_bookings, $date_to_bookings])
                ->groupBy('day')
                ->orderBy('day', 'asc')
                ->get([
                    DB::raw('DATE(created_at) as day'),
                    DB::raw('COUNT(*) as bookings_counts'),
                ]);
        }
        if ($show_by_bookings == 'today') {
            // Count bookings & group by day
            $data_booking = Booking::whereNotNull('created_at')
                ->whereBetween('created_at', [$yesterday, $today])
                ->groupBy('day')
                ->get([
                    DB::raw('DATE(created_at) as day'),
                    DB::raw('count(*) as bookings_counts'),
                ]);
        }
        if ($show_by_bookings == 'this_week') {
            $data_booking = Booking::whereNotNull('created_at')
                ->whereBetween('created_at', [$this_week, $today])
                ->groupBy('day')
                ->get([
                    DB::raw('DATE(created_at) as day'),
                    DB::raw('count(*) as bookings_counts'),
                ]);
        }
        if ($show_by_bookings == 'this_month') {
            $data_booking = Booking::whereNotNull('created_at')
                ->whereBetween('created_at', [$this_month, $today])
                ->groupBy('day')
                ->get([
                    DB::raw('DATE(created_at) as day'),
                    DB::raw('count(*) as bookings_counts'),
                ]);
        }
        if ($show_by_bookings == 'this_quarter') {
            $data_booking = Booking::whereNotNull('created_at')
                ->where(DB::raw('QUARTER(created_at)'), $this_quarter)
                ->where(DB::raw('YEAR(created_at)'), $this_year)
                ->groupBy('day')
                ->get([
                    DB::raw('DATE(created_at) as day'),
                    DB::raw('count(*) as bookings_counts'),
                ]);
        }
        if ($show_by_bookings == 'this_year') {
            $data_booking = Booking::whereNotNull('created_at')
                ->where(DB::raw('YEAR(created_at)'), $this_year)
                ->groupBy('day')
                ->get([
                    DB::raw('DATE(created_at) as day'),
                    DB::raw('count(*) as bookings_counts'),
                ]);
        }
        return $data_booking;
    }

    public function getReport(Request $request)
    {
        $date_from_report = $request->input('date_from_report'). ' 00:00:00';
        $date_to_report = $request->input('date_to_report'). ' 23:59:59';
        $type_of_report = $request->input('type_of_report');

        if (isset($type_of_report) && $type_of_report == 1 && isset($date_from_report) && isset($date_to_report)) {
            $user_counts = User::where('role', '=', 'customer')->whereBetween('created_at', [$date_from_report, $date_to_report])->count();
            $data_report['sum'] = $user_counts . ' ' . 'khách hàng';
            $data_rep = User::whereNotNull('created_at')
                ->whereBetween('created_at', [$date_from_report, $date_to_report])
                ->where('users.role', '=', 'customer')
                ->select(
                    DB::Raw("IFNULL( username, '' )"),
                    DB::Raw("IFNULL( name, '' )"),
                    DB::Raw("IFNULL(birth_day, '' )"),
                    DB::Raw("IFNULL(phone_number, '' )"),
                    DB::Raw("IFNULL(bank_account, '' )"),
                    DB::Raw("IFNULL(bank_account_number, '')"),
                    DB::Raw("IFNULL(bank_name, '' )"),
                    DB::Raw("IFNULL(bank_branch, '' )"),
                    DB::Raw("IFNULL(created_at, '' )")
                )
                ->get()->toArray();
            $data_report['data_rep'] = $data_rep;
            $data_report['data_title'] = ['Tài khoản', 'Tên', 'Ngày sinh', 'Số điện thoại', 'Tài khoàn NH', 'Số TK', 'Tên Ngân hàng', 'Chi Nhánh', 'Ngày đăng ký'];
        }
        if (isset($type_of_report) && $type_of_report == 2 && isset($date_from_report) && isset($date_to_report)) {
            $shipper_counts = User::whereBetween('created_at', [$date_from_report, $date_to_report])->where('role', '=', 'shipper');
            $data_rep = DB::table('users')->whereNotNull('created_at')->where('status', '=', 'active')->where('role', '=', 'shipper')
                ->whereBetween('created_at', [$date_from_report, $date_to_report])
                ->select(DB::Raw("IFNULL(uuid, '' )"), DB::Raw("IFNULL(name, '' )"), DB::Raw("IFNULL(phone_number, '' )"), DB::Raw("IFNULL(created_at, '' )"));

            if (Auth::user()->role == 'collaborators') {
                $agency = Collaborator::where('user_id', Auth::user()->id)->pluck('agency_id');
                $shipper = Shipper::whereIn('agency_id', $agency)->pluck('user_id');
                $data_rep = $data_rep->whereIn('id', $shipper);
                $shipper_counts = $shipper_counts->whereIn('id', $shipper);
            }
            $shipper_counts = $shipper_counts->count();
            $data_rep = $data_rep->get()->toArray();
            $data_report['sum'] = $shipper_counts . ' ' . 'shipper';
            $data_report['data_rep'] = $data_rep;
            $data_report['data_title'] = ['Mã Shipper', 'Tên Shipper', 'Số điện thoại', 'Ngày đăng ký'];
        }
        if (isset($type_of_report) && $type_of_report == 3 && isset($date_from_report) && isset($date_to_report)) {
            $agency_counts = Agency::whereBetween('created_at', [$date_from_report, $date_to_report])->count();
            $data_report['sum'] = $agency_counts . ' ' . 'đại lý';
            $data_rep = Agency::whereNotNull('created_at')->whereBetween('created_at', [$date_from_report, $date_to_report])
                ->with('revenues', 'liabilities')->select('id', 'name', 'address', 'discount')->get()->toArray();
            $data_report['data_rep'] = $data_rep;
            $data_report['data_title'] = ['Mã đại lý','Tên đại lý', 'Địa chỉ', 'Chiết khấu(%)','Người quản lý', 'Tổng doanh thu đơn hàng', 'Tổng doanh thu chiết khấu', 'Đại lý đã thanh toán', 'Hệ thống đã thanh toán'];
            $data_report['action'] = 3;
        }
        if (isset($type_of_report) && $type_of_report == 4 && isset($date_from_report) && isset($date_to_report)) {
            $title = ['Tổng Doanh thu đơn hàng', 'Tổng doanh thu chiết khấu', 'Ngày'];
            $data_rep = DB::table('revenues')->whereBetween('created_at', [$date_from_report, $date_to_report])
                ->select( [DB::raw('sum(booking_revenue)'), DB::raw('sum(agency_discount)'), DB::raw('DATE(created_at) as day')]);
            $sum_counts = Revenue::whereBetween('created_at', [$date_from_report, $date_to_report]);
            if (Auth::user()->role != 'admin') {
                $scope = Collaborator::where('user_id', Auth::user()->id)->pluck('agency_id');
                $data_rep = Agency::whereNotNull('created_at')->whereBetween('created_at', [$date_from_report, $date_to_report])
                    ->whereIn('id', $scope)->with('revenues', 'liabilities')->select('id', 'name', 'address', 'discount');
                $sum_counts = $sum_counts->whereIn('agency_id', $scope);
                $data_report['action'] = 4;
                $title = ['Mã đại lý','Tên đại lý', 'Địa chỉ', 'Chiết khấu(%)','Người quản lý', 'Tổng doanh thu đơn hàng', 'Tổng doanh thu chiết khấu', 'Đại lý đã thanh toán', 'Hệ thống đã thanh toán'];
            }
            $data_report['data_rep'] = Auth::user()->role != 'admin' ? $data_rep->groupBy('id')->get() : $data_rep->groupBy('day')->get();
            $data_report['data_title'] = $title;
            $data_report['sum'] = Auth::user()->role != 'admin' ? number_format($sum_counts->sum('agency_discount')) : number_format($sum_counts->sum('booking_revenue')) . ' ' . 'VNĐ';
        }
        if (isset($type_of_report) && $type_of_report == 5 && isset($date_from_report) && isset($date_to_report)) {
            if (Auth::user()->role != 'admin') {
                $price_by_cola = DB::table('bookings')->whereIn('send_ward_id', $this->getBookingScope())///
                ->whereNotNull('created_at')
                    ->whereBetween('created_at', [$date_from_report, $date_to_report])
                    ->select(
                        DB::Raw("IFNULL(uuid, '' )"),
                        DB::Raw("IFNULL(status, '' )"),
                        DB::Raw("IFNULL(send_name, '' )"),
                        DB::Raw("IFNULL(receive_name, '' )"),
                        DB::Raw("IFNULL(send_full_address, '' )"),
                        DB::Raw("IFNULL(receive_full_address, '' )"),
                        DB::Raw("IFNULL(price, '' )"),
                        DB::Raw("IFNULL(created_at, '' )")
                    )
                    ->get()->toArray();
                $sum_delivery_counts = DB::table('bookings')->whereIn('send_ward_id', $this->getBookingScope())->whereBetween('created_at', [$date_from_report, $date_to_report])->count();
                // $agency_data = DB::table('collaborators')->where('user_id', '=', $user_id)->get();

                $data_report['data_rep'] = $price_by_cola;
                $data_report['data_title'] = ['Mã đơn hàng', 'Trạng thái', 'Người gửi', 'Người nhận', 'Nơi gửi', 'Nơi nhận', 'Giá', 'Ngày tạo'];
            } else {
                $sum_delivery_counts = DB::table('bookings')->whereBetween('created_at', [$date_from_report, $date_to_report])->count();

                $data_rep = DB::table('bookings')->whereNotNull('created_at')
                    ->whereBetween('created_at', [$date_from_report, $date_to_report])
                    ->select(
                        DB::Raw("IFNULL(uuid, '' )"),
                        DB::Raw("IFNULL(status, '' )"),
                        DB::Raw("IFNULL(send_name, '' )"),
                        DB::Raw("IFNULL(receive_name, '' )"),
                        DB::Raw("IFNULL(send_full_address, '' )"),
                        DB::Raw("IFNULL(receive_full_address, '' )"),
                        DB::Raw("IFNULL(price, '' )"),
                        DB::Raw("IFNULL(created_at, '' )")
                    )
                    ->get()->toArray();
                $data_report['data_rep'] = $data_rep;
                $data_report['data_title'] = ['Mã đơn hàng', 'Trạng thái', 'Người gửi', 'Người nhận', 'Nơi gửi', 'Nơi nhận', 'Giá', 'Ngày tạo'];
            }
            $data_report['sum'] = $sum_delivery_counts . ' ' . 'đơn hàng được đặt';
        }
        if (isset($type_of_report) && $type_of_report == 6 && isset($date_from_report) && isset($date_to_report)) {
            if (Auth::user()->role != 'admin') {
                $price_by_cola = DB::table('bookings')->whereIn('send_ward_id', $this->getBookingScope())
                    ->whereNotNull('created_at')
                    ->whereBetween('created_at', [$date_from_report, $date_to_report])
                    ->where('status', 'new')
                    ->select(
                        DB::Raw("IFNULL(uuid, '' )"),
                        DB::Raw("IFNULL(status, '' )"),
                        DB::Raw("IFNULL(send_name, '' )"),
                        DB::Raw("IFNULL(receive_name, '' )"),
                        DB::Raw("IFNULL(send_full_address, '' )"),
                        DB::Raw("IFNULL(receive_full_address, '' )"),
                        DB::Raw("IFNULL(price, '' )"),
                        DB::Raw("IFNULL(created_at, '' )")
                    )
                    ->get()->toArray();
                $new_counts = DB::table('bookings')->whereIn('send_ward_id', $this->getBookingScope())->whereBetween('created_at', [$date_from_report, $date_to_report])->where('status', 'new')->count();

                $data_report['data_rep'] = $price_by_cola;
                $data_report['data_title'] = ['Mã đơn hàng', 'Trạng thái', 'Người gửi', 'Người nhận', 'Nơi gửi', 'Nơi nhận', 'Giá', 'Ngày tạo'];
            } else {
                $new_counts = DB::table('bookings')->where('status', 'new')->whereBetween('created_at', [$date_from_report, $date_to_report])->count();

                $data_rep = DB::table('bookings')->whereNotNull('created_at')
                    ->where('status', '=', 'new')
                    ->whereBetween('created_at', [$date_from_report, $date_to_report])
                    ->select(
                        DB::Raw("IFNULL(uuid, '' )"),
                        DB::Raw("IFNULL(status, '' )"),
                        DB::Raw("IFNULL(send_name, '' )"),
                        DB::Raw("IFNULL(receive_name, '' )"),
                        DB::Raw("IFNULL(send_full_address, '' )"),
                        DB::Raw("IFNULL(receive_full_address, '' )"),
                        DB::Raw("IFNULL(price, '' )"),
                        DB::Raw("IFNULL(created_at, '' )")
                    )
                    ->get()->toArray();
                $data_report['data_rep'] = $data_rep;
                $data_report['data_title'] = ['Mã đơn hàng', 'Trạng thái', 'Người gửi', 'Người nhận', 'Nơi gửi', 'Nơi nhận', 'Giá', 'Ngày tạo'];
            }
            $data_report['sum'] = $new_counts . ' ' . 'đơn hàng mới';
        }
        if (isset($type_of_report) && $type_of_report == 7 && isset($date_from_report) && isset($date_to_report)) {
            if (Auth::user()->role != 'admin') {
                $price_by_cola = DB::table('bookings')->whereIn('send_ward_id', $this->getBookingScope())
                    ->whereNotNull('created_at')
                    ->whereBetween('created_at', [$date_from_report, $date_to_report])
                    ->where('status', 'completed')
                    ->select(
                        DB::Raw("IFNULL(uuid, '' )"),
                        DB::Raw("IFNULL(status, '' )"),
                        DB::Raw("IFNULL(send_name, '' )"),
                        DB::Raw("IFNULL(receive_name, '' )"),
                        DB::Raw("IFNULL(send_full_address, '' )"),
                        DB::Raw("IFNULL(receive_full_address, '' )"),
                        DB::Raw("IFNULL(price, '' )"),
                        DB::Raw("IFNULL(created_at, '' )")
                    )
                    ->get()->toArray();
                $compeleted_counts = DB::table('bookings')->whereIn('send_ward_id', $this->getBookingScope())->whereBetween('created_at', [$date_from_report, $date_to_report])->where('status', 'completed')->count();

                $data_report['data_rep'] = $price_by_cola;
                $data_report['data_title'] = ['Mã đơn hàng', 'Trạng thái', 'Người gửi', 'Người nhận', 'Nơi gửi', 'Nơi nhận', 'Giá', 'Ngày tạo'];
            } else {
                $compeleted_counts = DB::table('bookings')->whereBetween('created_at', [$date_from_report, $date_to_report])->where('status', 'completed')->count();

                $data_rep = DB::table('bookings')->whereNotNull('created_at')
                    ->where('status', '=', 'completed')
                    ->whereBetween('created_at', [$date_from_report, $date_to_report])
                    ->select(
                        DB::Raw("IFNULL(uuid, '' )"),
                        DB::Raw("IFNULL(status, '' )"),
                        DB::Raw("IFNULL(send_name, '' )"),
                        DB::Raw("IFNULL(receive_name, '' )"),
                        DB::Raw("IFNULL(send_full_address, '' )"),
                        DB::Raw("IFNULL(receive_full_address, '' )"),
                        DB::Raw("IFNULL(price, '' )"),
                        DB::Raw("IFNULL(created_at, '' )")
                    )
                    ->get()->toArray();
                $data_report['data_rep'] = $data_rep;
                $data_report['data_title'] = ['Mã đơn hàng', 'Trạng thái', 'Người gửi', 'Người nhận', 'Nơi gửi', 'Nơi nhận', 'Giá', 'Ngày tạo'];
            }
            $data_report['sum'] = $compeleted_counts . ' ' . 'đơn hàng thành công';
        }
        if (isset($type_of_report) && $type_of_report == 8 && isset($date_from_report) && isset($date_to_report)) {
            if (Auth::user()->role != 'admin') {
                $price_by_cola = DB::table('bookings')->whereIn('send_ward_id', $this->getBookingScope())
                    ->whereNotNull('created_at')
                    ->whereBetween('created_at', [$date_from_report, $date_to_report])
                    ->where('status', 'cancel')
                    ->select(
                        DB::Raw("IFNULL(uuid, '' )"),
                        DB::Raw("IFNULL(status, '' )"),
                        DB::Raw("IFNULL(send_name, '' )"),
                        DB::Raw("IFNULL(receive_name, '' )"),
                        DB::Raw("IFNULL(send_full_address, '' )"),
                        DB::Raw("IFNULL(receive_full_address, '' )"),
                        DB::Raw("IFNULL(price, '' )"),
                        DB::Raw("IFNULL(created_at, '' )")
                    )
                    ->get()->toArray();
                $cancel_counts = DB::table('bookings')->whereIn('send_ward_id', $this->getBookingScope())->whereBetween('created_at', [$date_from_report, $date_to_report])->where('status', 'cancel')->count();
                $data_report['data_rep'] = $price_by_cola;
                $data_report['data_title'] = ['Mã đơn hàng', 'Trạng thái', 'Người gửi', 'Người nhận', 'Nơi gửi', 'Nơi nhận', 'Giá', 'Ngày tạo'];
            } else {
                $cancel_counts = DB::table('bookings')->whereBetween('created_at', [$date_from_report, $date_to_report])->where('status', 'cancel')->count();

                $data_rep = DB::table('bookings')->whereNotNull('created_at')
                    ->where('status', '=', 'cancel')
                    ->whereBetween('created_at', [$date_from_report, $date_to_report])
                    ->select(
                        DB::Raw("IFNULL(uuid, '' )"),
                        DB::Raw("IFNULL(status, '' )"),
                        DB::Raw("IFNULL(send_name, '' )"),
                        DB::Raw("IFNULL(receive_name, '' )"),
                        DB::Raw("IFNULL(send_full_address, '' )"),
                        DB::Raw("IFNULL(receive_full_address, '' )"),
                        DB::Raw("IFNULL(price, '' )"),
                        DB::Raw("IFNULL(created_at, '' )")
                    )
                    ->get();

                $data_report['data_rep'] = $data_rep;
                $data_report['data_title'] = ['Mã đơn hàng', 'Trạng thái', 'Người gửi', 'Người nhận', 'Nơi gửi', 'Nơi nhận', 'Giá', 'Ngày tạo'];
            }
            $data_report['sum'] = $cancel_counts . ' ' . 'đơn hàng hủy';
        }
        if (isset($type_of_report) && $type_of_report == 9 && isset($date_from_report) && isset($date_to_report)) {
            if (Auth::user()->role != 'admin') {
                $db = DB::table('bookings')->whereIn('send_ward_id', $this->getBookingScope())
                    ->whereNotNull('completed_at')
                    ->whereBetween('completed_at', [$date_from_report, $date_to_report])
                    ->where('status', 'completed')
                    ->select(
                        DB::Raw("IFNULL(uuid, '' )"),
                        DB::Raw("IFNULL(status, '' )"),
                        DB::Raw("IFNULL(send_name, '' )"),
                        DB::Raw("IFNULL(receive_name, '' )"),
                        DB::Raw("IFNULL(send_full_address, '' )"),
                        DB::Raw("IFNULL(receive_full_address, '' )"),
                        DB::Raw("IFNULL(price, '' )"),
                        DB::Raw("IFNULL(completed_at, '' )")
                    );
                $amount = $db->sum('price');
                $data_rep = $db->get()->toArray();
                $data_report['data_rep'] = $data_rep;
                $data_report['data_title'] = ['Mã đơn hàng', 'Trạng thái', 'Người gửi', 'Người nhận', 'Nơi gửi', 'Nơi nhận', 'Giá', 'Ngày hoàn thành'];
            } else {
                $db = DB::table('bookings')->whereBetween('completed_at', [$date_from_report, $date_to_report])->where('status', 'completed');
                $amount = $db->sum('price');

                $data_rep = $db
                    ->select(['uuid', 'status', 'send_name', 'receive_name', 'send_full_address', 'receive_full_address', 'price', 'completed_at'])
                    ->get()->toArray();

                $data_report['data_rep'] = $data_rep;
                $data_report['data_title'] = ['Mã đơn hàng', 'Trạng thái', 'Người gửi', 'Người nhận', 'Nơi gửi', 'Nơi nhận', 'Giá', 'Ngày hoàn thành'];
            }
            $data_report['sum'] = count($data_rep) . ' ' . 'đơn hàng hoàn thành hôm nay. Tổng tiền cước: ' . number_format($amount) . ' VND';
        }
        if (isset($type_of_report) && $type_of_report == 10 && isset($date_from_report) && isset($date_to_report)) {
            if (Auth::user()->role != 'admin') {
                $db = DB::table('bookings')->whereIn('send_ward_id', $this->getBookingScope())
                    ->whereNotNull('completed_at')
                    ->whereBetween('completed_at', [$date_from_report, $date_to_report])
                    ->where('status', 'completed')
                    ->select(
                        DB::Raw("IFNULL(uuid, '' )"),
                        DB::Raw("IFNULL(status, '' )"),
                        DB::Raw("IFNULL(send_name, '' )"),
                        DB::Raw("IFNULL(receive_name, '' )"),
                        DB::Raw("IFNULL(send_full_address, '' )"),
                        DB::Raw("IFNULL(receive_full_address, '' )"),
                        DB::Raw("IFNULL(COD, '' )"),
                        DB::Raw("IFNULL(completed_at, '' )")
                    );
                $amount = $db->sum('COD');
                $data_rep = $db->get()->toArray();
                $data_report['data_rep'] = $data_rep;
                $data_report['data_title'] = ['Mã đơn hàng', 'Trạng thái', 'Người gửi', 'Người nhận', 'Nơi gửi', 'Nơi nhận', 'COD', 'Ngày hoàn thành'];
            } else {
                $db = DB::table('bookings')->whereBetween('completed_at', [$date_from_report, $date_to_report])->where('status', 'completed');
                $amount = $db->sum('COD');

                $data_rep = $db
                    ->select(['uuid', 'status', 'send_name', 'receive_name', 'send_full_address', 'receive_full_address', 'COD', 'completed_at'])
                    ->get()->toArray();

                $data_report['data_rep'] = $data_rep;
                $data_report['data_title'] = ['Mã đơn hàng', 'Trạng thái', 'Người gửi', 'Người nhận', 'Nơi gửi', 'Nơi nhận', 'COD', 'Ngày hoàn thành'];
            }
            $data_report['sum'] = count($data_rep) . ' ' . 'đơn hàng hoàn thành hôm nay. Tổng tiền COD: ' . number_format($amount) . ' VND';
        }
        return $data_report;
    }

    public function reportExport(Request $request)
    {
        $date_from_report = $request->input('date_from'). ' 00:00:00';
        $date_to_report = $request->input('date_to'). ' 23:59:59';
        $type_of_report = $request->input('type_export');
        $result = [];
        $num = 1;
        if ($request->input('type_export') == 1) {
            $file_name = 'Báo cáo khách hàng';
            $sheet_name = 'Khach_hang';
            $excel = 'khachhang';
            $data_collect = DB::table('users')->whereNotNull('created_at')
                ->where('status', 'active')
                ->whereBetween('created_at', [$date_from_report, $date_to_report])
                ->where('users.role', '=', 'customer')
                ->get()->toArray();

            foreach ($data_collect as $b) {
                $data['Stt'] = $num;
                $data['id'] = $b->id;
                $data['uuid'] = $b->uuid;
                $data['username'] = $b->username;
                $data['name'] = $b->name != null ? $b->name : '';
                $data['birth_day'] = $b->birth_day;
                $data['phone_number'] = $b->phone_number;
                $data['bank_account'] = $b->bank_account;
                $data['bank_account_number'] = $b->bank_account_number;
                $data['bank_name'] = $b->bank_name;
                $data['bank_branch'] = $b->bank_branch;
                $data['created_at'] = $b->created_at;
                $result[] = $data;
                $num++;
            }
        }
        if ($request->input('type_export') == 2) {
            $file_name = 'Báo cáo Shipper';
            $sheet_name = 'Shipper';
            $excel = 'shipper';
            $data_collect = DB::table('users')->whereNotNull('created_at')->where('status', '=', 'active')->where('role', '=', 'shipper')
                ->whereBetween('created_at', [$date_from_report, $date_to_report])
                ->get()->toArray();
            foreach ($data_collect as $b) {
                $data['Stt'] = $num;
                $data['uuid'] = $b->uuid;
                $data['name'] = $b->name != null ? $b->name : '';
                $data['phone_number'] = $b->phone_number;
                $data['created_at'] = $b->created_at;
                $result[] = $data;
                $num++;
            }
        }
        if ($request->input('type_export') == 3) {
            $file_name = 'Báo cáo đại lý';
            $sheet_name = 'Dai_ly';
            $excel = 'daily';
            $data_collect = Agency::whereNotNull('created_at')->whereBetween('created_at', [$date_from_report, $date_to_report])
                ->with('revenues', 'liabilities')->select('id', 'name', 'address', 'discount')->get();
            foreach ($data_collect as $b) {
                $data['Stt'] = $num;
                $data['id'] = $b->id;
                $data['name'] = $b->name != null ? $b->name : '';
                $data['address'] = $b->address;
                $data['discount'] = $b->discount;
                $data['collaborators'] = $b->collaborator_name;
                $data['total_revenue'] = $b->total_revenue;
                $data['agency_discount'] = $b->agency_discount;
                $data['turnover_paid'] = $b->turnover_paid;
                $data['discount_paid'] = $b->discount_paid;
                $result[] = $data;
                $num++;
            }
        }
        if ($request->input('type_export') == 4) {
            $file_name = 'Báo cáo tổng doanh thu';
            $sheet_name = 'Doanh_thu';
            $excel = 'doanhthu_admin';
            if (Auth::user()->role != 'admin') {
                $excel = 'daily';
                $scope = Collaborator::where('user_id', Auth::user()->id)->pluck('agency_id');
                $data_collect = Agency::whereNotNull('created_at')->whereBetween('created_at', [$date_from_report, $date_to_report])
                    ->whereIn('id', $scope)->with('revenues', 'liabilities')->select('id', 'name', 'address', 'discount')->groupBy('id')->get();
                foreach ($data_collect as $b) {
                    $data['Stt'] = $num;
                    $data['id'] = $b->id;
                    $data['name'] = $b->name != null ? $b->name : '';
                    $data['address'] = $b->address;
                    $data['discount'] = $b->discount;
                    $data['collaborators'] = $b->collaborator_name;
                    $data['total_revenue'] = $b->total_revenue;
                    $data['agency_discount'] = $b->agency_discount;
                    $data['turnover_paid'] = $b->turnover_paid;
                    $data['discount_paid'] = $b->discount_paid;
                    $result[] = $data;
                    $num++;
                }
            } else {
                $data_collect = DB::table('revenues')->whereBetween('created_at', [$date_from_report, $date_to_report])
                    ->select( [DB::raw('sum(booking_revenue) as total_revenue'), DB::raw('sum(agency_discount) as total_discount'), DB::raw('DATE(created_at) as day')])
                    ->groupBy('day')->get();
                foreach ($data_collect as $b) {
                    $data['Stt'] = $num;
                    $data['total_revenue'] = $b->total_revenue;
                    $data['total_discount'] = $b->total_discount;
                    $data['day'] = $b->day;
                    $result[] = $data;
                    $num++;
                }
            }

        }
        if ($request->input('type_export') == 5) {
            $file_name = 'Báo cáo tổng đơn hàng';
            $sheet_name = 'Don_hang';
            $excel = 'donhang';
            if (Auth::user()->role != 'admin') {
                $data_collect = DB::table('bookings')->whereIn('send_ward_id', $this->getBookingScope())///
                ->whereNotNull('created_at')
                    ->whereBetween('created_at', [$date_from_report, $date_to_report])
                    ->get()->toArray();
                foreach ($data_collect as $b) {
                    $data['Stt'] = $num;
                    $data['uuid'] = $b->uuid;
                    $data['status'] = $b->status;
                    $data['send_name'] = $b->send_name;
                    $data['receive_name'] = $b->receive_name;
                    $data['send_full_address'] = $b->send_full_address;
                    $data['receive_full_address'] = $b->receive_full_address;
                    $data['price'] = $b->price + $b->incurred;
                    $data['created_at'] = $b->created_at;
                    $result[] = $data;
                    $num++;
                }
            } else {
                $data_collect = DB::table('bookings')->whereNotNull('created_at')
                    ->whereBetween('created_at', [$date_from_report, $date_to_report])
                    ->get()->toArray();
                foreach ($data_collect as $b) {
                    $data['Stt'] = $num;
                    $data['uuid'] = $b->uuid;
                    $data['status'] = $b->status;
                    $data['send_name'] = $b->send_name;
                    $data['receive_name'] = $b->receive_name;
                    $data['send_full_address'] = $b->send_full_address;
                    $data['receive_full_address'] = $b->receive_full_address;
                    $data['price'] = $b->price + $b->incurred;
                    $data['created_at'] = $b->created_at;
                    $result[] = $data;
                    $num++;
                }
            }
        }
        if ($request->input('type_export') == 7) {
            $file_name = 'Báo cáo đơn hàng thành công';
            $sheet_name = 'Don_hang_thanh_cong';
            $excel = 'donhang_thanhcong';
            if (Auth::user()->role != 'admin') {
                $data_collect = DB::table('bookings')->whereIn('send_ward_id', $this->getBookingScope())
                    ->whereNotNull('created_at')
                    ->whereBetween('created_at', [$date_from_report, $date_to_report])
                    ->where('status', 'completed')
                    ->get()->toArray();
                foreach ($data_collect as $b) {
                    $data['Stt'] = $num;
                    $data['uuid'] = $b->uuid;
                    $data['status'] = $b->status;
                    $data['send_name'] = $b->send_name;
                    $data['receive_name'] = $b->receive_name;
                    $data['send_full_address'] = $b->send_full_address;
                    $data['receive_full_address'] = $b->receive_full_address;
                    $data['price'] = $b->price + $b->incurred;
                    $data['created_at'] = $b->created_at;
                    $result[] = $data;
                    $num++;
                }
            } else {
                $data_collect = DB::table('bookings')->whereNotNull('created_at')
                    ->where('status', 'completed')
                    ->whereBetween('created_at', [$date_from_report, $date_to_report])
                    ->get()->toArray();
                foreach ($data_collect as $b) {
                    $data['Stt'] = $num;
                    $data['uuid'] = $b->uuid;
                    $data['status'] = $b->status;
                    $data['send_name'] = $b->send_name;
                    $data['receive_name'] = $b->receive_name;
                    $data['send_full_address'] = $b->send_full_address;
                    $data['receive_full_address'] = $b->receive_full_address;
                    $data['price'] = $b->price + $b->incurred;
                    $data['created_at'] = $b->created_at;
                    $result[] = $data;
                    $num++;
                }
            }
        }
        if ($request->input('type_export') == 6) {
            $file_name = 'Báo cáo đơn hàng mới';
            $sheet_name = 'Don_hang_moi';
            $excel = 'donhang_moi';
            if (Auth::user()->role != 'admin') {
                $data_collect = DB::table('bookings')->whereIn('send_ward_id', $this->getBookingScope())
                    ->whereNotNull('created_at')
                    ->whereBetween('created_at', [$date_from_report, $date_to_report])
                    ->where('status', 'new')
                    ->get()->toArray();
                foreach ($data_collect as $b) {
                    $data['Stt'] = $num;
                    $data['uuid'] = $b->uuid;
                    $data['status'] = $b->status;
                    $data['send_name'] = $b->send_name;
                    $data['receive_name'] = $b->receive_name;
                    $data['send_full_address'] = $b->send_full_address;
                    $data['receive_full_address'] = $b->receive_full_address;
                    $data['price'] = $b->price + $b->incurred;
                    $data['created_at'] = $b->created_at;
                    $result[] = $data;
                    $num++;
                }
            } else {
                $data_collect = DB::table('bookings')->whereNotNull('created_at')
                    ->where('status', 'new')
                    ->whereBetween('created_at', [$date_from_report, $date_to_report])
                    ->get()->toArray();
                foreach ($data_collect as $b) {
                    $data['Stt'] = $num;
                    $data['uuid'] = $b->uuid;
                    $data['status'] = $b->status;
                    $data['send_name'] = $b->send_name;
                    $data['receive_name'] = $b->receive_name;
                    $data['send_full_address'] = $b->send_full_address;
                    $data['receive_full_address'] = $b->receive_full_address;
                    $data['price'] = $b->price + $b->incurred;
                    $data['created_at'] = $b->created_at;
                    $result[] = $data;
                    $num++;
                }
            }
        }
        if ($request->input('type_export') == 8) {
            $file_name = 'Báo cáo đơn hàng hủy';
            $sheet_name = 'Don_hang_huy';
            $excel = 'donhang_huy';
            if (Auth::user()->role != 'admin') {
                $data_collect = DB::table('bookings')->whereIn('send_ward_id', $this->getBookingScope())
                    ->whereNotNull('created_at')
                    ->whereBetween('created_at', [$date_from_report, $date_to_report])
                    ->where('status', 'cancel')
                    ->get()->toArray();
                foreach ($data_collect as $b) {
                    $data['Stt'] = $num;
                    $data['uuid'] = $b->uuid;
                    $data['status'] = $b->status;
                    $data['send_name'] = $b->send_name;
                    $data['receive_name'] = $b->receive_name;
                    $data['send_full_address'] = $b->send_full_address;
                    $data['receive_full_address'] = $b->receive_full_address;
                    $data['price'] = $b->price + $b->incurred;
                    $data['created_at'] = $b->created_at;
                    $result[] = $data;
                    $num++;
                }
            } else {
                $data_collect = DB::table('bookings')->whereNotNull('created_at')
                    ->where('status', 'cancel')
                    ->whereBetween('created_at', [$date_from_report, $date_to_report])
                    ->get()->toArray();
                foreach ($data_collect as $b) {
                    $data['Stt'] = $num;
                    $data['uuid'] = $b->uuid;
                    $data['status'] = $b->status;
                    $data['send_name'] = $b->send_name;
                    $data['receive_name'] = $b->receive_name;
                    $data['send_full_address'] = $b->send_full_address;
                    $data['receive_full_address'] = $b->receive_full_address;
                    $data['price'] = $b->price + $b->incurred;
                    $data['created_at'] = $b->created_at;
                    $result[] = $data;
                    $num++;
                }
            }
        }
        if ($request->input('type_export') == 9) {
            $file_name = 'Báo cáo đơn hàng hoàn thành hôm nay';
            $sheet_name = 'Don_hang_hoan_thanh_hom_nay';
            $excel = 'donhang_hoanthanh_homnay';
            if (Auth::user()->role != 'admin') {
                $data_collect = DB::table('bookings')->whereIn('send_ward_id', $this->getBookingScope())
                    ->whereNotNull('completed_at')
                    ->whereBetween('completed_at', [$date_from_report, $date_to_report])
                    ->where('status', 'completed')
                    ->get()->toArray();
                foreach ($data_collect as $b) {
                    $data['Stt'] = $num;
                    $data['uuid'] = $b->uuid;
                    $data['status'] = $b->status;
                    $data['send_name'] = $b->send_name;
                    $data['receive_name'] = $b->receive_name;
                    $data['send_full_address'] = $b->send_full_address;
                    $data['receive_full_address'] = $b->receive_full_address;
                    $data['price'] = $b->price + $b->incurred;
                    $data['created_at'] = $b->created_at;
                    $result[] = $data;
                    $num++;
                }
            } else {
                $data_collect = DB::table('bookings')->whereNotNull('completed_at')
                    ->where('status', 'completed')
                    ->whereBetween('completed_at', [$date_from_report, $date_to_report])
                    ->get()->toArray();
                foreach ($data_collect as $b) {
                    $data['Stt'] = $num;
                    $data['uuid'] = $b->uuid;
                    $data['status'] = $b->status;
                    $data['send_name'] = $b->send_name;
                    $data['receive_name'] = $b->receive_name;
                    $data['send_full_address'] = $b->send_full_address;
                    $data['receive_full_address'] = $b->receive_full_address;
                    $data['price'] = $b->price + $b->incurred;
                    $data['created_at'] = $b->created_at;
                    $result[] = $data;
                    $num++;
                }
            }
        }
        if ($request->input('type_export') == 10) {
            $file_name = 'Báo cáo đơn hàng hoàn thành hôm nay';
            $sheet_name = 'Don_hang_hoan_thanh_hom_nay';
            $excel = 'donhang_hoanthanh_homnay_COD';
            if (Auth::user()->role != 'admin') {
                $data_collect = DB::table('bookings')->whereIn('send_ward_id', $this->getBookingScope())
                    ->whereNotNull('completed_at')
                    ->whereBetween('completed_at', [$date_from_report, $date_to_report])
                    ->where('status', 'completed')
                    ->get()->toArray();
                foreach ($data_collect as $b) {
                    $data['Stt'] = $num;
                    $data['uuid'] = $b->uuid;
                    $data['status'] = $b->status;
                    $data['send_name'] = $b->send_name;
                    $data['receive_name'] = $b->receive_name;
                    $data['send_full_address'] = $b->send_full_address;
                    $data['receive_full_address'] = $b->receive_full_address;
                    $data['COD'] = number_format($b->COD);
                    $data['created_at'] = $b->created_at;
                    $result[] = $data;
                    $num++;
                }
            } else {
                $data_collect = DB::table('bookings')->whereNotNull('completed_at')
                    ->where('status', 'completed')
                    ->whereBetween('completed_at', [$date_from_report, $date_to_report])
                    ->get()->toArray();
                foreach ($data_collect as $b) {
                    $data['Stt'] = $num;
                    $data['uuid'] = $b->uuid;
                    $data['status'] = $b->status;
                    $data['send_name'] = $b->send_name;
                    $data['receive_name'] = $b->receive_name;
                    $data['send_full_address'] = $b->send_full_address;
                    $data['receive_full_address'] = $b->receive_full_address;
                    $data['COD'] = number_format($b->COD);
                    $data['created_at'] = $b->created_at;
                    $result[] = $data;
                    $num++;
                }
            }
        }

        $file_path = public_path('excel_temp/' . $excel . '.xlsx');
        Excel::load($file_path, function ($reader) use ($result, $request) {
            $reader->skipRows(3);

            $reader->sheet('Report', function ($sheet) use ($result, $request) {
                $sheet->cell('D2', function ($cell) use ($request) {
                    $cell->setValue($request->date_from);
                });
                $sheet->cell('D3', function ($cell) use ($request) {
                    $cell->setValue($request->date_to);
                });
                $sheet->fromArray($result, null, 'B7', true, false);
            });

        })->setFilename($file_name)->export('xlsx');
        // $data = $this->get_report();
        // return $data;
    }
}
