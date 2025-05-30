<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $role = $this->route('role');
        
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles')->ignore($role)
            ],
            'guard_name' => 'sometimes|string|max:255',
            'permissions' => 'sometimes|array',
            'permissions.*' => 'exists:permissions,id'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Název role je povinný.',
            'name.unique' => 'Role s tímto názvem již existuje.',
            'name.max' => 'Název role může mít maximálně 255 znaků.',
            'permissions.array' => 'Oprávnění musí být pole.',
            'permissions.*.exists' => 'Vybrané oprávnění neexistuje.'
        ];
    }
    
}