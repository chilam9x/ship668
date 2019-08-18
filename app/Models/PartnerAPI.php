<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerAPI extends Model
{
    protected $table = 'partner_a_p_is';

    protected $fillable = [
        'partner_id',
        'api_content',
    ];

}
