<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
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
            // 'email' => 'required|email|unique:users,email,'.$this->route('customer'),
            // 'birth_day' => 'required',
            'home_number' => 'required',
            'phone_number' => 'required|unique:users,phone_number',
            // 'id_number' => 'required|unique:users,id_number,'.$this->route('customer'),
//            'bank_account_number' => 'unique:users,bank_account_number,'.$this->route('customer'),
        ];
        if ($this->method() == 'PUT')
        {
            $rule['delivery_address'] = 'required';
            unset($rule['home_number']);
            $rule['phone_number'] = 'required';
        }
        return $rule;
    }
    public function messages()
    {
        return [
            'required' => 'Trường dữ liêu bắt buộc',
            'email' => 'Trường dữ liêu không đúng định dạng email',
            'unique' => 'Dữ liệu đã tồn tại',
            'phone_number.min' => 'Phải nhập ít nhất 10 kí tự',
            'phone_number.max' => 'Dữ liệu tối đa 13 kí tự',
        ];
    }
}
