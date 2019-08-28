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
        return view('admin.qrcode.print', ['qrcode' => $res]);
       
    }
    public function print()
    {
        $res = QRCode::getQRCodeListUnused();
        return view('admin.qrcode.print', ['qrcode' => $res]);
    }
    public function edit($id)
    {
        $res = Role::find($id);
        return view('admin.role.edit', ['role' => $res]);
    }
    public function postedit(Request $request, $id)
    {
        $data = $request->all();
        $res = Role::edit($data, $id);
        if ($res === 200) {
            return redirect('admin/role');
        } else {
            return redirect('admin/role/edit/' . $id)->with('error', 'Cannot update');
        }
    }
    public function OrderTake($data)
    {
        try {
            //check qrcode co ton tai & status da su dung
            $qr = QRCode::findQRCode_OrderNew($data->code);
            if ($qr) //check ton tai
            {
                //check qr va don hang
                $qrcode_order = Order::checkOrderNew($qr->id);
                if ($qrcode_order) { //check ton tai
                    //change status order moi->da lay hang
                    $changeOrderTake = Order::changeStatusOrderTake($qrcode_order->id);
                    //insert shipper take order
                    $user = Auth::user();
                    $insertOrderUser = OrderUser::insertShipperOderTake($qrcode_order->id, $user->id);
                }
                return 200;
            }
        } catch (\Exception $e) {
            return $e;
        }
    }
    public function delete($id)
    {
        $res = Role::delete((int)$id);
        return redirect('admin/role');
    }
}
