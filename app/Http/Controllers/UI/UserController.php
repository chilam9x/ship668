<?php

namespace App\Http\Controllers\UI;

use App\Models\User;
use function dd;
use function redirect;
use \Validator, \Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\DeliveryAddress;

class UserController extends Controller
{
    // public function login(Request $request){
    //     $messages = [
    //         'required' => ':Please enter'
    //     ];
    //     $validator = Validator::make($request->all(), [
    //         'phone' => 'required|numeric',
    //         'password_code' => 'required',
    //     ], $messages);
    //     return json_encode($validator);

    //     if ($validator->fails()) {
    //         return redirect()->back()->with('danger', 'Số điện thoại không đúng');
    //     }
    //     $user = User::where('phone_number', $request->phone)->where('delete_status', 0)->first();

    //     if (empty($user)) {
    //         $user = User::create([
    //             'phone_number' => $request->phone,
    //             'password_code' => $request->password_code
    //         ]);
    //     } else {
    //         if (!empty($user->password_code)) {
    //             User::where('id', $user->id)->update(['password_code' => $request->password_code]);
    //         }
    //     }
    //     Auth::login($user);
    //     return redirect()->back();
    // }

    public function login(Request $request) {
        $check = 1;
        $user = User::where('phone_number', request()->phone)
                    ->where('delete_status', 0)
                    ->first();
        if (!empty($user) && !empty($user->password_code)) {
            if ($user->password_code != request()->password_code) {
                $check = 0;
            }
        }

        if ($check == 1) {
            if (empty($user)) {
                $user = User::create([
                    'phone_number' => $request->phone,
                    'password_code' => $request->password_code
                ]);
            } else {
                if (empty($user->password_code)) {
                    User::where('id', $user->id)->update(['password_code' => $request->password_code]);
                }
            }
            Auth::login($user);
        }
        
        return json_encode($check);
    }

    public function logout(Request $request){
        Auth::logout();
        return redirect()->back();
    }

    public function profile(Request $request) {
        if (!Auth::check()) {
            return redirect(url('/'));
        }
        if ($request->input('update')) {
            $messages = [
                'required' => 'Trường dữ liêu bắt buộc',
                'email' => 'Email không đúng',
                'phone_number.numeric' => 'Số điện thoại không đúng'
            ];
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|email',
                'phone_number' => 'required|numeric'
            ], $messages);

            if ($validator->fails()) {
                return redirect('front-ent/profile')->withErrors($validator)->withInput();
            }

            $user = User::find(Auth::user()->id);
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            $user->phone_number = $request->input('phone_number');
            $user->bank_account = $request->input('bank_account');
            $user->bank_account_number = $request->input('bank_account_number');
            $user->bank_name = $request->input('bank_name');
            $user->bank_branch = $request->input('bank_branch');
            $user->province_id = $request->input('province_id');
            $user->district_id = $request->input('district_id');
            $user->ward_id = $request->input('ward_id');
            $user->home_number = $request->input('home_number');
            if ($user->save()) {
                return redirect()->back();
            }
        }
        $deliveryAddress = DeliveryAddress::where('user_id', Auth::user()->id)->get();
        foreach ( $deliveryAddress as $item ) {
            $item->province_name = $item->provinces->name;
            $item->district_name = $item->districts->name;
            $item->ward_name = $item->wards->name;
        }
        return view('front-ent.element.profile.index', array('deliveryAddress' => $deliveryAddress));
    }
}
