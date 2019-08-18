<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Accuracy extends Model
{
    protected $table = 'accuracies';

    protected $fillable = [
        'id',
        'user_id',
        'content',
    ];

    public function users()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
}
