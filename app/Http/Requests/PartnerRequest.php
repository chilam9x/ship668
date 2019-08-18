<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PartnerRequest extends FormRequest
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
        $rule = [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$this->route('partner'),
            'home_number' => 'required',
            'phone_number' => 'required|min:8|max:15|unique:users,phone_number,'.$this->route('partner'),
        ];
        return $rule;
    }
    public function messages()
    {
        return [
            'required' => 'Trường dữ liêu bắt buộc',
            'email' => 'Trường dữ liêu không đúng định dạng email',
            'unique' => 'Dữ liệu đã tồn tại',
            'phone_number.min' => 'Phải nhập ít nhất 8 kí tự',
            'phone_number.max' => 'Dữ liệu tối đa 15 kí tự',
        ];
    }
}
