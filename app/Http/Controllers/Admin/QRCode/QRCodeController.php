<?php

namespace App\Http\Controllers\Admin\QRCode;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\QRCode;
class QRCodeController extends Controller
{
    protected $breadcrumb = ['Quản lý qrcode'];
    public function __construct()
    {
        $this->middleware('admin');
    }
    public function index()
    {
        $countQrcodeUsed=QRCode::countQrcodeUsed();
        $countQrcodeUnused=QRCode::countQrcodeUnused();
        $qrcode=QRCode::getList();
        return view('admin.elements.qrcode.index', ['qrcode'=>$qrcode,'countQrcodeUsed'=>$countQrcodeUsed,'countQrcodeUnused'=>$countQrcodeUnused,'active' => 'qrcode','breadcrumb' => $this->breadcrumb]);
    }
    public function find(Request $request)
    {
        $countQrcodeUsed=QRCode::countQrcodeUsed();
        $countQrcodeUnused=QRCode::countQrcodeUnused();
        $qrcode=QRCode::find($request->name);
        return view('admin.elements.qrcode.index', ['qrcode'=>$qrcode,'countQrcodeUsed'=>$countQrcodeUsed,'countQrcodeUnused'=>$countQrcodeUnused,'active' => 'qrcode','breadcrumb' => $this->breadcrumb]);
    }
    public function postCreate(Request $request)
    {
        $res = QRCode::postCreate($request->qrcode);
        return view('admin.elements.qrcode.print', ['qrcode' => $res]);
    }
    public function print()
    {
        $res = QRCode::getQRCodeListUnused();
        return view('admin.elements.qrcode.print', ['qrcode' => $res]);
    }


}
