<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncProductWiseSummaryRequest extends FormRequest
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
            'data.*.shift_id' => 'required|integer',
            'data.*.fuel_grade_id' => 'required|integer',
            'data.*.total_volume' => 'required|numeric|min:0',
            'data.*.total_amount' => 'required|numeric|min:0',
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
            'data.required' => 'Product wise summary data is required',
            'data.array' => 'Product wise summary data must be an array',
            'data.min' => 'At least one product wise summary must be provided',
            'data.*.id.required' => 'Summary ID is required for each summary',
            'data.*.id.integer' => 'Summary ID must be an integer',
            'data.*.uuid.required' => 'Summary UUID is required for each summary',
            'data.*.uuid.uuid' => 'Summary UUID must be a valid UUID',
            'data.*.shift_id.required' => 'Shift ID is required for each summary',
            'data.*.shift_id.integer' => 'Shift ID must be an integer',
            'data.*.fuel_grade_id.required' => 'Fuel grade ID is required for each summary',
            'data.*.fuel_grade_id.integer' => 'Fuel grade ID must be an integer',
            'data.*.total_volume.required' => 'Total volume is required for each summary',
            'data.*.total_volume.numeric' => 'Total volume must be a number',
            'data.*.total_volume.min' => 'Total volume must be greater than or equal to 0',
            'data.*.total_amount.required' => 'Total amount is required for each summary',
            'data.*.total_amount.numeric' => 'Total amount must be a number',
            'data.*.total_amount.min' => 'Total amount must be greater than or equal to 0',
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
            'data' => 'product wise summary data',
            'data.*.id' => 'summary ID',
            'data.*.uuid' => 'summary UUID',
            'data.*.shift_id' => 'shift ID',
            'data.*.fuel_grade_id' => 'fuel grade ID',
            'data.*.total_volume' => 'total volume',
            'data.*.total_amount' => 'total amount',
        ];
    }
}
