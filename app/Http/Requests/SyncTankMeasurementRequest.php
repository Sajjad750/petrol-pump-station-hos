<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncTankMeasurementRequest extends FormRequest
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
            'data.*.request_id' => 'nullable|integer',
            'data.*.pts_id' => 'nullable|string|max:255',
            'data.*.date_time' => 'required|date',
            'data.*.tank' => 'required|integer',
            'data.*.fuel_grade_id' => 'nullable|integer',
            'data.*.fuel_grade_name' => 'nullable|string|max:255',
            'data.*.status' => 'nullable|string|max:255',
            'data.*.alarms' => 'nullable|string',
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
            'data.required' => 'Tank measurement data is required',
            'data.array' => 'Tank measurement data must be an array',
            'data.min' => 'At least one tank measurement must be provided',
            'data.*.id.required' => 'Tank measurement ID is required for each measurement',
            'data.*.id.integer' => 'Tank measurement ID must be an integer',
            'data.*.uuid.required' => 'Tank measurement UUID is required for each measurement',
            'data.*.uuid.uuid' => 'Tank measurement UUID must be a valid UUID',
            'data.*.request_id.integer' => 'Request ID must be an integer',
            'data.*.pts_id.string' => 'PTS ID must be a string',
            'data.*.date_time.required' => 'Date time is required for each measurement',
            'data.*.date_time.date' => 'Date time must be a valid date',
            'data.*.tank.required' => 'Tank number is required for each measurement',
            'data.*.tank.integer' => 'Tank number must be an integer',
            'data.*.fuel_grade_id.integer' => 'Fuel grade ID must be an integer',
            'data.*.fuel_grade_name.string' => 'Fuel grade name must be a string',
            'data.*.status.string' => 'Status must be a string',
            'data.*.alarms.string' => 'Alarms must be a string',
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
            'data.*.tank_filling_percentage.max' => 'Tank filling percentage must be less than or equal to 100',
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
            'data' => 'tank measurement data',
            'data.*.id' => 'tank measurement ID',
            'data.*.uuid' => 'tank measurement UUID',
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
