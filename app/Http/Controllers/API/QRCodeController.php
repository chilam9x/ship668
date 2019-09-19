<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Models\QRCode;

class QRCodeController extends ApiController
{
    //khách hàng check QRcode trước khi tạo đơn hàng
    public function checkQRCodeCreateNew(Request $request)
    {
        try {
            //kiểm tra qrcode đã tồn tại chưa
            $qr = QRCode::findQRCode($request->qrcode);
            if ($qr!=null) {
                //check qrcode đã được sử dụng chưa
                $qr2=QRCode::findQRCode_OrderNew($request->qrcode);
                if($qr2==null)
                {
                    return response()->json(['msg' => 'Bạn đã quét QRcode thành công', 'code' => 200]);
                }else{
                    return response()->json(['msg' => 'Qrcode đã được sử dụng', 'code' => 201]);
                }
            } else {
                return response()->json(['msg' => 'Qrcode không tồn tại', 'code' => 400]);
            }
        } catch (\Exception $e) {
            return $e;
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
}
