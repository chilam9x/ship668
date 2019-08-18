<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DistrictType extends Model
{
    protected $table = 'district_types';

    protected $fillable = [
        'name',
    ];

    static function getAllOption()
    {
        $data = [];
        $province = DistrictType::all();
        foreach ($province as $p) {
            $data[$p->id] = $p->name;
        }
        return $data;
    }
}
