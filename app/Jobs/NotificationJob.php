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
use App\Jobs\PushNotificationBook;

class NotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $booking;
    protected $toObject;
    protected $title;
    protected $collapseKey;
    public function __construct($booking, $toObject, $title, $collapseKey)
    {
        $this->booking = $booking;
        $this->toObject = $toObject;
        $this->title = $title;
        $this->collapseKey = $collapseKey;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->notificationBooking($this->booking, $this->toObject, $this->title, $this->collapseKey);
    }

    public function test() {
        dd(Auth::user());
    }

    public function notificationBooking($booking = null, $toObject = 'admin', $title = ' vừa được tạo', $collapseKey = 'push_order') {
        $db = User::where('status', 'active')->where('delete_status', 0);
        switch ($toObject) {
            case 'admin':
                $db = $db->where('role', 'admin');
                break;
            case 'customer':
                $db = $db->where('role', 'customer')->where('id', $booking['sender_id']);
                break;
            case 'shipper':
                $db = $db->where('role', 'shipper')->where('id', $booking['shipper_id']);
                break;
            case 'partner':
                // $db = $db->where('role', 'partner')->where('id', $booking->shipper_id);
                break;
            default:
                // $db = $db->where('role', 'admin');
                break;
        }

        $user = $db->first();
        if (!empty($user)) {
            $devices = Device::where('user_id', $user->id)->get();
            \Log::info('NotificationBook:' . count($devices));
            // không gửi thông báo tới customer
            // if ($toObject == 'customer') {
            //     $devices = null;
            // }

            DB::beginTransaction();
            try {
                $notificationId = 0;
                $notification = Notification::where('booking_id', $booking['id'])->where('title', 'Đơn hàng [' . $booking['uuid'] . ']' . $title)->first();
                if (empty($notification)) {
                    $notification = new Notification();
                    $notification->title = 'Đơn hàng [' . $booking['uuid'] . ']' . $title;
                    $notification->booking_id = $booking['id'];
                    $notification->type = 'book';
                    $notification->save();
                    $notificationId = $notification->id;
                } else {
                    $notificationId = $notification->id;
                }
                if ($notificationId != 0) {
                    $notificationUser = new NotificationUser();
                    $notificationUser->notification_id = $notificationId;
                    $notificationUser->user_id = $user->id;
                    $notificationUser->is_readed = 0;
                    $notificationUser->save();
                    DB::commit();

                    $message = array(
                        'notification_id' => intval($notificationId),
                        'title' => $notification->title,
                        'type' => $notification->type,
                        'booking_id' => intval($booking['id']),
                        'user_id' => intval($notificationUser->user_id),
                        'body' => $notification->content,
                        'booking_status' => $booking['status'],
                        'amount' => intval($booking['price']),
                        'badge' => intval($this->getUnreadCount($notificationUser->user_id))
                    );
                    if ($toObject == 'shipper') {
                        $message['booking_id'] = isset($booking['book_delivery_id']) ? intval($booking['book_delivery_id']) : intval($booking['id']);
                    }

                    if (count($devices) > 0 && !empty($devices)) {
                        foreach ($devices as $device) {
                            // $this->send($device->device_token, $notification->title, $notification->title, $message, $device->device_type, $collapseKey);
                            dispatch(new PushNotificationBook($device->device_token, $notification->title, $notification->title, $message, $device->device_type, $collapseKey));
                        }
                    }

                }
            } catch (Exception $e) {
                DB::rollBack();
                dd($e);
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
