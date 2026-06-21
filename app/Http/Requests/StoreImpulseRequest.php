<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreImpulseRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'dependency_id' => [
                'required',
                Rule::exists('dependencies', 'id')->where('user_id', $this->user()->id),
            ],
            'resisted' => ['required', 'boolean'],
            'trigger'  => ['nullable', 'string', 'max:255'],
            'comment'  => ['nullable', 'string', 'max:2000'],
        ];
    }
}
