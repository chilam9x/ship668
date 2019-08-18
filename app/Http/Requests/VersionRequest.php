<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VersionRequest extends FormRequest
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
            'version_code' => 'required',
            'version_name' => 'required',
            'force_upgrade' => 'required',
            'description' => 'required',
        ];
    }

    public function messages()
    {
        return [
          'required' => 'Trường dữ liệu bắt buộc'
        ];
    }
}
