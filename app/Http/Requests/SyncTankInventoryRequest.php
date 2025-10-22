<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncTankInventoryRequest extends FormRequest
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
            'data.*.request_id' => 'nullable|string|max:255',
            'data.*.pts_id' => 'required|string|max:255',
            'data.*.date_time' => 'required|date',
            'data.*.tank' => 'required|integer',
            'data.*.fuel_grade_id' => 'nullable|integer',
            'data.*.fuel_grade_name' => 'nullable|string|max:255',
            'data.*.status' => 'nullable|string|max:255',
            'data.*.alarms' => 'nullable|array',
            'data.*.product_height' => 'nullable|numeric|min:0',
            'data.*.water_height' => 'nullable|numeric|min:0',
            'data.*.temperature' => 'nullable|numeric',
            'data.*.product_volume' => 'nullable|numeric|min:0',
            'data.*.water_volume' => 'nullable|numeric|min:0',
            'data.*.product_ullage' => 'nullable|numeric|min:0',
            'data.*.product_tc_volume' => 'nullable|numeric|min:0',
            'data.*.product_density' => 'nullable|numeric|min:0',
            'data.*.product_mass' => 'nullable|numeric|min:0',
            'data.*.tank_filling_percentage' => 'nullable|numeric|min:0|max:100',
            'data.*.configuration_id' => 'nullable|integer',
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
            'data.required' => 'Tank inventory data is required',
            'data.array' => 'Tank inventory data must be an array',
            'data.min' => 'At least one tank inventory must be provided',
            'data.*.id.required' => 'Inventory ID is required for each inventory',
            'data.*.id.integer' => 'Inventory ID must be an integer',
            'data.*.uuid.required' => 'Inventory UUID is required for each inventory',
            'data.*.uuid.uuid' => 'Inventory UUID must be a valid UUID',
            'data.*.request_id.string' => 'Request ID must be a string',
            'data.*.request_id.max' => 'Request ID must not exceed 255 characters',
            'data.*.pts_id.required' => 'PTS ID is required for each inventory',
            'data.*.pts_id.string' => 'PTS ID must be a string',
            'data.*.date_time.required' => 'Date time is required for each inventory',
            'data.*.date_time.date' => 'Date time must be a valid date',
            'data.*.tank.required' => 'Tank number is required for each inventory',
            'data.*.tank.integer' => 'Tank number must be an integer',
            'data.*.fuel_grade_id.integer' => 'Fuel grade ID must be an integer',
            'data.*.fuel_grade_name.string' => 'Fuel grade name must be a string',
            'data.*.fuel_grade_name.max' => 'Fuel grade name must not exceed 255 characters',
            'data.*.status.string' => 'Status must be a string',
            'data.*.status.max' => 'Status must not exceed 255 characters',
            'data.*.alarms.array' => 'Alarms must be an array',
            'data.*.product_height.numeric' => 'Product height must be a number',
            'data.*.product_height.min' => 'Product height must be greater than or equal to 0',
            'data.*.water_height.numeric' => 'Water height must be a number',
            'data.*.water_height.min' => 'Water height must be greater than or equal to 0',
            'data.*.temperature.numeric' => 'Temperature must be a number',
            'data.*.product_volume.numeric' => 'Product volume must be a number',
            'data.*.product_volume.min' => 'Product volume must be greater than or equal to 0',
            'data.*.water_volume.numeric' => 'Water volume must be a number',
            'data.*.water_volume.min' => 'Water volume must be greater than or equal to 0',
            'data.*.product_ullage.numeric' => 'Product ullage must be a number',
            'data.*.product_ullage.min' => 'Product ullage must be greater than or equal to 0',
            'data.*.product_tc_volume.numeric' => 'Product TC volume must be a number',
            'data.*.product_tc_volume.min' => 'Product TC volume must be greater than or equal to 0',
            'data.*.product_density.numeric' => 'Product density must be a number',
            'data.*.product_density.min' => 'Product density must be greater than or equal to 0',
            'data.*.product_mass.numeric' => 'Product mass must be a number',
            'data.*.product_mass.min' => 'Product mass must be greater than or equal to 0',
            'data.*.tank_filling_percentage.numeric' => 'Tank filling percentage must be a number',
            'data.*.tank_filling_percentage.min' => 'Tank filling percentage must be greater than or equal to 0',
            'data.*.tank_filling_percentage.max' => 'Tank filling percentage must not exceed 100',
            'data.*.configuration_id.integer' => 'Configuration ID must be an integer',
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
            'data' => 'tank inventory data',
            'data.*.id' => 'inventory ID',
            'data.*.uuid' => 'inventory UUID',
            'data.*.request_id' => 'request ID',
            'data.*.pts_id' => 'PTS ID',
            'data.*.date_time' => 'date time',
            'data.*.tank' => 'tank number',
            'data.*.fuel_grade_id' => 'fuel grade ID',
            'data.*.fuel_grade_name' => 'fuel grade name',
            'data.*.status' => 'status',
            'data.*.alarms' => 'alarms',
            'data.*.product_height' => 'product height',
            'data.*.water_height' => 'water height',
            'data.*.temperature' => 'temperature',
            'data.*.product_volume' => 'product volume',
            'data.*.water_volume' => 'water volume',
            'data.*.product_ullage' => 'product ullage',
            'data.*.product_tc_volume' => 'product TC volume',
            'data.*.product_density' => 'product density',
            'data.*.product_mass' => 'product mass',
            'data.*.tank_filling_percentage' => 'tank filling percentage',
            'data.*.configuration_id' => 'configuration ID',
        ];
    }
}
