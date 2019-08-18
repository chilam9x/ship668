<?php

namespace App\Http\Controllers\UI;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use \Auth;

class CODController extends Controller
{
    public function __construct()
    {
        $this->middleware('ui.auth');
    }

    public function pendingCOD(){
    	$db = Booking::where('sender_id', Auth::user()->id)
        			->where('COD_status', 'pending')
        			->where('COD', '>', '0')
        			->where('status', 'completed')
        			->orderBy('created_at', 'desc');
		$count = $db->sum('COD');
		$bookings = $db->paginate(10);
        return view('front-ent.element.cod.index', ['bookings' => $bookings, 'active' => 'pending', 'count' => $count]);
    }

    public function finishCOD(){
        $db = Booking::where('sender_id', Auth::user()->id)
        			->where('COD_status', 'finish')
        			->where('COD', '>', '0')
        			->where('status', 'completed')
        			->orderBy('completed_at', 'desc');
        $count = $db->sum('COD');
        $bookings = $db->paginate(10);
        return view('front-ent.element.cod.index', ['bookings' => $bookings, 'active' => 'finish', 'count' => $count]);
    }
}
