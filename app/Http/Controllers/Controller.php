<?php

namespace App\Http\Controllers;

use App\Models\User;
use function dd;
use \Validator;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function createdValidate($request, $role)
    {
        $validator = Validator::make([], []);
        $email = User::where('email', $request->email)->where('delete_status', 0)->count();
        $phone = User::where('phone_number', $request->phone_number)->where('delete_status', 0)->count();
        $id_number = User::where('id_number', $request->id_number)->where('delete_status', 0)->count();
        /*if ($role != 'admin' || $role != 'collaborator') {
            $email = $email->where('role', $role);
            $phone = $phone->where('role', $role);
            $id_number = $id_number->where('role', $role);
        }*/
        if ($request->bank_account_number != null) {
            $bank_account_number = User::where('bank_account_number', $request->bank_account_number)->where('delete_status', 0)->count();
          /*  if ($role != 'admin' || $role != 'collaborator') {
                $bank_account_number = $bank_account_number->where('role', $role);
            }*/
            if ($bank_account_number > 0) {
                $validator->errors()->add('bank_account_number', 'Số tài khoản đã tồn tại');
            }
        }
        if ($email > 0) {
            $validator->errors()->add('email', 'Email đã tồn tại');
        }
        if ($phone > 0) {
            $validator->errors()->add('phone_number', 'Số điện thoại đã tồn tại');
        }
        if ($id_number > 0) {
            $validator->errors()->add('id_number', 'Số CMND đã tồn tại');
        }
        return $validator;
    }

    public function updatedValidate($request, $id, $role)
    {
        $validator = Validator::make([], []);
        $email = User::where('id', '!=', $id)->where('email', $request->email)->where('delete_status', 0)->count();
        $phone = User::where('id', '!=', $id)->where('phone_number', $request->phone_number)->where('delete_status', 0)->count();
        $id_number = User::where('id', '!=', $id)->where('id_number', $request->id_number)->where('delete_status', 0)->count();
        if ($request->bank_account_number != null) {
            $bank_account_number = User::where('id', '!=', $id)->where('bank_account_number', $request->bank_account_number)->where('delete_status', 0)->count();
            if ($bank_account_number > 0) {
                $validator->errors()->add('bank_account_number', 'Số tài khoản đã tồn tại');
            }
        }
        if ($email > 0) {
            $validator->errors()->add('email', 'Email đã tồn tại');
        }
        if ($phone > 0) {
            $validator->errors()->add('phone_number', 'Số điện thoại đã tồn tại');
        }
        if ($id_number > 0) {
            $validator->errors()->add('id_number', 'Số CMND đã tồn tại');
        }
        return $validator;

    }
}
