<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class QRCode
{
    public static function getList()
    {
        $res = DB::table('qrcode')->orderBy('id', 'desc')->paginate(3);
        return $res;
    }
    public static function find($data)
    {
        $res = DB::table('qrcode')->where('name', 'LIKE', '%' .  $data . '%')->orderBy('id', 'desc')->paginate(3);
        return $res;
    }
    public static function getQRCodeListUnused()
    {
        $count = DB::table(config('constants.QRCODE_TABLE'))->where('status', 0)->get();
        return $count;
    }
    //kiểm tra qr code có tồn tại không
    public static function findQRCode($code)
    {
        $res = DB::table(config('constants.QRCODE_TABLE'))->where('code', $code)->first();
        return $res;
    }
    //check qrcode đã được sử dụng chưa
    public static function findQRCode_OrderNew($code)
    {
        $res = DB::table(config('constants.QRCODE_TABLE'))->where('code', $code)->where('status', 1)->first();
        return $res;
    }
    public static function countQrcodeUsed()
    {
        $count = DB::table('qrcode')->where('is_used', 1)->count();
        return $count;
    }
    public static function countQrcodeUnused()
    {
        $count = DB::table('qrcode')->where('is_used', 0)->count();
        return $count;
    }
    public static function postCreate($data)
    {
        try {
            $array = array();
            for ($i = 1; $i <= $data; $i++) {
                $name = str_random(12);
                $checkcode = DB::table('qrcode')->where('name', $name)->count();
                if ($checkcode < 1) {
                    DB::table('qrcode')->insert(
                        [
                            'name' => $name,
                            'created_at' => date('Y-m-d h:i:s'),
                            'is_used' => 0
                        ]
                    );
                    $array[] = $name;
                }
            }
            return $array;
        } catch (\Exception $e) {
            return $e;
        }
    }
    public static function edit($data = [], $id)
    {
        try {
            DB::table(config('constants.ROLE_TABLE'))
                ->where('id', $id)
                ->update([
                    'name' => $data['name'],
                    'updated_at' => date('Y-m-d h:i:s'),

                ]);
            return 200;
        } catch (\Exception $e) {
            return $e;
        }
    }
    public static function delete($id)
    {
        try {
            DB::table(config('constants.ROLE_TABLE'))
                ->where('id', $id)
                ->update([
                    'status_id' => 1
                ]);
            return 200;
        } catch (\Exception $e) {
            return $e;
        }
    }
    public static function changeStatus($id) //thay doi status chua su dung ->dang su dung
    {
        try {
            DB::table(config('constants.QRCODE_TABLE'))
                ->where('id', $id)
                ->update([
                    'status' => 1
                ]);
            return 200;
        } catch (\Exception $e) {
            return $e;
        }
    }

}
