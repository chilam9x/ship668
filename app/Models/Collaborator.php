<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Collaborator extends Model
{
    protected $table = 'collaborators';

    protected $appends = ['agency_name'];

    protected $fillable = [
        'user_id',
        'agency_id',
    ];

    public function users(){
        return $this->belongsTo('App\Models\User', 'user_id');
    }
    public function agencies(){
        return $this->belongsTo('App\Models\Agency', 'agency_id');
    }
    public function getAgencyNameAttribute(){
        return $this->agencies != null ? $this->agencies->name : '';
    }
}
