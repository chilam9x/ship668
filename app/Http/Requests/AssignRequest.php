<?php

namespace App\Http\Requests;

use function dd;
use Illuminate\Foundation\Http\FormRequest;

class AssignRequest extends FormRequest
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
            'shipper' => 'required',
            'incurred' => 'required|numeric'
        ];
        if ($this->route('id') != null) {
            unset($rule['incurred']);
        }
        return $rule;
    }

    public function messages()
    {
        return [
            'required' => 'Trường dữ liêu bắt buộc',
            'numeric' => 'Trường dữ liêu phải là số',
        ];
    }
}
