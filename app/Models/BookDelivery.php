<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookDelivery extends Model
{
    protected $table = 'book_deliveries';

    protected $appends = ['shipper_name','category_name','shipper_phone','shipper_info'];

    protected $fillable = [
        'user_id', 'book_id', 'send_address', 'send_lat', 'send_lng',
        'receive_address', 'receive_lat', 'receive_lng', 'status', 'category', 'total_delay'
    ];


    public function users(){
        return $this->belongsTo('App\Models\User', 'user_id')->select('id','uuid','name','phone_number','home_number');
    }

    public function booking(){
        return $this->belongsTo('App\Models\Booking', 'book_id');
    }
    public function reportImage(){
        return $this->hasMany('App\Models\ReportImage', 'task_id')->orderBy('id','DESC');
    }
    
    public function getCategoryNameAttribute()
    {
        if (isset($this->category) && $this->category =='return') {
            return 'Yêu cầu trả lại';
        }
        return '';
    }
    public function getShipperInfoAttribute(){
        if (isset($this->users)) {
            $name =  $this->users->name != null ? $this->users->name : '';
            $phone = $this->users->phone_number;
            $home = $this->users->home_number;
            return [
                'name'=>$name,
                'phone_number'=>$phone,
                'home_number'=>$home
            ];
        }
        return [];
    }
    public function getShipperPhoneAttribute() {
        if (isset($this->users)) {
            return $this->users->phone_number != null ? $this->users->phone_number : '';
        }
        return '';
    }

    public function getShipperNameAttribute()
    {
        if (isset($this->users)) {
            return $this->users->name != null ? $this->users->name : '';
        }
        return '';
    }

    public function updateStatus($id, $status){
        switch ($status){
            case 'received':
                $this->where('book_id', $id)->update(['status'=> 'transporting']);
            case 'sent':
                $this->where('book_id', $id)->update(['status'=> 'finish']);
            case 'return':
                $this->where('book_id', $id)->update(['status'=> 'transporting']);
        }
    }
}
