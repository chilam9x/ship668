<?php

namespace App\Listeners;

use App\Events\GetLocationShipper;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use LRedis;

class PushLocationShipperToSocket
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  GetLocationShipper  $event
     * @return void
     */
    public function handle(GetLocationShipper $event)
    {
        Log::info('Push location: ' . $event->message['lat'] . '-' . $event->message['lng'] . ' shipper to socket.io: ' . date('Y-m-d H:i:s'));
        $redis = LRedis::connection();
        $redis->publish('message', json_encode($event->message));
    }
}
