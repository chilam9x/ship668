<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportImage extends Model
{
    protected $table = 'report_images';

    protected $fillable = [
        'task_id',
        'image',
    ];

    public function bookingDelivery() {
        return $this->belongsTo('App\Models\BookDelivery', 'task_id');
    }

}
