<?php

namespace App\Http\Controllers\Admin\Notification;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\NotificationUser;
use App\Models\User;
use DB;
use App\Jobs\NotificationHandleJob;

class NotificationHandleController extends Controller
{
    public function __construct() {
        $this->middleware('admin');
    }

    protected $breadcrumb = ['Quản lý thông báo', 'Thông báo thủ công'];

    public function index() {
    	return view('admin.elements.notifications.index', ['active' => 'notification-handle', 'breadcrumb' => $this->breadcrumb]);
    }

    public function add() {
    	$roles = [
    		'shipper' => 'Giao hàng',
    		'customer' => 'Khách hàng',
    		'collaborators' => 'Cộng tác viên',
    		'panter' => 'Đối tác'
    	];

    	if (request()->isMethod('post')) {
            // $db = User::where('delete_status', 0)->where('status', 'active')->where('role', '!=', 'admin');
            if (request()->user_id == 'all_customer') {
                $users = User::where('delete_status', 0)
                            ->where('status', 'active')
                            ->where('role', '!=', 'admin')
                            ->where('role', 'customer')
                            ->select('id', 'role', 'status', 'delete_status')
                            ->get();
            } else if (request()->user_id == 'all_shipper') {
                $users = User::where('delete_status', 0)
                            ->where('status', 'active')
                            ->where('role', '!=', 'admin')
                            ->where('role', 'shipper')
                            ->select('id', 'role', 'status', 'delete_status')
                            ->get();
            } else if (request()->user_id == 'all_customer_booked') {
                $userBooks = User::join('bookings', 'users.id', '=', 'bookings.sender_id')
                        ->where('delete_status', 0)
                        ->where('role', 'customer')
                        ->where('users.status', 'active')
                        ->where('role', '!=', 'admin')
                        ->select('users.*')
                        ->pluck('users.id');
                $users = User::whereIn('id', $userBooks)->get();
            } else {
                $users = User::where('delete_status', 0)->where('status', 'active')->where('role', '!=', 'admin')->get();    
            }
            
    		DB::beginTransaction();
    		try {
    			$notificationHandle = new Notification;
	    		$notificationHandle->title = request()->title;
	    		$notificationHandle->content = request()->content;
	    		$notificationHandle->type = 'handle';
	    		$notificationHandle->save();

	    		$dataSet = [];
                $userIdArr = [];
    			
				foreach ( $users as $user ) {
					$dataSet[] = array(
						'notification_id' => $notificationHandle->id,
						'user_id' => $user->id
					);
                    $userIdArr[] = $user->id;
				}
				DB::table('notifications_users')->insert($dataSet);
    			DB::commit();

                // thông báo
                $notificationHandleArr = array(
                    'notification_id' => intval($notificationHandle->id),
                    'title' => $notificationHandle->title,
                    'body' => $notificationHandle->content,
                    'type' => $notificationHandle->type
                );
                dispatch(new NotificationHandleJob($notificationHandleArr, $userIdArr));
                
			    return redirect(url('admin/notification-handles'))->with('success', 'Thêm mới thông báo thành công');
    		} catch (Exception $e) {
    			DB::rollback();
    			return redirect(url('admin/notification-handles'))->with('delete', 'Thêm mới thông báo thất bại');
    		}
    	}
    	return view('admin.elements.notifications.add', ['active' => 'notification-handle', 'breadcrumb' => $this->breadcrumb]);
    }

    public function edit($id) {
    	$notificationHandle = Notification::find($id);
    	$users = NotificationUser::join('users', 'notifications_users.user_id', '=', 'users.id')
                            ->where('notification_id', $id)
                            ->select('notifications_users.user_id', 'name')
                            ->get();

    	return view('admin.elements.notifications.edit', ['active' => 'notification-handle', 'breadcrumb' => $this->breadcrumb, 'notificationHandle' => $notificationHandle, 'users' => $users]);
    }

    public function delete($id) {
    	$notificationHandle = Notification::find($id);
    	$notificationHandle->is_deleted = 1;
		if ($notificationHandle->save()) {
			return redirect(url('admin/notification-handles'))->with('success', 'Xóa thông báo thành công');
		}
		return redirect(url('admin/notification-handles'))->with('delete', 'Xóa thông báo thất bại');
    }
}
