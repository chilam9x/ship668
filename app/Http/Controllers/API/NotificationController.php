<?php
namespace App\Http\Controllers\Api;

use function dd;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Models\Notification;
use App\Models\NotificationUser;
use App\Models\Booking;
use App\Models\BookDelivery;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use DB;

class NotificationController extends ApiController
{

    public function getNotification(Request $req) {
        $limit = $req->limit ? (int) $req->limit : 10;
        $page = $req->page ? (int) $req->page : 0;
        $query = Notification::join('notifications_users', 'notifications.id', '=', 'notifications_users.notification_id')
                    ->where('is_deleted', 0);
        
        if (isset($req->type)) {
            $query = $query->where('type', $req->type);
        }
        if ($req->user()->id) {
            $query = $query->where('user_id', $req->user()->id);
        }
        $query = $query->orderBy('created_at', 'DESC')->paginate($limit);

        $notifications = $query;
        foreach ($notifications as $key => $item) {
            if (!empty($item->booking_id) && $req->user()->role == 'shipper') {
                $bookDelivery = BookDelivery::where('book_id', $item->booking_id)->where('sending_active', 1)->select('id', 'book_id')->first();
                $item->booking_id = $bookDelivery->id;
            }
        }
        return $this->apiOk($notifications);
    }

    public function getNotificationDetail(Request $req) {
        $validator = Validator::make($req->all(), [
            'id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->apiError($validator->errors()->first());
        }

        $notification = Notification::find($req->id);
        if (!$notification) {
            return $this->apiError('Không tìm thấy thông báo.');
        }

        NotificationUser::where('notification_id', $req->id)
                          ->where('user_id', $req->user()->id)
                          ->update(array('is_readed' => 1));

        return $this->apiOk($notification->toArray());
    }

    public function getUnreadCount(Request $req) {
        $query = DB::table('notifications_users')
                    ->join('notifications', 'notifications_users.notification_id', '=', 'notifications.id')
                    ->select('notifications_users.*', 'notifications.*')
                    ->where('is_deleted', 0)
                    ->where('is_readed', 0);
        if (isset($req->type)) {
            $query = $query->where('type', $req->type);
        }
        if ($req->user()->id) {
            $query = $query->where('user_id', $req->user()->id);
        }
        $notifications = $query->count();
        return $this->apiOk($notifications);
    }

    public function readAll(Request $req) {
        if ($req->user()->id) {
            NotificationUser::where('user_id', $req->user()->id)->update(['is_readed' => 1]);
            return $this->apiOk([]);   
        }
        return $this->apiError('User không tồn tại!');
    }
}