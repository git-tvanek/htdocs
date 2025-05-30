<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PermissionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $permission = $this->route('permission');
        
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('permissions')->ignore($permission)
            ],
            'guard_name' => 'sometimes|string|max:255'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Název oprávnění je povinný.',
            'name.unique' => 'Oprávnění s tímto názvem již existuje.',
            'name.max' => 'Název oprávnění může mít maximálně 255 znaků.'
        ];
    }
}