<?php

namespace App\Http\Controllers\Api;

use App\Models\Agency;
use App\Models\District;
use App\Models\Province;
use App\Models\SendAndReceiveAddress;
use App\Models\ShipperRevenue;
use App\Models\Ward;
use function array_merge;
use function bcrypt;
use Carbon\Carbon;
use function dd;
use function GuzzleHttp\Promise\queue;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Models\User;
use App\Models\Shipper;
use App\Models\Device;
use App\Models\DeliveryAddress;
use function implode;
use function is;
use JWTAuthException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use DB, Hash, JWTAuth, So;
use Socialite;
use Facebook\Facebook;
use Facebook\Exceptions\FacebookResponseException;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\ClientException;
use function url;
use App\Models\ShipperLocation;
use App\Events\GetLocationShipper;

class APIUserController extends ApiController
{

    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function login(Request $req)
    {
        $check = false;
        $messages = [
            'account.required' => 'Vui lòng nhập tài khoản đăng nhập'
        ];
        if (isset(request()->deviceToken) || isset(request()->deviceType)) {
            request()->device_token = request()->deviceToken;
            request()->device_type = request()->deviceType;
        }
        $roles = [
            'account' => 'required',
//            'password' => 'required|min:5',
            'device_token' => 'string',
            'device_type' => 'string|in:ios,android'
        ];
        $validator = Validator::make($req->all(), $roles, $messages);

        if ($validator->fails()) {
            return response([
                'msg' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }
        
        $user = User::where('username', $req->account)
                    ->orWhere('phone_number', $req->account)
                    ->orWhere('email', $req->account)
                    ->where('delete_status', 0)
                    ->with('provinces', 'districts', 'wards')
                    ->first();
        // $user = User::where(function($query) use ($req){
        //     $query->where('username', $req->account);
        //     $query->orWhere('phone_number', $req->account);
        //     $query->orWhere('email', $req->account);
        // })->where('delete_status', 0)
        //     ->with('provinces', 'districts', 'wards')
        //     ->first();

        if (empty($user)) {
            $check = true;

            $createUser = [
                'phone_number' => $req->account,
    //                'password' => bcrypt($req->password)
            ];
            if (isset($req->password_code)) {
                $createUser['password_code'] = $req->password_code;
            }

            $user = User::create($createUser);
        }

        
        if (isset($req->password_code)) {
            if (empty($user->password_code)) {
                $user = User::find($user->id);
                $user->password_code = $req->password_code;
                $user->save();
            } else {
                if ($user->password_code != request()->password_code) {
                    return $this->apiError('Mật khẩu không đúng!');
                }
            }
        } else {
            if (!empty($user->password_code)) {
                return $this->apiError('Mật khẩu không đúng!');
            }
        }

        if (!empty($req->device_token) && !empty($req->device_type)) {
            $deviceInfo = [
                'user_id' => $user->id,
                'device_token' => $req->device_token,
                'device_type' => $req->device_type
            ];
            $userDevice = Device::where('device_token', $req->device_token)
                ->where('device_type', $req->device_type)
                ->first();

            if (!$userDevice) {
                $userDevice = Device::create($deviceInfo);
            } else {
                $userDevice->update($deviceInfo);
            }
        }

        $tokenGenerateJwt = $user->generateJwt();//->đây là code tạo token dựa trên
        $user->toArray();
        $data = [
            'id' => $user['id'],
            'token' => $tokenGenerateJwt,
            'phone_number' => $user['phone_number'],
            'created_at' => (string)$user['created_at'],
            'updated_at' => (string)$user['updated_at'],
        ];
        unset($user['id'], $user['phone_number'], $user['created_at'], $user['updated_at']);
        if ($check) {
            $profile = ['profile' => null];
        } else {
            $child = $user;
            $full_address = '';
            if ($user->home_number != null) {
                $full_address .= $user->home_number . ', ';
            }
            if ($user->ward_id != null) {
                $full_address .= $user->wards->name . ', ';
            }
            if ($user->district_id != null) {
                $full_address .= $user->districts->name . ', ';
            }
            if ($user->province_id != null) {
                $full_address .= $user->provinces->name;
            }
            if ($user->avatar != null) {
                $user->avatar = url($user->avatar);
            }
            if ($full_address != '') {
                $child['address'] = [
                    'full_address' => $full_address,
                    'province_id' => $user->province_id,
                    'district_id' => $user->district_id,
                    'ward_id' => $user->ward_id,
                    'home_number' => $user->home_number
                ];
            } else {
                $child['address'] = null;
            }
            if ($user->bank_account_number != null) {
                $child['bank_info'] = [
                    'account' => $user->bank_account,
                    'account_number' => $user->bank_account_number,
                    'name' => $user->bank_name,
                    'branch' => $user->bank_branch,
                ];
            } else {
                $child['bank_info'] = null;
            }
            unset($child['provinces'], $child['districts'], $child['wards'], $child['province_id'], $child['district_id'], $child['ward_id'], $child['home_number'],
                $child['bank_account'], $child['bank_account_number'], $child['bank_name'], $child['bank_branch']);
            $profile = ['profile' => $child];
        }
        return $this->apiOk(array_merge($data, $profile));
    }

    public function loginWithPassword(Request $req)
    {
        $check = false;
        if (isset(request()->deviceToken) || isset(request()->deviceType)) {
            request()->device_token = request()->deviceToken;
            request()->device_type = request()->deviceType;
        }
        $messages = [
            'required' => ':Please enter'
        ];
        $validator = Validator::make($req->all(), [
            'account' => 'required',
            'password' => 'required|min:5',
            'device_token' => 'string',
            'device_type' => 'string|in:ios,android'
        ], $messages);

        if ($validator->fails()) {
            return response([
                'msg' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }
        $user = User::where('uuid', $req->account)->where('delete_status', 0)->with('provinces', 'districts', 'wards')->first();

        if (empty($user)) {
            return response([
                'msg' => 'Account does not exist'
            ], 404);
        }

        if (!empty($req->device_token) && !empty($req->device_type)) {
            $deviceInfo = [
                'user_id' => $user->id,
                'device_token' => $req->device_token,
                'device_type' => $req->device_type
            ];
            $userDevice = Device::where('device_token', $req->device_token)
                ->where('device_type', $req->device_type)
                ->first();

            if (!$userDevice) {
                $userDevice = Device::create($deviceInfo);
            } else {
                $userDevice->update($deviceInfo);
            }
        }

        if (!$token = JWTAuth::attempt(['uuid' => $req->account, 'password' => $req->password])) {
            return response()->json(['code_token' => 0, 'status' => '404', 'msg' => 'wrong password', 'datatype' => 1, 'data' => []], 401);
        }

        $tokenGenerateJwt = $user->generateJwt();//->đây là code tạo token dựa trên
        $user->first();
        $data = [
            'id' => $user->id,
            'token' => $tokenGenerateJwt,
            'phone_number' => $user->phone_number,
            'created_at' => (string)$user->created_at,
            'updated_at' => (string)$user->updated_at,
        ];
        $agency = @Shipper::where('user_id', $user->id)->first();
        if ($check) {
            $profile = ['profile' => null];
        } else {
            $child = [];
            $child['uuid'] = $user->uuid;
            $child['avatar'] = $user->avatar != null ? url($user->avatar) : null;
            $child['username'] = $user->username;
            $child['birth_day'] = $user->birth_day;
            $child['name'] = $user->name;
            $child['email'] = $user->email;
            $child['id_number'] = $user->id_number;
            $child['role'] = $user->role;
            $child['status'] = $user->status;
            $child['fb_id'] = $user->fb_id;
            $child['gg_id'] = $user->gg_id;
            $child['total_COD'] = $user->total_COD;
            $child['delete_status'] = $user->delete_status;
            $scope = Agency::where('id', $agency->agency_id)->with('managementScope')->first();
            $child['hot_line'] = $scope->phone;
            if (!empty($scope->managementScope->pluck('district_id'))) {
                $district = $agency != null ? District::whereIn('id', $scope->managementScope->pluck('district_id'))->with('province')->get() : [];
                $shipper_scope = [];
                foreach ($district as $d) {
                    $shipper_scope[] = $d->name . ' - ' . $d->province->name;
                }
                if (!empty($shipper_scope)) {
                    $child['scope'] = implode(', ', $shipper_scope);
                }
            }
            $revenue = 0;
            if ($user->revenues != null) {
                $revenue = ($user->revenues->total_price - $user->revenues->price_paid) + ($user->revenues->total_COD - $user->revenues->COD_paid);
            }
            $child['revenue'] = $revenue;
            $full_address = '';
            if ($user->home_number != null) {
                $full_address .= $user->home_number . ', ';
            }
            if ($user->ward_id != null) {
                $full_address .= $user->wards->name . ', ';
            }
            if ($user->district_id != null) {
                $full_address .= $user->districts->name . ', ';
            }
            if ($user->province_id != null) {
                $full_address .= $user->provinces->name;
            }
            if ($user->avatar != null) {
                $user->avatar = url($user->avatar);
            }
            if ($full_address != '') {
                $child['address'] = [
                    'full_address' => $full_address,
                    'province_id' => $user->province_id,
                    'district_id' => $user->district_id,
                    'ward_id' => $user->ward_id,
                    'home_number' => $user->home_number
                ];
            } else {
                $child['address'] = null;
            }
            if ($user->bank_account_number != null) {
                $child['bank_info'] = [
                    'account' => $user->bank_account,
                    'account_number' => $user->bank_account_number,
                    'name' => $user->bank_name,
                    'branch' => $user->bank_branch,
                ];
            } else {
                $child['bank_info'] = null;
            }
            $profile = ['profile' => $child];
        }
        return $this->apiOk(array_merge($data, $profile));
    }

    public function loginfb(Request $req, Facebook $fb)
    {

        $validator = Validator::make($req->all(), [
            'phone' => 'required',
            'accessToken' => 'required|string',
            'device_token' => 'required|string',
            'device_type' => 'required|string|in:ios,android'
        ]);

        if ($validator->fails()) {
            return $this->apiError($validator->errors()->first());
        }

        try {
            $oAuth2Client = $fb->getOAuth2Client();
            $fb->setDefaultAccessToken($req->accessToken);

            $fields = "id,email,name,picture,verified";

            $fbUser = $fb->get('/me?fields=' . $fields)->getGraphUser()->asArray();
        } catch (FacebookResponseException $e) {
            return $this->apiError($e->getMessage());
        }

        if (empty($fbUser) || empty($fbUser['id'])) {
            return $this->apiError("Invalid Access Token!");
        }

        $fbId = $fbUser['id'];
        $fbEmail = !empty($fbUser['email']) ? $fbUser['email'] : '';
        $fbName = !empty($fbUser['name']) ? $fbUser['name'] : '';
        $fbAvatar = '';

        $fbInfo = [
            'fb_id' => $fbId,
        ];

        $fbAvatar = "http://graph.facebook.com/{$fbId}/picture?type=large";
        $fbInfo['phone'] = $req->phone;

        // if (!empty($fbUser['picture']) && !empty($fbUser['picture']['url'])) {
        //     $fbAvatar = $fbUser['picture']['url'];
        // }
        $fbInfo['avatar'] = $fbAvatar;

        $query = User::where('fb_id', $fbId);

        if (!empty($fbEmail)) {
            $fbInfo['email'] = $fbEmail;
            $query->orWhere('email', $fbEmail);
        }

        if (!empty($fbName)) {
            $fbInfo['name'] = $fbName;
        }

        $user = $query->first();
        $dataType = 1;

        if (!$user) {
            $user = User::create($fbInfo);
            $dataType = 0;
        } else {
            if (empty($user->phone_number)) {
                $user->update([
                    'phone_number' => $req->phone
                ]);
            }
            $user->update([
                'avatar' => $fbAvatar,
            ]);
        }

        $deviceInfo = [
            'user_id' => $user->id,
            'device_token' => $req->device_token,
            'device_type' => $req->device_type
        ];

        $userDevice = Device::where('device_token', $req->device_token)
            ->where('device_type', $req->device_type)
            ->first();

        if (!$userDevice) {
            $userDevice = Device::create($deviceInfo);
        } else {
            $userDevice->update($deviceInfo);
        }

        $tokenGenerateJwt = $user->generateJwt();
        $result = array_merge($user->toArray(), ['token' => $tokenGenerateJwt]);
        return $this->apiOk($result, $dataType);
    }

    public function loginGG(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'accessToken' => 'required|string',
            'device_token' => 'required|string',
            'device_type' => 'required|string|in:ios,android'
        ]);

        if ($validator->fails()) {
            return $this->apiError($validator->errors()->first());
        }

        try {
            $provider = 'google';
            $driver = Socialite::driver($provider);
            $socialUserObject = $driver->userFromToken($req->accessToken);
        } catch (ClientException $e) {
            return $this->apiError(Psr7\str($e->getResponse()));
        }

        if (empty($socialUserObject) || empty($socialUserObject['id'])) {
            return $this->apiError("Invalid Access Token!");
        }

        $ggId = $socialUserObject['id'];
        $ggEmail = !empty($ggUser['email']) ? $ggUser['email'] : '';
        $ggName = !empty($ggUser['name']) ? $ggUser['name'] : '';
        $ggAvatar = '';

        $ggInfo = [
            'gg_id' => $ggId,
        ];

        $ggInfo['avatar'] = "";

        $query = User::where('gg_id', $ggId);

        if (!empty($ggEmail)) {
            $ggInfo['email'] = $ggEmail;
            $query->orWhere('email', $ggEmail);
        }

        if (!empty($ggName)) {
            $ggInfo['name'] = $ggName;
        }

        $user = $query->first();
        $dataType = 1;

        if (!$user) {
            $user = User::create($ggInfo);
            $dataType = 0;
        } else {
            $user->update([
                'avatar' => $ggAvatar,
            ]);
        }

        $deviceInfo = [
            'user_id' => $user->id,
            'device_token' => $req->device_token,
            'device_type' => $req->device_type
        ];

        $userDevice = Device::where('device_token', $req->device_token)
            ->where('device_type', $req->device_type)
            ->first();

        if (!$userDevice) {
            $userDevice = Device::create($deviceInfo);
        } else {
            $userDevice->update($deviceInfo);
        }

        $tokenGenerateJwt = $user->generateJwt();
        $result = array_merge($user->toArray(), ['token' => $tokenGenerateJwt]);
        return $this->apiOk($result, $dataType);
    }

    public function getUser(Request $request)
    {
        $userId = $request->user()->id;
        $data = User::where('id', $userId)->with('provinces', 'districts', 'wards')->first();
        if ($data->role == 'shipper') {
            $agency = Shipper::where('user_id', $userId)->first();
            $scope = Agency::where('id', $agency->agency_id)->with('managementScope')->first();
            $data->hot_line = @$scope->phone;
            if (!empty($scope->managementScope->pluck('district_id'))) {
                $district = District::whereIn('id', $scope->managementScope->pluck('district_id'))->with('province')->get();
                $shipper_scope = [];
                foreach ($district as $d) {
                    $shipper_scope[] = $d->name . ' - ' . $d->province->name;
                }
            }
            if (!empty($shipper_scope)) {
                $data->scope = implode(', ', $shipper_scope);
            }
            $revenue = 0;
            if ($data->revenues != null) {
                $revenue = ($data->revenues->total_price - $data->revenues->price_paid) + ($data->revenues->total_COD - $data->revenues->COD_paid);
            }
            $data->revenue = $revenue;
        }
        $full_address = '';

        if ($data->home_number != null) {
            $full_address .= $data->home_number . ', ';
        }
        if ($data->ward_id != null) {
            $full_address .= $data->wards->name . ', ';
        }
        if ($data->district_id != null) {
            $full_address .= $data->districts->name . ', ';
        }
        if ($data->province_id != null) {
            $full_address .= $data->provinces->name;
        }

        if ($data->avatar != null) {
            $data->avatar = url($data->avatar);
        }
        if ($full_address != '') {
            $data->address = [
                'full_address' => $full_address,
                'province_id' => $data->province_id,
                'district_id' => $data->district_id,
                'ward_id' => $data->ward_id,
                'home_number' => $data->home_number
            ];

        } else {
            $data->address = null;
            $data->bank_info = null;
        }
        if ($data->bank_account_number != null) {
            $data->bank_info = [
                'account' => $data->bank_account,
                'account_number' => $data->bank_account_number,
                'name' => $data->bank_name,
                'branch' => $data->bank_branch,
            ];
        } else {
            $data->bank_info = null;
        }

        unset($data['provinces'], $data['districts'], $data['wards'], $data['province_id'], $data['district_id'], $data['ward_id'], $data['home_number'],
            $data['bank_account'], $data['bank_account_number'], $data['bank_name'], $data['bank_branch']);
        return $this->apiOk($data);
    }

    public function otp(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'phone' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return $this->apiError($validator->errors()->first());
        }
        return 1;

    }

    public function resetPassword(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'phone' => 'required|integer',
            'password' => 'required|min:5',
            'password-cf' => 'required|same:password'
        ]);
        if ($validator->fails()) {
            return $this->apiError($validator->errors()->first());
        }
        $user = User::where('phone_number', $req->phone)->first();
        if (empty($user)) {
            return response([
                'msg' => 'Account does not exist'
            ], 404);
        } else {
            $user->password = bcrypt($req->password);
            $user->save();
        }
        return response()->json(['code_token' => 1, 'datatype' => 1, 'status' => '200', 'msg' => 'Change password success']);
    }

    public function updateGeneral(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'name' => 'required|string',
            'email' => 'required|email',
            'phone_number' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->apiError($validator->errors()->first());
        }

        $user = $req->user();
        $userId = $user->id;

        $email = $req->email;
        if (!empty($email)) {
            $existedEmail = User::where('email', $req->email)
                ->where('id', '<>', $userId)
                ->count();

            if ($existedEmail) {
                return $this->apiError('Email này đã tồn tại.');
            }
        }

        $phone = $req->phone;
        if (!empty($phone)) {
            $existedPhone = User::where('phone_number', $req->phone)
                ->where('id', '<>', $userId)
                ->count();

            if ($existedPhone) {
                return $this->apiError('Số điện thoại này đã tồn tại.');
            }
        }
        $user->update($req->all());
        $tokenGenerateJwt = $user->generateJwt();
        $result = array_merge($user->toArray(), ['token' => $tokenGenerateJwt]);
        return $this->apiOk($result);
    }

    public function addDelivery(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'province_id' => 'required',
            'district_id' => 'required',
            'ward_id' => 'required',
            'home_number' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->apiError($validator->errors()->first());
        }
        $req['user_id'] = $req->user()->id;
        $check = DeliveryAddress::where('user_id', $req['user_id'])->where('province_id', $req->province_id)->where('district_id', $req->district_id)
            ->where('ward_id', $req->ward_id)->where('home_number', $req->home_number)->first();
        $data = DeliveryAddress::where([]);
        if (!empty((array)$check)) {
            if (isset($req->last_address)) {
                $this->updateAddress($check->id, $req);
                $data = $data->where('id', $check->id)->with('users', 'provinces', 'districts', 'wards')->get();
            } else {
                return $this->apiError('your delivery address already exist');
            }
        } else {
            $delivery = DeliveryAddress::create($req->all());
            if (isset($req->default)) {
                if ($req->default == true) {
                    $flag = DeliveryAddress::where('user_id', $req->user()->id)->where('default', 1)->first();
                    if (!empty((array)$flag)) {
                        $flag->default = 0;
                        $flag->save();
                    }
                    $delivery->default = 1;
                    $delivery->save();
                    $this->updateAddress($delivery->id, $req);
                }
            }
            $data = $data->where('id', $delivery->id)->with('users', 'provinces', 'districts', 'wards')->get();
        }
        $result = [];
        foreach ($data as $q) {
            $result['id'] = $q->id;
            $result['address'] = [
                'province_id' => $q->province_id,
                'district_id' => $q->district_id,
                'ward_id' => $q->ward_id,
                'home_number' => $q->home_number,
                'full_address' => $q->home_number . ', ' . $q->wards->name . ', ' . $q->districts->name . ', ' . $q->provinces->name
            ];
            $result['default'] = $q->default;
        }
        return $this->apiOk($result);
    }

    public function removeDelivery($id)
    {
        $delivery = DeliveryAddress::find($id);
        if (isset($delivery->user_id)) {
            $user = User::where('id', $delivery->user_id)->where('province_id', $delivery->province_id)->where('district_id', $delivery->district_id)
                ->where('ward_id', $delivery->ward_id)->where('home_number', $delivery->home_number)->first();;
            if (isset($user->province_id)) {
                $user->province_id = null;
                $user->district_id = null;
                $user->ward_id = null;
                $user->home_number = null;
                $user->save();
            }
            $delivery->delete();
            return $this->apiOk('remove success');
        } else {
            return $this->apiError('no data found');
        }
    }

    public function updateBank(Request $req)
    {
        $user = $req->user();
        if ($user->bank_account != null && $user->bank_name != null && $user->bank_branch != null && $user->bank_account_number != null){
            return $this->apiError('Tài khoản không được phép tự ý cập nhật, gọi đến hotline để được hỗ trợ');
        }
        $validator = Validator::make($req->all(), [
            'bank_account' => 'required|string',
            'bank_name' => 'required|string',
            'bank_branch' => 'required',
            'bank_account_number' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->apiError($validator->errors()->first());
        }
        $user->update($req->all());
        if ($user->avatar != null) {
            $user->avatar = url($user->avatar);
        }
        $tokenGenerateJwt = $user->generateJwt();
        $result = array_merge($user->toArray(), ['token' => $tokenGenerateJwt]);
        return $this->apiOk($result);
    }

    public function updateAddress($id, Request $req)
    {
        $data = DeliveryAddress::find($id);
        $flag = DeliveryAddress::where('user_id', $req->user()->id)->where('id', '!=', $id)->where('default', 1)->first();
        if (isset($flag->default)) {
            $flag->default = 0;
            $flag->save();
        }
        $data->default = 1;
        $data->save();
        $user = User::find($data->user_id);
        $user->province_id = $data->province_id;
        $user->district_id = $data->district_id;
        $user->ward_id = $data->ward_id;
        $user->home_number = $data->home_number;
        $user->save();
        return $this->apiOk('update success');
    }

    public function updateAvatar(Request $req)
    {
        $file = $req->file;
        $filename = date('Ymd-His-') . $file->getFilename() . '.' . $file->extension();
        $filePath = 'img/avatar/';
        $movePath = public_path($filePath);
        $file->move($movePath, $filename);
        $user = User::find($req->user()->id);
        $user->avatar = $filePath . $filename;
        $user->save();
        return $this->apiOk(url($filePath . $filename));
    }

    public function updatePass(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|min:5'
        ], [
            'password.required' => 'Password is required!',
            'password.min' => 'Your password is too short!',
        ]);

        if ($validator->fails()) {
            return $this->apiError($validator->errors()->first());
        }

        $userId = $request->user()->id;
        $data = $request->all();
        if (Hash::check($request->passwordold, Auth::user()->password)) {
            $user = User::where('id', $userId)->first();
            $user->password = bcrypt($request->passwordnew);
            $user->save();
            return response()->json(['code_token' => 1, 'datatype' => 1, 'status' => '200', 'msg' => 'Change password success']);
        } else {
            return response()->json(['status' => '400', 'msg' => 'Your password is incorrect!']);
        }
    }

    public function logout(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'device_token' => 'string',
            'device_type' => 'string|in:ios,android'
        ]);

        if ($validator->fails()) {
            return $this->apiError($validator->errors()->first());
        }

        if (!empty($req->device_token) && !empty($req->device_type)) {
            // $deviceInfo = [
            //     'user_id' => null,
            //     'device_token' => $req->device_token,
            //     'device_type' => $req->device_type
            // ];

            $userDevice = Device::where('device_token', $req->device_token)
                ->where('device_type', $req->device_type)
                ->first();

            // if (!$userDevice) {
            //     $userDevice = Device::create($deviceInfo);
            // } else {
            //     $userDevice->update($deviceInfo);
            // }

            if ($userDevice && !empty($userDevice)) {
                $userDevice->delete();
            }
        }

        ShipperLocation::where('user_id', request()->user()->id)->update(['online' => 0]);

        $token = JWTAuth::getToken();
        JWTAuth::invalidate($token);

        return response()->json(['code_token' => 0, 'datatype' => 1, 'status' => '200', 'msg' => 'Logout success']);
    }

    public function getSendOrReceiveAddress(Request $req)
    {
        $limit = $req->get('limit', 10);
        $validator = Validator::make($req->all(), [
            'type' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->apiError($validator->errors()->first());
        }
        $user_id = $req->user()->id;
        $data = SendAndReceiveAddress::where('user_id', $user_id)->where('type', $req->type)->orderBy('updated_at', 'desc')->paginate($limit);
        foreach ($data->items() as $item) {
            $item->address = [
                'province_id' => $item->province_id,
                'district_id' => $item->district_id,
                'ward_id' => $item->ward_id,
                'home_number' => $item->home_number,
                'full_address' => $item->full_address
            ];
            unset($item->province_id, $item->district_id, $item->ward_id, $item->home_number, $item->full_address, $item->user_id, $item->type, $item->created_at, $item->updated_at);
        }
        return $this->apiOk($data);
    }

    public function turnOnParttime(Request $req) {
        $validator = Validator::make($req->all(), [
            'turn_on' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->apiError($validator->errors()->first());
        }

        $userId = $req->user()->id;
        $shipper = ShipperLocation::where('user_id', $userId)->first();
        if ($shipper == null){
            $shipper = new ShipperLocation();
            $shipper->user_id = $userId;
        }
        $shipper->online = $req->turn_on;
        if ($shipper->save()) {
            $dataLocation = array(
                'lat' =>  isset($req->lat) ? $req->lat : $shipper->lat,
                'lng' => isset($req->lng) ? $req->lng : $shipper->lng,
                'id' => $userId
            );
            event(new GetLocationShipper($dataLocation));
            \Log::info('id'.$userId.', turn_on: '.$req->turn_on);
            return $this->apiOk($shipper->toArray());
        }
        return $this->apiError('Updated shipper fail!');
    }

    public function updateDevice(Request $req) {
        $validator = Validator::make($req->all(), [
            'device_type' => 'required|in:ios,android',
            'device_token' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->apiError($validator->errors()->first());
        }

        $userId = $req->user()->id;
        $status = Device::updateOrCreate(array(
            'user_id' => $userId,
            'device_type' => $req->device_type
        ), array(
            'device_token' => $req->device_token
        ));
        if ($status) {
            return $this->apiOk('Updated device success!');
        }
        return $this->apiError('Updated device fail!');
    }

    public function getShipperOnline(Request $req) {
        $userId = $req->user()->id;
        $shipperOnline = ShipperLocation::where('user_id', $userId)->where('online', 1)->first();
        if ( !empty($shipperOnline) ) {
            return $this->apiOk(['online' => 1]);
        }
        return $this->apiOk(['online' => 0]);
    }
}
