<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncFuelGradePriceHistoryRequest extends FormRequest
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
            'data.*.fuel_grade_id' => 'required|integer',
            'data.*.old_price' => 'required|numeric|min:0',
            'data.*.new_price' => 'required|numeric|min:0',
            'data.*.change_type' => 'nullable|string|max:255',
            'data.*.effective_at' => 'nullable|date',
            'data.*.notes' => 'nullable|string',
            'data.*.changed_by' => 'nullable|integer',
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
            'data.required' => 'Fuel grade price history data is required',
            'data.array' => 'Fuel grade price history data must be an array',
            'data.min' => 'At least one fuel grade price history record must be provided',
            'data.*.id.required' => 'Price history ID is required for each record',
            'data.*.id.integer' => 'Price history ID must be an integer',
            'data.*.uuid.required' => 'Price history UUID is required for each record',
            'data.*.uuid.uuid' => 'Price history UUID must be a valid UUID',
            'data.*.fuel_grade_id.required' => 'Fuel grade ID is required for each record',
            'data.*.fuel_grade_id.integer' => 'Fuel grade ID must be an integer',
            'data.*.old_price.required' => 'Old price is required for each record',
            'data.*.old_price.numeric' => 'Old price must be a number',
            'data.*.old_price.min' => 'Old price must be greater than or equal to 0',
            'data.*.new_price.required' => 'New price is required for each record',
            'data.*.new_price.numeric' => 'New price must be a number',
            'data.*.new_price.min' => 'New price must be greater than or equal to 0',
            'data.*.change_type.string' => 'Change type must be a string',
            'data.*.effective_at.date' => 'Effective at must be a valid date',
            'data.*.notes.string' => 'Notes must be a string',
            'data.*.changed_by.integer' => 'Changed by must be an integer',
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
            'data' => 'fuel grade price history data',
            'data.*.id' => 'price history ID',
            'data.*.uuid' => 'price history UUID',
            'data.*.fuel_grade_id' => 'fuel grade ID',
            'data.*.old_price' => 'old price',
            'data.*.new_price' => 'new price',
            'data.*.change_type' => 'change type',
            'data.*.effective_at' => 'effective at',
            'data.*.notes' => 'notes',
            'data.*.changed_by' => 'changed by',
        ];
    }
}
