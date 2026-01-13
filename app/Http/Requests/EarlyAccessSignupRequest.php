<?php

namespace App\Http\Requests;

use App\Models\EarlyAccessSignup;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EarlyAccessSignupRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(EarlyAccessSignup::class),
            ],
            'selected_topics' => [
                'nullable',
                'array',
            ],
            'selected_topics.*' => [
                'string',
                Rule::in([
                    'critical-thinking',
                    'active-listening',
                    'first-principles',
                    'strategic-thinking',
                    'clear-communication',
                    'thinking-under-pressure',
                ]),
            ],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'email.unique' => "You're already on the list!",
        ];
    }
}
