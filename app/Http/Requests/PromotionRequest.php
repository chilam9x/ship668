<?php

namespace App\Http\Requests;

use function dd;
use Illuminate\Foundation\Http\FormRequest;

class PromotionRequest extends FormRequest
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
            // 'name' => 'required',
            // 'password' => 'required',
            // 'cf-password' => 'required|same:password',
            // 'email' => 'required|email|unique:users,email,' . $this->route('collaborator'),
            // 'birth_day' => 'required',
            // 'home_number' => 'required',
            // 'phone_number' => 'required|min:10|max:13|unique:users,phone_number,' . $this->route('collaborator'),
            // 'id_number' => 'required|unique:users,id_number,' . $this->route('collaborator'),
//            'bank_account_number' => 'unique:users,bank_account_number,' . $this->route('collaborator'),
        ];
        return $rule;
    }

    public function messages()
    {
        return [
            // 'required' => 'Trường dữ liêu bắt buộc',
            // 'same' => 'Mật khẩu xác nhận không trùng khớp',
            // 'email' => 'Trường dữ liêu không đúng định dạng email',
            // 'unique' => 'Dữ liệu đã tồn tại',
            // 'phone_number.min' => 'Phải nhập ít nhất 10 kí tự',
            // 'phone_number.max' => 'Dữ liệu tối đa 13 kí tự',
        ];
    }
}
