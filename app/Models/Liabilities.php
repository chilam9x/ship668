<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Liabilities extends Model
{
    protected $table = 'liabilities';

    protected $fillable = [
        'agency_id', 'turnover_paid', 'discount_paid'
    ];
}
