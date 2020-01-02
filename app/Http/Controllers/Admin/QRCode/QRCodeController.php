<?php

namespace App\Http\Controllers\Admin\QRCode;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\QRCode;
use function view;
class QRCodeController extends Controller
{
    protected $breadcrumb = ['Quản lý qrcode','qrcode'];
    public function index()
    {
        $countQrcodeUsed=QRCode::countQrcodeUsed();
        $countQrcodeUnused=QRCode::countQrcodeUnused();
        $this->breadcrumb = ['QRCode'];
        return view('admin.elements.qrcode.index', ['active' => 'qrcode', 'breadcrumb' => $this->breadcrumb,'countQrcodeUsed'=>$countQrcodeUsed,'countQrcodeUnused'=>$countQrcodeUnused]);
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
