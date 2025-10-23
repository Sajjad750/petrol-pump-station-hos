<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncTankDeliveryRequest extends FormRequest
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
            'data.*.pts_delivery_id' => 'nullable|string|max:255',
            'data.*.tank' => 'required|integer',
            'data.*.fuel_grade_id' => 'nullable|integer',
            'data.*.fuel_grade_name' => 'nullable|string|max:20',
            'data.*.configuration_id' => 'nullable|string|max:8',
            'data.*.start_datetime' => 'nullable|date',
            'data.*.start_product_height' => 'nullable|numeric|min:0',
            'data.*.start_water_height' => 'nullable|numeric|min:0',
            'data.*.start_temperature' => 'nullable|numeric',
            'data.*.start_product_volume' => 'nullable|numeric|min:0',
            'data.*.start_product_tc_volume' => 'nullable|numeric|min:0',
            'data.*.start_product_density' => 'nullable|numeric|min:0',
            'data.*.start_product_mass' => 'nullable|numeric|min:0',
            'data.*.end_datetime' => 'nullable|date',
            'data.*.end_product_height' => 'nullable|numeric|min:0',
            'data.*.end_water_height' => 'nullable|numeric|min:0',
            'data.*.end_temperature' => 'nullable|numeric',
            'data.*.end_product_volume' => 'nullable|numeric|min:0',
            'data.*.end_product_tc_volume' => 'nullable|numeric|min:0',
            'data.*.end_product_density' => 'nullable|numeric|min:0',
            'data.*.end_product_mass' => 'nullable|numeric|min:0',
            'data.*.received_product_volume' => 'nullable|numeric|min:0',
            'data.*.absolute_product_height' => 'nullable|numeric|min:0',
            'data.*.absolute_water_height' => 'nullable|numeric|min:0',
            'data.*.absolute_temperature' => 'nullable|numeric',
            'data.*.absolute_product_volume' => 'nullable|numeric|min:0',
            'data.*.absolute_product_tc_volume' => 'nullable|numeric|min:0',
            'data.*.absolute_product_density' => 'nullable|numeric|min:0',
            'data.*.absolute_product_mass' => 'nullable|numeric|min:0',
            'data.*.pumps_dispensed_volume' => 'nullable|numeric|min:0',
            'data.*.probe_data' => 'nullable|array',
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
            'data.required' => 'Tank delivery data is required',
            'data.array' => 'Tank delivery data must be an array',
            'data.min' => 'At least one tank delivery must be provided',
            'data.*.id.required' => 'Tank delivery ID is required for each delivery',
            'data.*.id.integer' => 'Tank delivery ID must be an integer',
            'data.*.uuid.required' => 'Tank delivery UUID is required for each delivery',
            'data.*.uuid.uuid' => 'Tank delivery UUID must be a valid UUID',
            'data.*.request_id.integer' => 'Request ID must be an integer',
            'data.*.pts_id.string' => 'PTS ID must be a string',
            'data.*.pts_delivery_id.string' => 'PTS delivery ID must be a string',
            'data.*.tank.required' => 'Tank number is required for each delivery',
            'data.*.tank.integer' => 'Tank number must be an integer',
            'data.*.fuel_grade_id.integer' => 'Fuel grade ID must be an integer',
            'data.*.fuel_grade_name.string' => 'Fuel grade name must be a string',
            'data.*.configuration_id.string' => 'Configuration ID must be a string',
            'data.*.start_datetime.date' => 'Start datetime must be a valid date',
            'data.*.start_product_height.numeric' => 'Start product height must be a number',
            'data.*.start_product_height.min' => 'Start product height must be greater than or equal to 0',
            'data.*.start_water_height.numeric' => 'Start water height must be a number',
            'data.*.start_water_height.min' => 'Start water height must be greater than or equal to 0',
            'data.*.start_temperature.numeric' => 'Start temperature must be a number',
            'data.*.start_product_volume.numeric' => 'Start product volume must be a number',
            'data.*.start_product_volume.min' => 'Start product volume must be greater than or equal to 0',
            'data.*.start_product_tc_volume.numeric' => 'Start product TC volume must be a number',
            'data.*.start_product_tc_volume.min' => 'Start product TC volume must be greater than or equal to 0',
            'data.*.start_product_density.numeric' => 'Start product density must be a number',
            'data.*.start_product_density.min' => 'Start product density must be greater than or equal to 0',
            'data.*.start_product_mass.numeric' => 'Start product mass must be a number',
            'data.*.start_product_mass.min' => 'Start product mass must be greater than or equal to 0',
            'data.*.end_datetime.date' => 'End datetime must be a valid date',
            'data.*.end_product_height.numeric' => 'End product height must be a number',
            'data.*.end_product_height.min' => 'End product height must be greater than or equal to 0',
            'data.*.end_water_height.numeric' => 'End water height must be a number',
            'data.*.end_water_height.min' => 'End water height must be greater than or equal to 0',
            'data.*.end_temperature.numeric' => 'End temperature must be a number',
            'data.*.end_product_volume.numeric' => 'End product volume must be a number',
            'data.*.end_product_volume.min' => 'End product volume must be greater than or equal to 0',
            'data.*.end_product_tc_volume.numeric' => 'End product TC volume must be a number',
            'data.*.end_product_tc_volume.min' => 'End product TC volume must be greater than or equal to 0',
            'data.*.end_product_density.numeric' => 'End product density must be a number',
            'data.*.end_product_density.min' => 'End product density must be greater than or equal to 0',
            'data.*.end_product_mass.numeric' => 'End product mass must be a number',
            'data.*.end_product_mass.min' => 'End product mass must be greater than or equal to 0',
            'data.*.received_product_volume.numeric' => 'Received product volume must be a number',
            'data.*.received_product_volume.min' => 'Received product volume must be greater than or equal to 0',
            'data.*.absolute_product_height.numeric' => 'Absolute product height must be a number',
            'data.*.absolute_product_height.min' => 'Absolute product height must be greater than or equal to 0',
            'data.*.absolute_water_height.numeric' => 'Absolute water height must be a number',
            'data.*.absolute_water_height.min' => 'Absolute water height must be greater than or equal to 0',
            'data.*.absolute_temperature.numeric' => 'Absolute temperature must be a number',
            'data.*.absolute_product_volume.numeric' => 'Absolute product volume must be a number',
            'data.*.absolute_product_volume.min' => 'Absolute product volume must be greater than or equal to 0',
            'data.*.absolute_product_tc_volume.numeric' => 'Absolute product TC volume must be a number',
            'data.*.absolute_product_tc_volume.min' => 'Absolute product TC volume must be greater than or equal to 0',
            'data.*.absolute_product_density.numeric' => 'Absolute product density must be a number',
            'data.*.absolute_product_density.min' => 'Absolute product density must be greater than or equal to 0',
            'data.*.absolute_product_mass.numeric' => 'Absolute product mass must be a number',
            'data.*.absolute_product_mass.min' => 'Absolute product mass must be greater than or equal to 0',
            'data.*.pumps_dispensed_volume.numeric' => 'Pumps dispensed volume must be a number',
            'data.*.pumps_dispensed_volume.min' => 'Pumps dispensed volume must be greater than or equal to 0',
            'data.*.probe_data.array' => 'Probe data must be an array',
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
            'data' => 'tank delivery data',
            'data.*.id' => 'tank delivery ID',
            'data.*.uuid' => 'tank delivery UUID',
            'data.*.request_id' => 'request ID',
            'data.*.pts_id' => 'PTS ID',
            'data.*.pts_delivery_id' => 'PTS delivery ID',
            'data.*.tank' => 'tank number',
            'data.*.fuel_grade_id' => 'fuel grade ID',
            'data.*.fuel_grade_name' => 'fuel grade name',
            'data.*.configuration_id' => 'configuration ID',
            'data.*.start_datetime' => 'start datetime',
            'data.*.start_product_height' => 'start product height',
            'data.*.start_water_height' => 'start water height',
            'data.*.start_temperature' => 'start temperature',
            'data.*.start_product_volume' => 'start product volume',
            'data.*.start_product_tc_volume' => 'start product TC volume',
            'data.*.start_product_density' => 'start product density',
            'data.*.start_product_mass' => 'start product mass',
            'data.*.end_datetime' => 'end datetime',
            'data.*.end_product_height' => 'end product height',
            'data.*.end_water_height' => 'end water height',
            'data.*.end_temperature' => 'end temperature',
            'data.*.end_product_volume' => 'end product volume',
            'data.*.end_product_tc_volume' => 'end product TC volume',
            'data.*.end_product_density' => 'end product density',
            'data.*.end_product_mass' => 'end product mass',
            'data.*.received_product_volume' => 'received product volume',
            'data.*.absolute_product_height' => 'absolute product height',
            'data.*.absolute_water_height' => 'absolute water height',
            'data.*.absolute_temperature' => 'absolute temperature',
            'data.*.absolute_product_volume' => 'absolute product volume',
            'data.*.absolute_product_tc_volume' => 'absolute product TC volume',
            'data.*.absolute_product_density' => 'absolute product density',
            'data.*.absolute_product_mass' => 'absolute product mass',
            'data.*.pumps_dispensed_volume' => 'pumps dispensed volume',
            'data.*.probe_data' => 'probe data',
        ];
    }
}
