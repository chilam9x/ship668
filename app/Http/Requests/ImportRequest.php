<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportRequest extends FormRequest
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
            'import' => 'required|mimes:xlsx, xls'
        ];
    }

    public function messages()
    {
        return [
          'required' => 'Không có file dữ liệu được tải lên',
          'mimes' => 'File tải lên phải có định dạng là xlsx hoặc xls'
        ];
    }
}
