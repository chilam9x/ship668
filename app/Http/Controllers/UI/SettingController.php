<?php

namespace App\Http\Controllers\UI;

use App\Http\Requests\FrontEnt\FeedbackRequest;
use App\Models\Feedback;
use App\Models\Policy;
use function dd;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function feedback(FeedbackRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = new Feedback();
            $data->name = $request->name;
            $data->email = $request->email;
            $data->phone = $request->phone;
            $data->content = $request->contents;
            $data->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
        }
        $request->session()->flash('success', 'Gửi phản hồi thành công! cảm ơn bạn đã phản hồi đến hệ thống!');
        return redirect(url('/'));
    }

    public function policy() {
        $policy = Policy::where('id', '>', 0)->first();
        return view('front-ent.element.policy', ['policy' => $policy]);
    }
}
