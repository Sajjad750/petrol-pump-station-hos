<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleBulkFuelGradePriceRequest extends FormRequest
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
            'fuel_grade_name' => 'required|string',
            'station_ids' => 'required|array|min:1',
            'station_ids.*' => 'required|integer|exists:stations,id',
            'scheduled_price' => 'required|numeric|min:0',
            'scheduled_at' => 'required|date',
            'user_timezone' => 'nullable|string',
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
            'fuel_grade_name.required' => 'Fuel grade name is required',
            'station_ids.required' => 'At least one station must be selected',
            'station_ids.array' => 'Station IDs must be an array',
            'station_ids.min' => 'At least one station must be selected',
            'station_ids.*.required' => 'Each station ID is required',
            'station_ids.*.integer' => 'Each station ID must be an integer',
            'station_ids.*.exists' => 'One or more selected stations do not exist',
            'scheduled_price.required' => 'Scheduled price is required',
            'scheduled_price.numeric' => 'Scheduled price must be a number',
            'scheduled_price.min' => 'Scheduled price must be at least 0',
            'scheduled_at.required' => 'Scheduled at is required',
            'scheduled_at.date' => 'Scheduled at must be a valid date',
        ];
    }
}
