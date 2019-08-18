<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Requests\PartnerRequest;
use App\Models\PartnerAPI;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;

class PartnerController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    protected $breadcrumb = ['Quản lý đối tác & khách hàng','đối tác'];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.elements.users.partner.index', ['active' => 'partner', 'breadcrumb' => $this->breadcrumb]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.elements.users.partner.add', ['active' => 'partner', 'breadcrumb' => $this->breadcrumb]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PartnerRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = new User();
            $data->name = $request->name;
            $data->email = $request->email;
            $data->province_id = $request->province_id;
            $data->district_id = $request->district_id;
            $data->ward_id = $request->ward_id;
            $data->home_number = $request->home_number;
            $data->phone_number = $request->phone_number;
            $data->role = 'partner';
            if ($request->hasFile('avatar')) {
                $file = $request->avatar;
                $filename = date('Ymd-His-') . $file->getFilename() . '.' . $file->extension();
                $filePath = 'img/avatar/';
                $movePath = public_path($filePath);
                $file->move($movePath, $filename);
                $data->avatar = $filePath . $filename;
            }
            $data->save();
            $api = new PartnerAPI();
            $api->partner_id = $data->id;
            $api->api_content = $request->api_content;
            $api->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
        return redirect(url('admin/partners'))->with('success', 'Thêm mới đối tác thành công');
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
        $partner = DB::table('users')->join('partner_a_p_is', 'users.id', '=', 'partner_a_p_is.partner_id')->where('users.id', $id)->first();
        return view('admin.elements.users.partner.add', ['active' => 'partner', 'partner' => $partner, 'breadcrumb' => $this->breadcrumb]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(PartnerRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = User::find($id);
            $data->name = $request->name;
            $data->email = $request->email;
            $data->province_id = $request->province_id;
            $data->district_id = $request->district_id;
            $data->ward_id = $request->ward_id;
            $data->home_number = $request->home_number;
            $data->phone_number = $request->phone_number;
            $data->role = 'partner';
            if ($request->hasFile('avatar')) {
                $file = $request->avatar;
                $filename = date('Ymd-His-') . $file->getFilename() . '.' . $file->extension();
                $filePath = 'img/avatar/';
                $movePath = public_path($filePath);
                $file->move($movePath, $filename);
                $data->avatar = $filePath . $filename;
            }
            $data->save();
            $api = PartnerAPI::where('partner_id', $id)->first();
            $api->api_content = $request->api_content;
            $api->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
        return redirect(url('admin/partners'))->with('success', 'Thêm mới đối tác thành công');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            User::find($id)->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
        return redirect(url('admin/partners'))->with('delete', 'Xóa đối tác thành công');
    }
}
