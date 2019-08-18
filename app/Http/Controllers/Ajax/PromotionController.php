<?php

namespace App\Http\Controllers\Ajax;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\NotificationUser;
use Auth;
use Form, DB;

class PromotionController extends Controller
{

    public function getPromotion()
    {
        $promotion = Notification::where('type', 'promotion')->where('is_deleted', 0)->orderBy('created_at', 'DESC');
        return datatables()->of($promotion)
            ->addColumn('action', function ($promotion) {
                $action = [];
                $action[] = '<a style="float:left" href="' . url('admin/promotions/' . $promotion->id . '/edit') . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Sửa</a>';
                $action[] = '<div style="float: left">' . Form::open(['method' => 'DELETE', 'url' => ['admin/promotions/' . $promotion->id]]) .
                    '<button class="btn btn-xs btn-danger" type="submit"><i class="fa fa-trash-o"></i> Xóa</button>' .
                    Form::close() . '</div>';
                return implode(' ', $action);
            })
            ->make(true);
    }
}
