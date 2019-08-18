<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DiscountRequest extends FormRequest
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
            'type'=>'required|regex:/^\S*$/u',
            'key' => 'required',
            'name' => 'required',
            'value' => 'required|numeric'
        ];
    }
    public function messages()
    {
        return [
            'required' => 'Trường dữ liêu bắt buộc',
            'type.regex' => 'Dữ liệu không được có khoảng cách',
            'value.integer' => 'Dữ liệu phải là số nguyên',
        ];
    }
}
