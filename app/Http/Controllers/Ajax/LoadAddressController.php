<?php

namespace App\Http\Controllers\Ajax;

use App\Models\District;
use App\Models\Province;
use App\Models\ManagementWardScope;
use App\Models\Ward;
use function dd;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class LoadAddressController extends Controller
{
    public function getProvince()
    {
        return Province::orderBy('name', 'asc')->get();
    }

    public function getDistrict($id)
    {
        return District::where('provinceId', $id)->orderBy('name', 'asc')->get();
    }

    public function getWard($id)
    {
        return Ward::where('districtId', $id)->orderBy('name', 'asc')->get();
    }

    public function getWardScope(Request $req)
    {
        $ward = Ward::whereIn('districtId', $req->data)->orderBy('name', 'asc')->pluck('id');
        $exist_ward = ManagementWardScope::where('agency_id', '!=', $req->agency_id)->whereIn('ward_id', $ward)->pluck('ward_id');
        $data = Ward::whereIn('districtId', $req->data)->whereNotIn('id', $exist_ward)->orderBy('name', 'asc')->get();
        return $data;
    }

    public function loadDataDistrict($id)
    {
        $district = DB::table('districts')->join('district_types', 'districts.district_type', '=', 'district_types.id')
            ->where('districts.provinceId', $id)->select('districts.id', 'districts.name as district_name', 'district_types.name as district_type_name', 'districts.allow_booking')->get();
        return $district;
    }

    public function changeType(Request $req)
    {
        DB::beginTransaction();
        try {
            $district = District::find($req->district_id);
            $district->district_type = $req->district_type;
            $district->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
        return 'success';
    }

    public function changeProvinceType(Request $request)
    {
        DB::beginTransaction();
        try {
            $pr = Province::find($request->id);
            if ($request->type == 'type') {
                $pr->province_type = $pr->province_type == 1 ? 0 : 1;
            }else {
                $pr->active = $pr->active == 1 ? 0 : 1;
            }
            $pr->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
        return $request->type == 'type' ? $pr->province_type : $pr->active;
    }

    public function checkProvince(Request $request)
    {
        $pr = Province::find($request->id);
        return $request->type == 'type' ? $pr->province_type : $pr->active;
    }
}
