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

class PushNotificationBook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $tokens, $title, $body, $data, $deviceType, $collapseKey;

    public function __construct($tokens, $title, $body, $data, $deviceType, $collapseKey)
    {
        $this->tokens = $tokens;
        $this->title = $title;
        $this->body = $body;
        $this->data = $data;
        $this->deviceType = $deviceType;
        $this->collapseKey = $collapseKey;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->send($this->tokens, $this->title, $this->body, $this->data, $this->deviceType, $this->collapseKey);
    }

    public function send($tokens, $title, $body = '', $data = [], $deviceType = 'ios', $collapseKey='')
    {
        if (!isset($tokens) || empty($tokens)) {
            return false;
        }

        if ($deviceType == 'ios') {
            $data['collapse_key'] = $collapseKey;
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
        \Log::info('Start' . date('Y-m-d H:i:s'));
        if ($deviceType == 'ios') {
            $downstreamResponse = FCM::sendTo($tokens, $option, $notification, $data);
        } else {
            $downstreamResponse = FCM::sendTo($tokens, $option, null, $data);
        }
        return $downstreamResponse;
    }
}
