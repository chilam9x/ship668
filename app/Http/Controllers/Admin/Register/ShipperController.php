<?php

namespace App\Http\Controllers\Admin\Register;

use App\Models\Feedback;
use App\Models\ShipperRegister;
use function dd;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use function redirect;
use function view;

class ShipperController extends Controller
{
    protected $breadcrumb = ['Quản lý lượt đăng lý mới', 'shipper'];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getFeedback()
    {
        $this->breadcrumb = ['Phản hồi'];
        return view('admin.elements.feedback.index', ['active' => 'feedback', 'breadcrumb' => $this->breadcrumb]);
    }

    public function deleteFeedback($id)
    {
        DB::beginTransaction();
        try {
            Feedback::find($id)->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
        return redirect(url('admin/feedback'))->with('delete', 'Xóa phản hồi thành công');
    }

    public function index()
    {
        return view('admin.elements.register.shipper.index', ['active' => 'shipper_register', 'breadcrumb' => $this->breadcrumb]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
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
            ShipperRegister::find($id)->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
        return redirect(url('admin/register/shippers'))->with('delete', 'Xóa shipper đăng ký thành công');

    }
}
