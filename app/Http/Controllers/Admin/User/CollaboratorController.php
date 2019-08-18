<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Requests\CollaboratorRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Agency;
use App\Models\Collaborator;
use App\Models\Province;
use App\Models\User;
use App\Models\Notification;
use App\Models\NotificationUser;
use function date_create_immutable_from_format;
use function dd;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Session;

class CollaboratorController extends Controller
{

    public function __construct()
    {
        $this->middleware('admin', ['except' => ['postLogin','postRegister', 'logout']]);
    }

    protected $breadcrumb = ['Quản lý thành viên', 'cộng tác viên'];

    public function postLogin(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();
        if (empty($user)) {
            return redirect()->back()->with('err', 'Tài khoản không tồn tại!');
        }
        if ($user->delete_status == 1) {
            return redirect()->back()->with('err', 'Tài khoản đã bị xóa!');
        }
        if ($user->status != 'active') {
            return redirect()->back()->with('err', 'Tài khoản chưa được kích hoạt');
        }
        if ($user->role == 'admin' || $user->role == 'collaborators') {
            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                // Authentication passed...
                return redirect(url('/admin/report'));
            } else {
                return redirect()->back()->with('err', 'Email hoặc mật khẩu không đúng!');
            }
        } else {
            return redirect()->back()->with('err', 'Tài khoản không có quyền truy cập vào hệ thống quản trị!');
        }
    }

    public function postRegister(RegisterRequest $request)
    {
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        return redirect(url('/login'))->with('success', 'Register success! please wait administrator active your account');
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        return view('admin.elements.users.collaborators.index', ['active' => 'collaborators', 'breadcrumb' => $this->breadcrumb]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $agency = Agency::with('collaborators')->whereHas('collaborators', function ($query) {
            $query->where('user_id', Auth::user()->id);
        })->get();
        return view('admin.elements.users.collaborators.add', ['agency' => $agency, 'active' => 'collaborators', 'breadcrumb' => $this->breadcrumb]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(CollaboratorRequest $request)
    {
        /* $validator = $this->createdValidate($request, 'collaborators');
         if (!empty($validator->errors()->first())) {
             return redirect('admin/collaborators/create')
                 ->withErrors($validator)
                 ->withInput();
         }*/
        DB::beginTransaction();
        try {
            $data = new User();
            $data->name = $request->name;
            $data->password = Hash::make($request->password);
            $data->email = $request->email;
            $data->birth_day = $request->birth_day;
            $data->province_id = $request->province_id;
            $data->district_id = $request->district_id;
            $data->ward_id = $request->ward_id;
            $data->home_number = $request->home_number;
            $data->phone_number = $request->phone_number;
            $data->id_number = $request->id_number;
            $data->role = 'collaborators';
            $data->bank_account = $request->bank_account;
            $data->bank_account_number = $request->bank_account_number;
            $data->bank_name = $request->bank_name;
            $data->bank_branch = $request->bank_branch;
            if ($request->hasFile('avatar')) {
                $file = $request->avatar;
                $filename = date('Ymd-His-') . $file->getFilename() . '.' . $file->extension();
                $filePath = 'img/avatar/';
                $movePath = public_path($filePath);
                $file->move($movePath, $filename);
                $data->avatar = $filePath . $filename;
            }
            $data->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
        }
        return redirect(url('admin/collaborators'))->with('success', 'Thêm mới cộng tác viên thành công');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $collaborator = User::find($id);
        $agency = Agency::with('collaborators')->whereHas('collaborators', function ($query) {
            $query->where('user_id', Auth::user()->id);
        })->get();
        return view('admin.elements.users.collaborators.add', ['collaborators' => $collaborator, 'agency' => $agency, 'active' => 'collaborators', 'breadcrumb' => $this->breadcrumb]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(CollaboratorRequest $request, $id)
    {
        /*$validator = $this->updatedValidate($request, $id, 'collaborators');
        if (!empty($validator->errors()->first())) {
            return redirect('admin/collaborators/'.$id.'/edit')
                ->withErrors($validator)
                ->withInput();
        }*/
        DB::beginTransaction();
        try {
            $data = User::find($id);
            $data->name = $request->name;
            if ($request->password != null) {
                $data->password = Hash::make($request->password);
            }
            $data->email = $request->email;
            $data->birth_day = $request->birth_day;
            $data->province_id = $request->province_id;
            $data->district_id = $request->district_id;
            $data->ward_id = $request->ward_id;
            $data->home_number = $request->home_number;
            $data->phone_number = $request->phone_number;
            $data->id_number = $request->id_number;
            $data->bank_account = $request->bank_account;
            $data->bank_account_number = $request->bank_account_number;
            $data->bank_name = $request->bank_name;
            $data->bank_branch = $request->bank_branch;
            if ($request->hasFile('avatar')) {
                $file = $request->avatar;
                $filename = date('Ymd-His-') . $file->getFilename() . '.' . $file->extension();
                $filePath = 'img/avatar/';
                $movePath = public_path($filePath);
                $file->move($movePath, $filename);
                $data->avatar = $filePath . $filename;
            }
            $data->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
        return redirect(url('admin/collaborators'))->with('success', 'Thêm mới cộng tác viên thành công');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $user = User::find($id);
            if ($user->avatar != null) {
                if (file_exists($user->avatar)) {
                    @unlink('file_path');
                }
            }
            $col_check = Collaborator::where('user_id', $id)->first();
            if (!empty($col_check)) {
                Collaborator::where('user_id', $id)->delete();
            }
            $user->delete_status = 1;
            $user->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
        return redirect(url('admin/collaborators'))->with('delete', 'Xóa Cộng tác viên thành công');
    }
}
