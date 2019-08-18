<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;
use Auth;
use App\Models\Notification;
use App\Models\NotificationUser;
use DB;
use App\Models\User;
use App\Models\Device;
use App\Jobs\PushNotification;

class NotificationHandleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $notificationHandle;
    protected $userIdArr;
    public function __construct($notificationHandle, $userIdArr)
    {
        $this->notificationHandle = $notificationHandle;
        $this->userIdArr = $userIdArr;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->notificationHandle($this->notificationHandle, $this->userIdArr);
    }

    public function test() {
        dd(Auth::user());
    }

    /*
    đơn hàng mới:                                           push_order
    phân công shipper đơn hàng:                             push_order_assign
    thay đổi trạng thái đơn hàng :                          push_order_change
    Thông báo tới khách hàng khi đã thanh toán tiền nợ:     push_customer_owe
    thông báo tin khuyến mãi:                               push_promotion
    thống báo thủ công từ admin                             push_handle
    */

    public function notificationHandle($notificationHandle = null, $userIdArr) {
        $devices = Device::whereIn('user_id', $userIdArr)->get();
        \Log::info('NotificationHandle:' .count($devices));

        if (count($devices) > 0 && !empty($devices)) {
            foreach ($devices as $device) {
                $notificationHandle['badge'] = intval($this->getUnreadCount($device->user_id));
                // $this->send($device->device_token, 'Thông báo hệ thống', $notificationHandle['title'], $notificationHandle, $device->device_type, 'push_handle');
                \Log::info('Push notification to user: ' . $device->user_id);
                dispatch(new PushNotification($device->device_token, 'Thông báo hệ thống', $notificationHandle['title'], $notificationHandle, $device->device_type, 'push_handle'));
            }
        }
    }

    public function getUnreadCount($user_id) {
        $query = DB::table('notifications_users')
                    ->join('notifications', 'notifications_users.notification_id', '=', 'notifications.id')
                    ->select('notifications_users.*', 'notifications.*')
                    ->where('is_deleted', 0)
                    ->where('is_readed', 0)
                    ->where('user_id', $user_id);
        $notifications = $query->count();
        return $notifications;
    }
}
