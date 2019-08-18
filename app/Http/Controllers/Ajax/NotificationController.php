<?php

namespace App\Http\Controllers\Ajax;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\NotificationUser;
use Auth;
use Form, DB;
use App\Jobs\NotificationHandleJob;

class NotificationController extends Controller
{
    public function getNotification() {
    	$db = DB::table('notifications_users')
            ->join('notifications', 'notifications_users.notification_id', '=', 'notifications.id')
            ->select('notifications_users.*', 'notifications.*')
    		->where('notifications_users.user_id', Auth::user()->id);

        $notifications = $db->orderBy('notifications.created_at', 'DESC')
            ->limit(20)
            ->get();

        $countNotReaded = $db->where('is_readed', 0)->count();
        return response()->json(array('notifications' => $notifications, 'countNotReaded' => $countNotReaded));
    }

    public function getNotificationHandle() {
        $notificationHandles = Notification::where('type', 'handle')->where('is_deleted', 0);
        return datatables()->of($notificationHandles)
            ->addColumn('action', function ($notificationHandles) {
                $action = [];
                $action[] = '<a style="float:left" href="' . url('admin/notification-handles/edit/' . $notificationHandles->id) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Chi tiết</a>';
                $action[] = '<div style="float: left"><a href="' . url('/admin/notification-handles/delete/' . $notificationHandles->id) . '" class="btn btn-xs btn-danger"><i class="fa fa-trash-o"></i> Xóa</a>' .
                    Form::close() . '</div>';
                return implode(' ', $action);
            })
            ->make(true);
    }

    public function addNotificationHandle() {
        if (request()->user_id && request()->user_id > 0) {
            DB::beginTransaction();
            try {
                $notificationHandle = new Notification;
                $notificationHandle->title = request()->title;
                $notificationHandle->content = request()->content;
                $notificationHandle->type = 'handle';
                $notificationHandle->save();

                $notificationUser = new NotificationUser;
                $notificationUser->notification_id = $notificationHandle->id;
                $notificationUser->user_id = request()->user_id;
                $notificationUser->save();
                DB::commit();

                // thông báo
                $userIdArr[] = request()->user_id;
                $notificationHandleArr = array(
                    'notification_id' => intval($notificationHandle->id),
                    'title' => $notificationHandle->title,
                    'body' => $notificationHandle->content,
                    'type' => $notificationHandle->type
                );
                dispatch(new NotificationHandleJob($notificationHandleArr, $userIdArr));
                
                return response()->json(array('status' => true));
            } catch (Exception $e) {
                DB::rollback();
                return response()->json(array('status' => false));
            }
        }
    }
}
