<?php

namespace App\Http\Controllers\Admin\Setting;

use App\Http\Requests\VersionRequest;
use App\Models\Version;
use function dd;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use function redirect;
use function url;

class VersionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected $breadcrumb = ['Quản lý version'];

    public function index()
    {
        return view('admin.elements.version.index', ['active' => 'version', 'breadcrumb' => $this->breadcrumb]);
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
        $ver = Version::find($id);
        return view('admin.elements.version.add', ['active' => 'version', 'breadcrumb' => $this->breadcrumb, 'version' => $ver]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(VersionRequest $request, $id)
    {
        $ver = Version::find($id);
        $ver->version_code = $request->version_code;
        $ver->version_name = $request->version_name;
        $ver->force_upgrade = $request->force_upgrade;
        $ver->description = $request->description;
        $ver->save();
        return redirect(url('/admin/versions'))->with('success', 'Chỉnh sửa thông tin phiên bản thành công');
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
