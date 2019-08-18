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

class NotificationPromotionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $promotion;
    public function __construct($promotion)
    {
        $this->promotion = $promotion;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->notificationPromotion($this->promotion);
    }

    public function test() {
        dd(Auth::user());
    }

    public function notificationPromotion($promotion = null) {
        $users = User::where('status', 'active')
                    ->where('delete_status', 0)
                    ->whereIn('role', array('customer', 'shipper', 'partner')) //'customer', chặn thông báo tới customer
                    ->get();
                    
        $arrUser = [];
        foreach ($users as $user) {
            $arrUser[] = $user->id;
        }
        $devices = Device::whereIn('user_id', $arrUser)->get();
        \Log::info('NotificationPromotion:' . count($devices));

        DB::beginTransaction();
        try {
            $tmpMessage = [];
            foreach ( $users->chunk(100) as $chunk ) {
                $tmpNotificationUser = array();

                foreach ( $chunk as $user ) {
                    $tmp = array(
                        'notification_id' => $promotion->id,
                        'user_id' => $user->id,
                        'is_readed' => 0
                    );
                    $tmpNotificationUser[] = $tmp;

                    $message = array(
                        'notification_id' => intval($promotion->id),
                        'title' => $promotion->title,
                        'type' => $promotion->type,
                        'booking_id' => '',
                        'user_id' => intval($user->id),
                        'start_date' => $promotion->start_date,
                        'end_date' => $promotion->end_date,
                        'badge' => intval($this->getUnreadCount($user->id))
                    );
                    $tmpMessage[] = $message;
                }
                       
                DB::table('notifications_users')->insert($tmpNotificationUser);
                DB::commit();
            }

            foreach ($devices as $device) {
                foreach ($tmpMessage as $message) {
                    if ($device->user_id == $message['user_id']) {
                        // $this->send($device->device_token, 'Chương trình khuyến mãi', $message['title'], $message, $device->device_type, 'push_promotion');
                        dispatch(new PushNotification($device->device_token, 'Chương trình khuyến mãi', $message['title'], $message, $device->device_type, 'push_promotion'));
                    }
                }
            }

        } catch (Exception $e) {
            DB::rollBack();
            dd($e);
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
