<?php

namespace App\Http\Controllers\Admin\COD;

use App\Models\Booking;
use App\Models\Collaborator;
use App\Models\User;
use Carbon\Carbon;
use function dd;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use \Auth;
use Illuminate\Support\Facades\DB;

class CODController extends Controller
{
    protected $breadcrumb = ['Quản lý đối tác & khách hàng'];

    public function getCOD()
    {
        $this->breadcrumb[] = 'danh sách';
        return view('admin.elements.COD.index', ['active' => 'cod', 'breadcrumb' => $this->breadcrumb]);
    }

    public function getCODDetails($id)
    {
        $booking = Booking::where('sender_id', $id)->where('status', 'completed')->where('COD', '>', 0)->where('COD_status', 'pending');
        if(Auth::user()->role == 'collaborators'){
            $user_id = Auth::user()->id;
            $scope = Collaborator::where('user_id', $user_id)->pluck('agency_id');
            $booking = $booking->whereIn('last_agency', $scope);
        }
        $booking = $booking->count();
        $this->breadcrumb[] = 'khách hàng';
        return view('admin.elements.COD.details', ['id' => $id, 'active' => 'customer', 'count' => $booking, 'breadcrumb' => $this->breadcrumb]);
    }

    public function paidCOD($id)
    {
        DB::beginTransaction();
        try {
            $check = User::where('id', $id)->first();
            if ($check){
                $booking =  Booking::where('sender_id', $check->id)->where('COD' , '>', 0)->where('status', 'completed')->where('COD_status', 'pending');
                if(Auth::user()->role == 'collaborators') {
                    $scope = Collaborator::where('user_id', Auth::user()->id)->pluck('agency_id');
                    $booking = $booking->whereIn('last_agency', $scope);
                    $cod = $booking->sum('COD');
                    $check->total_COD -= $cod;
                }
                else{
                    $check->total_COD = 0;
                }
                $booking->update(['COD_status' => 'finish', 'payment_date' => Carbon::now()->toDateTimeString()]);
                $check->save();
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
        }
        return redirect()->back()->with('success', 'Thanh toán toàn bộ phí thu hộ của khách hàng '.$check->name.' thành công');
    }
}
