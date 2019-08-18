<?php

namespace App\Http\Requests\FrontEnt;

use App\Models\Province;
use App\Models\District;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class BookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' =>'required',
            'name_fr' =>'required',
            'name_to' =>'required',
            'phone_number_fr' =>'required',
            'phone_number_to' =>'required',
            'province_id_fr' =>'required',
            'district_id_fr' =>'required',
            'ward_id_fr' =>'required',
            'home_number_fr' =>'required',
            'province_id_to' =>'required',
            'district_id_to' =>'required',
            'ward_id_to' =>'required',
            'home_number_to' =>'required',
            'receive_type' =>'required',
            'payment_type' =>'required',
            'transport_type' =>'required',
            'weight' =>'required|numeric|min:0',
            'price' =>'required|numeric|min:0',
            'cod' => 'required|numeric|min:0'
        ];
    }
    protected function getValidatorInstance()
    {
        return parent::getValidatorInstance()->after(function($validator){
            // Call the after method of the FormRequest (see below)
            $this->after($validator);
        });
    }


    public function after($validator)
    {
        $from = $this->request->get('province_id_fr');
        $to = $this->request->get('province_id_to');
        $districtFrom = $this->request->get('district_id_fr');
        $districtTo = $this->request->get('district_id_to');
        $phone_fr = $this->request->get('phone_number_fr');
        $phone_to = $this->request->get('phone_number_to');
        if ($phone_fr != null){
            $send = User::where('phone_number', $phone_fr)->first();
            if ($send != null){
                if ($send->role != 'customer'){
                    $validator->errors()->add('phone_number_fr', 'Số điện thoại gủi không phải của khách hàng');
                }
            }
        }
        if ($phone_to != null){
            $receive = User::where('phone_number', $phone_to)->first();
            if ($receive != null){
                if ($receive->role != 'customer'){
                    $validator->errors()->add('phone_number_to', 'Số điện thoại nhận không phải của khách hàng');
                }
            }
        }
        if ($from != null) {
            $pr = Province::where('id',$from)->first()->active;
            if ($pr != 1) {
                $validator->errors()->add('province_id_fr', 'Chưa áp dụng giao hàng khu vực này');
            }
        }
        if ($to != null) {
            $pr = Province::where('id',$to)->first()->active;
            if ($pr != 1) {
                $validator->errors()->add('province_id_to', 'Chưa áp dụng giao hàng khu vực này');
            }
        }
        if ($districtFrom != null) {
            $pr = District::where('id',$districtFrom)->first()->allow_booking;
            if ($pr != 1) {
                $validator->errors()->add('district_id_fr', 'Chưa áp dụng giao hàng khu vực này');
            }
        }
        if ($districtTo != null) {
            $pr = District::where('id',$districtTo)->first()->allow_booking;
            if ($pr != 1) {
                $validator->errors()->add('district_id_to', 'Chưa áp dụng giao hàng khu vực này');
            }
        }
    }

    public function messages()
    {
        return [
            'required' => 'Trường dữ liêu bắt buộc',
            'numeric' => 'Trường dữ liêu phải là kiểu số',
            'min' => 'Trường dữ liêu tối thiểu phải bằng 0',
        ];
    }
}
