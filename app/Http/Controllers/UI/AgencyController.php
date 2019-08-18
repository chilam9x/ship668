<?php

namespace App\Http\Controllers\UI;

use App\Http\Requests\FrontEnt\AgencyRequest;
use App\Models\AgencyRegister;
use App\Models\CollaboratorRegister;
use function dd;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use function view;

class AgencyController extends Controller
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
        return view('front-ent.element.agency');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AgencyRequest $request)
    {
        DB::beginTransaction();
        try {
            $col = new CollaboratorRegister();
            $col->name = $request->name;
            $col->email = $request->email;
            $col->birth_day = $request->birth_day;
            $col->phone_number = $request->phone_number;
            $col->province_id = $request->province_id;
            $col->district_id = $request->district_id;
            $col->ward_id = $request->ward_id;
            $col->home_number = $request->home_number;
            $col->id_number = $request->id_number;
            $col->bank_account = $request->bank_account;
            $col->bank_account_number = $request->bank_account_number;
            $col->bank_name = $request->bank_name;
            $col->bank_branch = $request->bank_branch;
            $col->save();
            $agency = new AgencyRegister();
            $agency->col_id = $col->id;
            $agency->name = $request->agency_name;
            $agency->phone_number = $request->hot_line;
            $agency->province_id = $request->agency_province;
            $agency->district_id = $request->agency_district;
            $agency->ward_id = $request->agency_ward;
            $agency->home_number = $request->agency_home_number;
            $agency->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
        }
        $request->session()->flash('success', 'Đăng ký đại lý thành công! Vui lòng chờ hệ thống liên hệ!');
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
