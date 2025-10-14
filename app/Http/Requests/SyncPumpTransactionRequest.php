<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncPumpTransactionRequest extends FormRequest
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
            'data.*.id' => 'required|string|max:255',
            'data.*.uuid' => 'required|uuid',
            'data.*.pts2_device_id' => 'required|string|max:255',
            'data.*.pts_id' => 'nullable|string|max:255',
            'data.*.request_id' => 'nullable|string|max:255',
            'data.*.date_time_start' => 'required|date',
            'data.*.date_time_end' => 'nullable|date',
            'data.*.date_time_paid' => 'nullable|date',
            'data.*.pump_id' => 'required|string|max:255',
            'data.*.nozzle_id' => 'required|string|max:255',
            'data.*.fuel_grade_id' => 'required|string|max:255',
            'data.*.tank_id' => 'required|string|max:255',
            'data.*.volume' => 'required|numeric|min:0',
            'data.*.amount' => 'required|numeric|min:0',
            'data.*.unit_price' => 'required|numeric|min:0',
            'data.*.transaction_type' => 'nullable|string|max:255',
            'data.*.payment_method' => 'nullable|string|max:255',
            'data.*.customer_id' => 'nullable|string|max:255',
            'data.*.vehicle_id' => 'nullable|string|max:255',
            'data.*.notes' => 'nullable|string',
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
            'data.required' => 'Transaction data is required',
            'data.array' => 'Transaction data must be an array',
            'data.min' => 'At least one transaction must be provided',
            'data.*.id.required' => 'Transaction ID is required for each transaction',
            'data.*.uuid.required' => 'Transaction UUID is required for each transaction',
            'data.*.uuid.uuid' => 'Transaction UUID must be a valid UUID',
            'data.*.pts2_device_id.required' => 'PTS2 Device ID is required for each transaction',
            'data.*.pts_id.required' => 'PTS ID is required for each transaction',
            'data.*.date_time_start.required' => 'Transaction start time is required for each transaction',
            'data.*.date_time_start.date' => 'Transaction start time must be a valid date',
            'data.*.date_time_end.date' => 'Transaction end time must be a valid date',
            'data.*.date_time_paid.date' => 'Transaction paid time must be a valid date',
            'data.*.pump_id.required' => 'Pump ID is required for each transaction',
            'data.*.nozzle_id.required' => 'Nozzle ID is required for each transaction',
            'data.*.fuel_grade_id.required' => 'Fuel grade ID is required for each transaction',
            'data.*.tank_id.required' => 'Tank ID is required for each transaction',
            'data.*.volume.required' => 'Volume is required for each transaction',
            'data.*.volume.numeric' => 'Volume must be a number',
            'data.*.volume.min' => 'Volume must be greater than or equal to 0',
            'data.*.amount.required' => 'Amount is required for each transaction',
            'data.*.amount.numeric' => 'Amount must be a number',
            'data.*.amount.min' => 'Amount must be greater than or equal to 0',
            'data.*.unit_price.required' => 'Unit price is required for each transaction',
            'data.*.unit_price.numeric' => 'Unit price must be a number',
            'data.*.unit_price.min' => 'Unit price must be greater than or equal to 0',
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
            'data' => 'transaction data',
            'data.*.id' => 'transaction ID',
            'data.*.uuid' => 'transaction UUID',
            'data.*.pts2_device_id' => 'PTS2 device ID',
            'data.*.pts_id' => 'PTS ID',
            'data.*.request_id' => 'request ID',
            'data.*.date_time_start' => 'transaction start time',
            'data.*.date_time_end' => 'transaction end time',
            'data.*.date_time_paid' => 'transaction paid time',
            'data.*.pump_id' => 'pump ID',
            'data.*.nozzle_id' => 'nozzle ID',
            'data.*.fuel_grade_id' => 'fuel grade ID',
            'data.*.tank_id' => 'tank ID',
            'data.*.volume' => 'volume',
            'data.*.amount' => 'amount',
            'data.*.unit_price' => 'unit price',
            'data.*.transaction_type' => 'transaction type',
            'data.*.payment_method' => 'payment method',
            'data.*.customer_id' => 'customer ID',
            'data.*.vehicle_id' => 'vehicle ID',
            'data.*.notes' => 'notes',
        ];
    }
}
