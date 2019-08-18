<?php
namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

class MessagesController extends ApiController
{
 /**
 * Show greetings
 * 
 * @param Request $request [description]
 * @return [type] [description]
 */
 public function index(Request $request)
 {
   $data = [
     'Việt Nam' => trans('messages.Vietnames'),
     'Đường' => trans('messages.suger')
   ];
   return response()->json($data, 200);
 }
}