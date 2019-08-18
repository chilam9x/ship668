<?php

namespace App\Http\Controllers\Admin\Setting;

use App\Http\Requests\DiscountRequest;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use function dd;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use function redirect;
use function url;

class DiscountController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    protected $breadcrumb = ['Quản lý chiết khấu'];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.elements.discount.index', ['active' => 'discount', 'breadcrumb' => $this->breadcrumb]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.elements.discount.add', ['active' => 'discount', 'breadcrumb' => $this->breadcrumb]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(DiscountRequest $request)
    {
        DB::beginTransaction();
        try {
            $key = strtolower($request->key);
            $data = new Setting();
            $data->type = $request->type;
            $data->key = str_replace(' ', '_', $key);
            $data->name = $request->name;
            $data->value = $request->value;
            $data->description = $request->description;
            $data->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
        return redirect(url('admin/discounts'))->with('success', 'Thêm mới chiết khấu thành công');
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
        $data = Setting::find($id);
        return view('admin.elements.discount.add', ['active' => 'discount', 'discount' => $data, 'breadcrumb' => $this->breadcrumb]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(DiscountRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $key = strtolower($request->key);
            $data = Setting::find($id);
            $data->type = $request->type;
            $data->key = str_replace(' ', '_', $key);
            $data->name = $request->name;
            $data->value = $request->value;
            $data->description = $request->description;
            $data->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
        return redirect(url('admin/discounts'))->with('success', 'Cập nhật chiết khấu thành công');
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
            $data = Setting::find($id);
            $data->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
        return redirect(url('admin/discounts'))->with('delete', 'Xóa chiết khấu thành công');
    }
}
