<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Models\QRCode;
use App\Models\Order;
use Illuminate\Http\Request;

class QRCodeController extends ApiController
{
    //khách hàng check QRcode trước khi tạo đơn hàng
    public function checkQRCodeCreateNew(Request $request)
    {
        try {
            //kiểm tra qrcode đã tồn tại chưa
            $qr = QRCode::findQRCode($request->qrcode);
            if ($qr != null) {
                //check qrcode đã được sử dụng chưa
                $qr2 = QRCode::findQRCode_OrderNew($request->qrcode);
                if ($qr2 == null) {
                    return response()->json(['msg' => 'Bạn đã quét QRcode thành công', 'code' => 200]);
                } else {
                    return response()->json(['msg' => 'Qrcode đã được sử dụng', 'code' => 201]);
                }
            } else {
                return response()->json(['msg' => 'Qrcode không tồn tại', 'code' => 400]);
            }
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function takeOrder(Request $request)
    {
        try {
            //kiểm tra qrcode đã tồn tại chưa
            $qr = QRCode::findQRCode($request->qrcode);
            dd($qr);

            if ($qr != null) {
                //check qrcode có phải của đơn hàng mới?
                $qr2 = QRCode::checkQRCode_OrderNew($request->qrcode);
                if ($qr2 != null) {
                    //phân công lấy đơn hàng
                    $order=QRCode::takeOrder($request->qrcode);
                    return response()->json(['msg' => 'QRcode ', 'code' => 200]);
                } else {
                    return response()->json(['msg' => 'Không phải đơn hàng mới, vui lòng kiểm tra lại', 'code' => 201]);
                }
            } else {
                return response()->json(['msg' => 'Qrcode không tồn tại', 'code' => 400]);
            }
        } catch (\Exception $e) {
            return $e;
        }
    }
}
