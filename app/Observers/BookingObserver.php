<?php

namespace App\Observers;

use App\Models\Booking;
use App\Observers\AbstractObserver;
use App\Models\BookingTransactionService;
use App\Models\Setting;
class BookingObserver extends AbstractObserver {

    public function created($model) {
        if (!empty($model->transport_type_services)) {
            $services = explode(',', $model->transport_type_services);
            $hasUpdated = 0;
            $dataInsert = [];
            foreach($services as $item){
                $service = Setting::where(['id'=>$item,'type'=>'transport_type'])->first();
                if (!empty($service)) {
                    $dataInsert[] = [
                        'user_id' => $model->sender_id,
                        'book_id' => $model->id,
                        'key' => $service->key,
                        'service_id' => $item,
                        'price' => $service->value
                    ];
                    $hasUpdated = 1;
                
                }
            }
   
            if($hasUpdated ==1){
              //  BookingTransactionService::where('book_id',$model->id)->update(['status'=>0]);
                BookingTransactionService::insert($dataInsert);
            }
        
        }
    }

    public function saved($model){
//        if (!empty($model->transport_type_services)) {
//            $services = explode(',', $model->transport_type_services);
//            $hasUpdated = 0;
//            $dataInsert = [];
//            foreach($services as $item){
//                $service = Setting::where(['id'=>$item,'type'=>'transport_type_des'])->first();
//                if (!empty($service)) {
//                    $dataInsert[] = [
//                        'user_id' => $model->sender_id,
//                        'book_id' => $model->id,
//                        'key' => $service->key,
//                        'service_id' => $item,
//                        'price' => $service->value
//                    ];
//                    $hasUpdated = 1;
//                
//                }
//            }
//   
//            if($hasUpdated ==1){
//              //  BookingTransactionService::where('book_id',$model->id)->update(['status'=>0]);
//                BookingTransactionService::insert($dataInsert);
//            }
//        
//        }
    }

    public function saving($model){
        
    }

    public function deleted($model){
        
    }

    public function deleting($model){
        
    }
}
