<?php

namespace App\Http\Controllers\Admin\Notification;

use App\Http\Requests\PromotionRequest;
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
use App\Helpers\NotificationHelper;
use App\Jobs\NotificationPromotionJob;

class PromotionController extends Controller
{

    public function __construct()
    {
        $this->middleware('admin');
    }

    protected $breadcrumb = ['Quản lý thông báo', 'Chương trình khuyến mãi'];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        return view('admin.elements.promotions.index', ['active' => 'promotions', 'breadcrumb' => $this->breadcrumb]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.elements.promotions.add', ['active' => 'promotions', 'breadcrumb' => $this->breadcrumb]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(PromotionRequest $request)
    {
        /* $validator = $this->createdValidate($request, 'collaborators');
         if (!empty($validator->errors()->first())) {
             return redirect('admin/collaborators/create')
                 ->withErrors($validator)
                 ->withInput();
         }*/
        DB::beginTransaction();
        try {
            $data = new Notification();
            $data->title = $request->title;
            $data->content = $request->content;
            $data->start_date = $request->start_date;
            $data->end_date = $request->end_date;
            $data->type = 'promotion';
            $data->save();
            DB::commit();

            // thông báo tới customer, shipper, partner khi có chương trình khuyến mãi
            // $notificationHelper = new NotificationHelper();
            // $notificationHelper->notificationPromotion($data);
            dispatch(new NotificationPromotionJob($data));
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
        }
        return redirect(url('admin/promotions'))->with('success', 'Thêm mới chương trình khuyến mãi thành công');
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
        $promotion = Notification::find($id);
        return view('admin.elements.promotions.add', ['promotions' => $promotion, 'active' => 'promotions', 'breadcrumb' => $this->breadcrumb]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(PromotionRequest $request, $id)
    {
        /*$validator = $this->updatedValidate($request, $id, 'collaborators');
        if (!empty($validator->errors()->first())) {
            return redirect('admin/collaborators/'.$id.'/edit')
                ->withErrors($validator)
                ->withInput();
        }*/
        DB::beginTransaction();
        try {
            $data = Notification::find($id);
            $data->title = $request->title;
            $data->content = $request->content;
            $data->start_date = $request->start_date;
            $data->end_date = $request->end_date;
            $data->type = 'promotion';
            $data->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
        return redirect(url('admin/promotions'))->with('success', 'Cập nhật chương trình khuyến mãi thành công');
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
            $notification = Notification::find($id);
            $notification->is_deleted = 1;
            $notification->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
        return redirect(url('admin/promotions'))->with('delete', 'Xóa chương trình khuyến mãi thành công');
    }
}
