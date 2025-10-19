<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncFuelGradeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'pts_id' => 'required|string|max:255',
            'data' => 'required|array|min:1',
            'data.*.id' => 'required|integer',
            'data.*.uuid' => 'required|uuid',
            'data.*.pts_fuel_grade_id' => 'nullable|string|max:255',
            'data.*.name' => 'required|string|max:255',
            'data.*.price' => 'required|numeric|min:0',
            'data.*.scheduled_price' => 'nullable|numeric|min:0',
            'data.*.scheduled_at' => 'nullable|date',
            'data.*.expansion_coefficient' => 'nullable|numeric|min:0',
            'data.*.blend_tank1_id' => 'nullable|integer|min:0|max:255',
            'data.*.blend_tank1_percentage' => 'nullable|integer|min:1|max:99',
            'data.*.blend_tank2_id' => 'nullable|integer|min:0|max:255',
            'data.*.created_at' => 'nullable|date',
            'data.*.updated_at' => 'nullable|date',
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
            'pts_id.required' => 'PTS ID is required',
            'pts_id.string' => 'PTS ID must be a string',
            'data.required' => 'Fuel grade data is required',
            'data.array' => 'Fuel grade data must be an array',
            'data.min' => 'At least one fuel grade must be provided',
            'data.*.id.required' => 'Fuel grade ID is required for each grade',
            'data.*.id.integer' => 'Fuel grade ID must be an integer',
            'data.*.uuid.required' => 'Fuel grade UUID is required for each grade',
            'data.*.uuid.uuid' => 'Fuel grade UUID must be a valid UUID',
            'data.*.pts_fuel_grade_id.string' => 'PTS fuel grade ID must be a string',
            'data.*.name.required' => 'Fuel grade name is required for each grade',
            'data.*.name.string' => 'Fuel grade name must be a string',
            'data.*.price.required' => 'Price is required for each fuel grade',
            'data.*.price.numeric' => 'Price must be a number',
            'data.*.price.min' => 'Price must be greater than or equal to 0',
            'data.*.scheduled_price.numeric' => 'Scheduled price must be a number',
            'data.*.scheduled_price.min' => 'Scheduled price must be greater than or equal to 0',
            'data.*.scheduled_at.date' => 'Scheduled at must be a valid date',
            'data.*.expansion_coefficient.numeric' => 'Expansion coefficient must be a number',
            'data.*.expansion_coefficient.min' => 'Expansion coefficient must be greater than or equal to 0',
            'data.*.blend_tank1_id.integer' => 'Blend tank 1 ID must be an integer',
            'data.*.blend_tank1_id.min' => 'Blend tank 1 ID must be between 0 and 255',
            'data.*.blend_tank1_id.max' => 'Blend tank 1 ID must be between 0 and 255',
            'data.*.blend_tank1_percentage.integer' => 'Blend tank 1 percentage must be an integer',
            'data.*.blend_tank1_percentage.min' => 'Blend tank 1 percentage must be between 1 and 99',
            'data.*.blend_tank1_percentage.max' => 'Blend tank 1 percentage must be between 1 and 99',
            'data.*.blend_tank2_id.integer' => 'Blend tank 2 ID must be an integer',
            'data.*.blend_tank2_id.min' => 'Blend tank 2 ID must be between 0 and 255',
            'data.*.blend_tank2_id.max' => 'Blend tank 2 ID must be between 0 and 255',
            'data.*.created_at.date' => 'Created at must be a valid date',
            'data.*.updated_at.date' => 'Updated at must be a valid date',
        ];
    }

    /**
     * Get custom attribute names for validation errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'pts_id' => 'PTS ID',
            'data' => 'fuel grade data',
            'data.*.id' => 'fuel grade ID',
            'data.*.uuid' => 'fuel grade UUID',
            'data.*.pts_fuel_grade_id' => 'PTS fuel grade ID',
            'data.*.name' => 'fuel grade name',
            'data.*.price' => 'price',
            'data.*.scheduled_price' => 'scheduled price',
            'data.*.scheduled_at' => 'scheduled at',
            'data.*.expansion_coefficient' => 'expansion coefficient',
            'data.*.blend_tank1_id' => 'blend tank 1 ID',
            'data.*.blend_tank1_percentage' => 'blend tank 1 percentage',
            'data.*.blend_tank2_id' => 'blend tank 2 ID',
        ];
    }
}
