<?php

namespace App\Http\Controllers\Ajax;

use App\Models\Collaborator;
use App\Models\CollaboratorRegister;
use App\Models\District;
use App\Models\Feedback;
use App\Models\ManagementWardScope;
use App\Models\Province;
use App\Models\ShipperRegister;
use App\Models\Ward;
use Form;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use function url;

class RegisterController extends Controller
{
    public function feedback()
    {
        $partner = Feedback::all();
        return datatables()->of($partner)
            ->addColumn('action', function ($user) {
                $action = [];
                $action[] = '<a class="btn btn-xs btn-danger" href="'.url('/admin/feedback/delete/').'/'.$user->id.'"><i class="fa fa-trash-o"></i> Xóa</a>';
                return implode(' ', $action);
            })
            ->make(true);
    }
    public function shipper()
    {
        $partner = ShipperRegister::all();
        if(Auth::user()->role == 'collaborators'){
            $user_id = Auth::user()->id;
            $scope = Collaborator::where('user_id', $user_id)->pluck('agency_id');
            $ward = ManagementWardScope::whereIn('agency_id', $scope)->pluck('ward_id');
            $partner = ShipperRegister::whereIn('ward_id', $ward)->get();
        }
        return datatables()->of($partner)
            ->addColumn('action', function ($user) {
                $action = [];
                $action[] = '<div style="float: left">' . Form::open(['method' => 'DELETE', 'url' => ['admin/register/shippers/' . $user->id]]) .
                    '<button class="btn btn-xs btn-danger" type="submit"><i class="fa fa-trash-o"></i> Xóa</button>' .
                    Form::close() . '</div>';
                return implode(' ', $action);
            })
            ->addColumn('address', function ($user) {
                $province_name = Province::find($user->province_id)->name;
                $district_name = District::find($user->district_id)->name;
                $ward_name = Ward::find($user->ward_id)->name;;
                return $user->home_number . ', ' . $ward_name . ', ' . $district_name . '. ' . $province_name;
            })
            ->make(true);
    }
    public function agency()
    {
        $partner = CollaboratorRegister::with('agencies')->get();
        return datatables()->of($partner)
            ->addColumn('action', function ($user) {
                $action = [];
                $action[] = '<div style="float: left">' . Form::open(['method' => 'DELETE', 'url' => ['admin/register/agency/' . $user->id]]) .
                    '<button class="btn btn-xs btn-danger" type="submit"><i class="fa fa-trash-o"></i> Xóa</button>' .
                    Form::close() . '</div>';
                return implode(' ', $action);
            })
            ->addColumn('address', function ($user) {
                $province_name = Province::find($user->province_id)->name;
                $district_name = District::find($user->district_id)->name;
                $ward_name = Ward::find($user->ward_id)->name;;
                return $user->home_number . ', ' . $ward_name . ', ' . $district_name . '. ' . $province_name;
            })
            ->addColumn('agency_address', function ($user) {
                $province_name = Province::find($user->agencies->province_id)->name;
                $district_name = District::find($user->agencies->district_id)->name;
                $ward_name = Ward::find($user->agencies->ward_id)->name;;
                return $user->home_number . ', ' . $ward_name . ', ' . $district_name . '. ' . $province_name;
            })
            ->make(true);
    }
}
