<?php

namespace App\Http\Controllers\UI;

use App\Models\User;
use function dd;
use function redirect;
use \Validator, \Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\NotificationUser;
use DB;

class NotificationController extends Controller
{
    public function __construct() {
        $this->middleware('ui.auth');
    }

    public function index() {
        if (!isset(request()->type)) {
            request()->type = 'promotion';
        }
        $notifications = DB::table('notifications_users')
            ->join('notifications', 'notifications_users.notification_id', '=', 'notifications.id')
            ->select('notifications_users.*', 'notifications.*')
            ->where('notifications_users.user_id', Auth::user()->id)
            ->where('type', request()->type)
            ->where('is_deleted', 0)
            ->orderBy('notifications.created_at', 'DESC')
            ->orderBy('notifications_users.is_readed', 'DESC')
            ->paginate(10);
        return view('front-ent.element.notification.index', array('notifications' => $notifications, 'active' => request()->type));
    }

    public function detail($id) {
        if (!isset(request()->type)) {
            request()->type = 'promotion';
        }
        if (!isset(request()->page)) {
            request()->page = 1;
        }
        $notification = Notification::find($id);
        NotificationUser::where('notification_id', $id)->where('user_id', Auth::user()->id)->update(array('is_readed' => 1));
        return view('front-ent.element.notification.detail', array('notification' => $notification, 'active' => request()->type, 'type' => request()->type, 'page' => request()->page));
    }
}
