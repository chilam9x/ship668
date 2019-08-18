<?php

namespace App\Http\Controllers\Admin\User;

use App\Helpers\GoogleMapsHelper;
use App\Http\Requests\AgencyRequest;
use App\Models\Agency;
use App\Models\Collaborator;
use App\Models\District;
use App\Models\ManagementScope;
use App\Models\ManagementWardScope;
use App\Models\Province;
use App\Models\Shipper;
use App\Models\User;
use App\Models\Ward;
use Carbon\Carbon;
use function dd;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use function json_decode;
use function redirect;
use function url;

class AgencyController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    protected $breadcrumb = ['Quản lý thành viên', 'Đại lý'];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getLiabilities($id)
    {
        return view('admin.elements.users.agency.liabilities', ['active' => 'agency','id' => $id, 'breadcrumb' => $this->breadcrumb]);
    }

    public function index()
    {
        return view('admin.elements.users.agency.index', ['active' => 'agency', 'breadcrumb' => $this->breadcrumb]);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $collaborator = User::where('role', 'collaborators')->where('delete_status', 0)->get();
        return view('admin.elements.users.agency.add', ['collaborators' => $collaborator, 'selected_col' => [], 'active' => 'agency', 'breadcrumb' => $this->breadcrumb]);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(AgencyRequest $request)
    {
        $province_name = Province::find($request->province_id)->name;
        $district_name = District::find($request->district_id)->name;
        $ward_name = Ward::find($request->ward_id)->name;
        $mapResults = GoogleMapsHelper::lookUpInfoFromAddress($province_name . ' ' . $district_name . ' ' . $ward_name . ' ' . $request->home_number);
        if (isset($mapResults->geometry)) {
            $location = isset($mapResults->geometry->location) ? $mapResults->geometry->location : null;
        }
        DB::beginTransaction();
        try {
            $data = new Agency();
            $data->name = $request->name;
            $data->discount = $request->discount;
            $data->phone = $request->phone;
            $data->address = $request->home_number . ', ' . $ward_name . ', ' . $district_name . ', ' . $province_name;
            $data->province_id = $request->province_id;
            $data->district_id = $request->district_id;
            $data->ward_id = $request->ward_id;
            $data->home_number = $request->home_number;
            if ($location != null) {
                $data->lat = $location->lat;
                $data->lng = $location->lng;
            }
            $data->created_at = Carbon::now();
            $data->updated_at = Carbon::now();
            $data->save();
            foreach ($request->collaborator as $col) {
                Collaborator::insert([
                    'user_id' => $col,
                    'agency_id' => $data->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            foreach ($request->scope as $s) {
                ManagementScope::insert([
                    'agency_id' => $data->id,
                    'district_id' => $s
                ]);
            }
            if ($request->ward_scope == null) {
                $ward_check = Ward::whereIn('districtId', $request->scope)->orderBy('name', 'asc')->pluck('id');
                $exist_ward = ManagementWardScope::whereIn('ward_id', $ward_check)->pluck('ward_id');
                $ward = Ward::whereIn('districtId', $request->scope)->whereNotIn('id', $exist_ward)->pluck('id');
                foreach ($ward as $w) {
                    ManagementWardScope::insert([
                        'agency_id' => $data->id,
                        'ward_id' => $w
                    ]);
                }
            } else {
                foreach ($request->ward_scope as $ws) {
                    ManagementWardScope::insert([
                        'agency_id' => $data->id,
                        'ward_id' => $ws
                    ]);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
        }
        return redirect(url('admin/agencies'))->with('success', 'Thêm mới đại lý thành công');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $collaborator = User::where('role', 'collaborators')->where('delete_status', 0)->get();
        $agency = Agency::find($id);
        $scope = ManagementScope::where('agency_id', $id)->pluck('district_id');
        $ward_scope = ManagementWardScope::where('agency_id', $id)->pluck('ward_id');
        $sc = Collaborator::select('user_id')->where('agency_id', $id)->get()->toArray();
        $selected_col = [];
        foreach ($sc as $d) {
            $selected_col[] = $d['user_id'];
        }
        return view('admin.elements.users.agency.add', ['collaborators' => $collaborator, 'scope' => $scope, 'ward_scope' => $ward_scope,
            'selected_col' => $selected_col, 'agency' => $agency, 'active' => 'agency', 'breadcrumb' => $this->breadcrumb]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(AgencyRequest $request, $id)
    {
        $province_name = Province::find($request->province_id)->name;
        $district_name = District::find($request->district_id)->name;
        $ward_name = Ward::find($request->ward_id)->name;
        $mapResults = GoogleMapsHelper::lookUpInfoFromAddress($province_name . ' ' . $district_name . ' ' . $ward_name . ' ' . $request->home_number);
        if (isset($mapResults->geometry)) {
            $location = isset($mapResults->geometry->location) ? $mapResults->geometry->location : null;
        }
        DB::beginTransaction();
        try {
            $data = Agency::find($id);
            $data->name = $request->name;
            $data->phone = $request->phone;
            $data->discount = $request->discount;
            $data->address = $request->home_number . ', ' . $ward_name . ', ' . $district_name . ', ' . $province_name;
            $data->province_id = $request->province_id;
            $data->district_id = $request->district_id;
            $data->ward_id = $request->ward_id;
            $data->home_number = $request->home_number;
            if ($location != null) {
                $data->lat = $location->lat;
                $data->lng = $location->lng;
            }
            $data->updated_at = Carbon::now();
            $data->save();
            Collaborator::where('agency_id', $id)->delete();
            foreach ($request->collaborator as $col) {
                Collaborator::insert([
                    'user_id' => $col,
                    'agency_id' => $data->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
            ManagementScope::where('agency_id', $id)->delete();
            foreach ($request->scope as $s) {
                ManagementScope::insert([
                    'agency_id' => $data->id,
                    'district_id' => $s
                ]);
            }
            ManagementWardScope::where('agency_id', $id)->delete();
            if ($request->ward_scope == null) {
                $ward = Ward::whereIn('districtId', $request->scope)->pluck('id');
                foreach ($ward as $w) {
                    ManagementWardScope::insert([
                        'agency_id' => $data->id,
                        'ward_id' => $w
                    ]);
                }
            } else {
                foreach ($request->ward_scope as $ws) {
                    ManagementWardScope::insert([
                        'agency_id' => $data->id,
                        'ward_id' => $ws
                    ]);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
        return redirect(url('admin/agencies'))->with('success', 'Cập nhật đại lý thành công');
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
            Collaborator::where('agency_id', $id)->delete();
            ManagementScope::where('agency_id', $id)->delete();
            ManagementWardScope::where('agency_id', $id)->delete();
            Shipper::where('agency_id', $id)->delete();
            Agency::find($id)->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
        return redirect(url('admin/agencies'))->with('delete', 'Xóa đại lý thành công');

    }
}
