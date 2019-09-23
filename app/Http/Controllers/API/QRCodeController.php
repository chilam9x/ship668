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
    //tài xế lấy đơn hàng
    public function receiveOrder(Request $request)
    {
        try {
            //kiểm tra qrcode đã tồn tại chưa
            $qr = QRCode::findQRCode($request->qrcode);
            if ($qr != null) {
                //check qrcode có phải status đơn hàng mới?
                $qr2 = QRCode::checkQRCode_OrderNew($request->qrcode);
                if ($qr2 != null) {
                    //phân công lấy đơn hàng
                    $order=QRCode::receiveOrder($request->qrcode);
                    if($order==200)
                    {
                        return response()->json(['msg' => 'Bạn lấy đơn hàng thành công ', 'code' => 200]);
                    }else {
                        return response()->json(['msg' => 'QRCode lấy đơn hàng đã phát sinh lỗi', 'code' => 201]);
                    }
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
    //tài xế chọn đơn hàng giao
    public function senderOrder(Request $request)
    {
        try {
            //kiểm tra qrcode đã tồn tại chưa
            $qr = QRCode::findQRCode($request->qrcode);
            if ($qr != null) {
                //check qrcode có phải của đơn hàng đã lấy?
                $qr2 = QRCode::checkQRCode_OrderTaking($request->qrcode);
                if ($qr2 != null) {
                    //phân công lấy đơn hàng
                    $order=QRCode::senderOrder($request->qrcode);
                    if($order==200)
                    {
                        return response()->json(['msg' => 'Bạn chọn đơn hàng giao thành công ', 'code' => 200]);
                    }else {
                        return response()->json(['msg' => 'QRCode lấy đơn hàng đã phát sinh lỗi', 'code' => 201]);
                    }
                } else {
                    return response()->json(['msg' => 'Không phải đơn hàng đã lấy, vui lòng kiểm tra lại', 'code' => 201]);
                }
            } else {
                return response()->json(['msg' => 'Qrcode không tồn tại', 'code' => 400]);
            }
        } catch (\Exception $e) {
            return $e;
        }
    }
}
