<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Version extends Model
{
    protected $table = 'versions';

    protected $fillable = [
        'version_code', 'version_name', 'description',
        'force_upgrade', 'category', 'device_type'];
}
