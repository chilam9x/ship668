<?php

namespace App\Http\Controllers\Admin\Setting;

use App\Models\Policy;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PolicyController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    protected $breadcrumb = ['Quản lý đồng hành cùng bạn', 'Đồng hành cùng bạn'];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $req)
    {
        $policy = Policy::where('id', '>', 0)->first();
        return view('admin.elements.policies.index', ['active' => 'policies', 'breadcrumb' => $this->breadcrumb, 'policy' => $policy]);
    }

    public function add(Request $req) {
        if ($req->isMethod('post')) {
            $policy = new Policy;
            $policy->content = $req->content;
            $policy->save();
            return redirect(url('/admin/policies'))->with('success', 'Thêm mới đồng hành cùng bạn thành công');
        }
        return view('admin.elements.policies.add', ['active' => 'policies', 'breadcrumb' => $this->breadcrumb]);
    }

    public function edit(Request $req, $id) {
        $policy = Policy::find($id);
        if ($req->isMethod('post')) {
            $policy->content = $req->content;
            $policy->save();
            return redirect(url('/admin/policies'))->with('success', 'Chỉnh sửa đồng hành cùng bạn thành công');
        }
        return view('admin.elements.policies.edit', ['active' => 'policies', 'breadcrumb' => $this->breadcrumb, 'policy' => $policy]);
    }

    public function delete(Request $req, $id) {
        $policy = Policy::find($id);
        if (!empty($policy)) {
            if ($policy->delete()) {
                return redirect(url('/admin/policies'))->with('success', 'Xóa đồng hành cùng bạn thành công');   
            }
            return redirect(url('/admin/policies'))->with('success', 'Xóa đồng hành cùng bạn thất bại');   
        }
        return redirect(url('/admin/policies'))->with('success', 'Chính sách không tồn tại');
    }
}
