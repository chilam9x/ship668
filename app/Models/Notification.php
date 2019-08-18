<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';

    protected $appends = [
        // 'task_id'
    ];

    protected $fillable = [
        // 'id',
        // 'notification',
        // 'user_id',
        // 'title'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function notificationUsers() {
        return $this->hasMany('App\Models\NotificationUser', 'notification_id');
    }

    public function bookDeliveryShipper() {
        return $this->belongsTo('App\Models\bookDelivery', 'booking_id', 'book_id')->where('sending_active', 1);
    }
}
