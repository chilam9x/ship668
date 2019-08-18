<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationUser extends Model
{
    protected $table = 'notifications_users';
    public $timestamps = false;

    public function notification() {
    	return $this->belongsTo('App\Models\Notification', 'notification_id');
    }
}
