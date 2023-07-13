<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            "title" => ["sometimes", "max:255"],
            "desc" => ["sometimes", "max:255"],
            "public" => ["sometimes", "boolean"],
            'categories' => 'sometimes|array',
            'categories.*' => 'sometimes|numeric',
        ];
    }
}
