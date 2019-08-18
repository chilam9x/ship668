<?php

namespace App\Helpers;

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

class NotificationHelper
{
    public function send($tokens, $title, $body = '', $data = [], $deviceType = 'ios', $collapseKey='')
    {
        if (!isset($tokens) || empty($tokens)) {
            return false;
        }

        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60 * 20);
        $optionBuilder->setCollapseKey($collapseKey);

        $notificationBuilder = new PayloadNotificationBuilder($title);
        $notificationBuilder->setBody($body)
            ->setSound('default');

        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData($data);

        $option = $optionBuilder->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();

        if ($deviceType == 'ios') {
            $downstreamResponse = FCM::sendTo($tokens, $option, $notification, $data);
        } else {
            $downstreamResponse = FCM::sendTo($tokens, $option, null, $data);
        }
        return $downstreamResponse;
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
    */
    public function notificationBooking($booking = null, $toObject = 'admin', $title = ' vừa được tạo', $collapseKey = 'push_order') {
        $db = User::where('status', 'active')->where('delete_status', 0);

        switch ($toObject) {
            case 'admin':
                $db = $db->where('role', 'admin');
                break;
            case 'customer':
                $db = $db->where('role', 'customer')->where('id', $booking->sender_id);
                break;
            case 'shipper':
                $db = $db->where('role', 'shipper')->where('id', $booking->shipper_id);
                break;
            case 'partner':
                // $db = $db->where('role', 'partner')->where('id', $booking->shipper_id);
                break;
            default:
                $db = $db->where('role', 'admin');
                break;
        }

        $user = $db->first();
        $devices = array();
        $devices = Device::where('user_id', $user->id)->get();

        DB::beginTransaction();
        try {
            $notificationId = 0;
            $notification = Notification::where('booking_id', $booking->id)->where('title', 'Đơn hàng [' . $booking->uuid . ']' . $title)->first();
            if (empty($notification)) {
                $notification = new Notification();
                $notification->title = 'Đơn hàng [' . $booking->uuid . ']' . $title;
                $notification->booking_id = $booking->id;
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
                if ($notificationUser->save()) {
                    DB::commit();

                    $message = array(
                        'notification_id' => $notificationId,
                        'title' => $notification->title,
                        'type' => $notification->type,
                        'booking_id' => $booking->id,
                        'user_id' => $notificationUser->user_id
                    );

                    if (count($devices) > 0 && !empty($devices)) {
                        foreach ($devices as $device) {
                            $status = $this->send($device->device_token, $notification->title, $notification->title, $message, $device->device_type, $collapseKey);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            DB::rollBack();
            dd($e);
        }
    }

    public function notificationPromotion($promotion = null) {
        $users = User::where('status', 'active')
                    ->where('delete_status', 0)
                    ->whereIn('role', array('customer', 'shipper', 'partner'))
                    ->get();
        $devices = array();
        $userIdArr = array();
        if (count($users) > 0 && !empty($users)) {
            foreach ($users as $user) {
                $userIdArr[] = $user->id;
            }
        }
        $devices = Device::whereIn('user_id', $userIdArr)->get();

        DB::beginTransaction();
        try {
            $tmpNotificationUser = array();
            $tmpMessage = array();
            foreach ( $users as $user ) {
                $tmp = array(
                    'notification_id' => $promotion->id,
                    'user_id' => $user->id,
                    'is_readed' => 0
                );
                $tmpNotificationUser[] = $tmp;

                $message = array(
                    'notification_id' => $promotion->id,
                    'title' => $promotion->title,
                    'type' => $promotion->type,
                    'booking_id' => '',
                    'user_id' => $user->id,
                    'start_date' => $promotion->start_date,
                    'end_date' => $promotion->end_date
                );
                $tmpMessage[] = $message;
            }
            if (DB::table('notifications_users')->insert($tmpNotificationUser)) {
                DB::commit();

                if (count($devices) > 0 && !empty($devices) && count($tmpMessage) > 0 && !empty($tmpMessage)) {
                    foreach ($devices as $device) {
                        foreach ($tmpMessage as $message) {
                            if ($device->user_id == $message['user_id']) {
                                $status = $this->send($device->device_token, $message['title'], $message['title'], $message, $device->device_type, 'push_promotion');
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            DB::rollBack();
            dd($e);
        }
    }
}