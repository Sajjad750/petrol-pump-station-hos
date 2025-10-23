<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncShiftPumpTotalRequest extends FormRequest
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
            'data.*.pump_id' => 'required|integer',
            'data.*.nozzle_id' => 'required|integer',
            'data.*.fuel_grade_id' => 'required|integer',
            'data.*.volume' => 'required|numeric|min:0',
            'data.*.amount' => 'required|numeric|min:0',
            'data.*.transaction_count' => 'required|integer|min:0',
            'data.*.user' => 'nullable|string|max:255',
            'data.*.type' => 'nullable|string|max:255',
            'data.*.recorded_at' => 'nullable|date',
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
            'data.required' => 'Shift pump total data is required',
            'data.array' => 'Shift pump total data must be an array',
            'data.min' => 'At least one shift pump total must be provided',
            'data.*.id.required' => 'Total ID is required for each total',
            'data.*.id.integer' => 'Total ID must be an integer',
            'data.*.uuid.required' => 'Total UUID is required for each total',
            'data.*.uuid.uuid' => 'Total UUID must be a valid UUID',
            'data.*.shift_id.required' => 'Shift ID is required for each total',
            'data.*.shift_id.integer' => 'Shift ID must be an integer',
            'data.*.pump_id.required' => 'Pump ID is required for each total',
            'data.*.pump_id.integer' => 'Pump ID must be an integer',
            'data.*.nozzle_id.required' => 'Nozzle ID is required for each total',
            'data.*.nozzle_id.integer' => 'Nozzle ID must be an integer',
            'data.*.fuel_grade_id.required' => 'Fuel grade ID is required for each total',
            'data.*.fuel_grade_id.integer' => 'Fuel grade ID must be an integer',
            'data.*.volume.required' => 'Volume is required for each total',
            'data.*.volume.numeric' => 'Volume must be a number',
            'data.*.volume.min' => 'Volume must be greater than or equal to 0',
            'data.*.amount.required' => 'Amount is required for each total',
            'data.*.amount.numeric' => 'Amount must be a number',
            'data.*.amount.min' => 'Amount must be greater than or equal to 0',
            'data.*.transaction_count.required' => 'Transaction count is required for each total',
            'data.*.transaction_count.integer' => 'Transaction count must be an integer',
            'data.*.transaction_count.min' => 'Transaction count must be greater than or equal to 0',
            'data.*.user.string' => 'User must be a string',
            'data.*.user.max' => 'User must not exceed 255 characters',
            'data.*.type.string' => 'Type must be a string',
            'data.*.type.max' => 'Type must not exceed 255 characters',
            'data.*.recorded_at.date' => 'Recorded at must be a valid date',
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
            'data' => 'shift pump total data',
            'data.*.id' => 'total ID',
            'data.*.uuid' => 'total UUID',
            'data.*.shift_id' => 'shift ID',
            'data.*.pump_id' => 'pump ID',
            'data.*.nozzle_id' => 'nozzle ID',
            'data.*.fuel_grade_id' => 'fuel grade ID',
            'data.*.volume' => 'volume',
            'data.*.amount' => 'amount',
            'data.*.transaction_count' => 'transaction count',
            'data.*.user' => 'user',
            'data.*.type' => 'type',
            'data.*.recorded_at' => 'recorded at',
        ];
    }
}
