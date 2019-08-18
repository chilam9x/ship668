<?php

namespace App\Http\Requests\FrontEnt;

use Illuminate\Foundation\Http\FormRequest;

class FeedbackRequest extends FormRequest
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
            'email' => 'required|email',
            'phone' => 'required|numeric',
            'contents' => 'required',
        ];
    }
    public function messages()
    {
        return [
            'required' => 'Trường dữ liêu bắt buộc',
            'email' => 'Trường dữ liêu không đúng định dạng email',
            'numeric' => 'Dữ liệu phải là kiểu số'
        ];
    }
}
