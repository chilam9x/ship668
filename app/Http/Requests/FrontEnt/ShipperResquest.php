<?php

namespace App\Http\Requests\FrontEnt;

use Illuminate\Foundation\Http\FormRequest;

class ShipperResquest extends FormRequest
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
        'name' => 'required',
        'email' => 'required|email|unique:shipper_registers,email',
        'birth_day' => 'required',
        'home_number' => 'required',
        'phone_number' => 'required|numeric|unique:shipper_registers,phone_number',
        'id_number' => 'required|unique:shipper_registers,id_number',
        'bank_account' => 'required',
        'bank_account_number' => 'required|numeric',
        'bank_name' => 'required',
        'bank_branch' => 'required'];
    }
    public function messages()
    {
        return [
            'required' => 'Trường dữ liêu bắt buộc',
            'email' => 'Trường dữ liêu không đúng định dạng email',
            'unique' => 'Dữ liệu đã tồn tại',
            'numeric' => 'Dữ liệu phải là kiểu số'
        ];
    }
}
