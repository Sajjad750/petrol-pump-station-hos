<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleFuelGradePriceRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'scheduled_price' => 'required|numeric|min:0',
            'scheduled_at' => 'required|date',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'scheduled_price.required' => 'Scheduled price is required',
            'scheduled_price.numeric' => 'Scheduled price must be a number',
            'scheduled_price.min' => 'Scheduled price must be at least 0',
            'scheduled_at.required' => 'Scheduled at is required',
            'scheduled_at.date' => 'Scheduled at must be a valid date',
        ];
    }
}
