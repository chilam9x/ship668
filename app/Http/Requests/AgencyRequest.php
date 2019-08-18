<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AgencyRequest extends FormRequest
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
            'phone' => 'required|numeric',
            'discount' => 'required|numeric',
            'collaborator' => 'required',
            'home_number' => 'required',
            'scope' => 'required'
        ];
    }
    public function messages()
    {
        return [
            'required' => 'Trường dữ liêu bắt buộc',
            'numeric' => 'Trường dữ liêu phải là số',
        ];
    }
}
