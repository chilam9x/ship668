<?php

namespace App\Http\Controllers\Ajax;

use App\Models\DeliveryAddress;
use App\Models\District;
use App\Models\Province;
use App\Models\Ward;
use App\Models\User;
use function asset;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DeliveryAddressController extends Controller
{
    public function getDelivery($id)
    {
        $data = DeliveryAddress::where('user_id', $id)->get();
        return datatables()->of($data)
            ->addColumn('full_address', function ($d) {
                $province_name = Province::find($d->province_id)->name;
                $district_name = District::find($d->district_id)->name;
                $ward_name = Ward::find($d->ward_id)->name;
                return $d->home_number . ', ' . $ward_name . ', ' . $district_name . ', ' . $province_name;
            })
            ->addColumn('action', function ($d) use ($id) {
                $action = [];
                $action[] = '<a style="float:left" href="' . url('admin/customers/delete_delivery/' . $d->id) . '" class="btn btn-xs btn-danger"><i class="fa fa-trash-o"></i> Xóa</a>';
                return implode(' ', $action);
            })
            ->editColumn('default', function ($d) {
                return $d->default == 1 ? '<img src="' . asset('/img/corect.png') . '" width="30px"></img>' : '<img onclick="changeDefault(' . $d->id . ')" src="' . asset('/img/incorect.png') . '" width="30px"></img>';
            })
            ->rawColumns(['action', 'default'])
            ->make(true);
    }

    public function createDelivery(Request $request, $id)
    {
        if ($request->home_number == '') {
            return 'error';
        }
        DB::beginTransaction();
        try {
            $delivery = new DeliveryAddress();
            $delivery->user_id = $id;
            $delivery->province_id = $request->province;
            $delivery->district_id = $request->district;
            $delivery->ward_id = $request->ward;
            $delivery->home_number = $request->home_number;
            $delivery->default = (isset($request->default) && $request->default == 1) ? $request->default : 0;
            $delivery->save();
            $lastId = $delivery->id;
            if (isset($request->default) && $request->default == 1) {
                DeliveryAddress::where('id', '!=', $lastId)->update(['default' => 0]);
                
                //cập nhật địa mặc định cho table "user"
                User::where('id', $delivery->user_id)->update(array(
                    'province_id' => $delivery->province_id,
                    'district_id' => $delivery->district_id,
                    'ward_id' => $delivery->ward_id,
                    'home_number' => $delivery->home_number,
                ));
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
        return 'success';
    }

    public function seDefaultDelivery($id)
    {
        DB::beginTransaction();
        try {
            $delivery = DeliveryAddress::find($id);
            $delivery->default = 1;
            $delivery->save();
            DeliveryAddress::where('id', '!=', $id)->update(['default' => 0]);

            //cập nhật địa mặc định cho table "user"
            User::where('id', $delivery->user_id)->update(array(
                'province_id' => $delivery->province_id,
                'district_id' => $delivery->district_id,
                'ward_id' => $delivery->ward_id,
                'home_number' => $delivery->home_number,
            ));
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
        return 'success';
    }

    public function deleteDelivery($id) {
        DB::beginTransaction();
        try {
            $delivery = DeliveryAddress::find($id);
            $delivery->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
        return 'success';
    }
}
