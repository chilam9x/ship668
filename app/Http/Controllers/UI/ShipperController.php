<?php

namespace App\Http\Controllers\UI;

use App\Http\Requests\FrontEnt\ShipperResquest;
use App\Models\ShipperRegister;
use function dd;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ShipperController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('front-ent.element.shipper');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ShipperResquest $request)
    {
        DB::beginTransaction();
        try {
            $data = new ShipperRegister();
            $data->name = $request->name;
            $data->email = $request->email;
            $data->birth_day = $request->birth_day;
            $data->phone_number = $request->phone_number;
            $data->province_id = $request->province_id;
            $data->district_id = $request->district_id;
            $data->ward_id = $request->ward_id;
            $data->home_number = $request->home_number;
            $data->id_number = $request->id_number;
            $data->bank_account = $request->bank_account;
            $data->bank_account_number = $request->bank_account_number;
            $data->bank_name = $request->bank_name;
            $data->bank_branch = $request->bank_branch;
            $data->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
        }
        $request->session()->flash('success', 'Đăng ký tài khoản  Shipper thành công! Vui lòng chờ hệ thống liên hệ!');
        return redirect(url('/'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
