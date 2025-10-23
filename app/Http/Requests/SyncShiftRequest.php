<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncShiftRequest extends FormRequest
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
            'data.*.start_time' => 'required|date',
            'data.*.end_time' => 'nullable|date',
            'data.*.user_id' => 'required|integer',
            'data.*.notes' => 'nullable|string|max:1000',
            'data.*.close_type' => 'required|string|in:manual,auto',
            'data.*.status' => 'required|string|in:started,completed',
            'data.*.auto_close_time' => 'nullable|date',
            'data.*.start_time_utc' => 'nullable|date',
            'data.*.end_time_utc' => 'nullable|date',
            'data.*.auto_close_time_utc' => 'nullable|date',
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
            'data.required' => 'Shift data is required',
            'data.array' => 'Shift data must be an array',
            'data.min' => 'At least one shift must be provided',
            'data.*.id.required' => 'Shift ID is required for each shift',
            'data.*.id.integer' => 'Shift ID must be an integer',
            'data.*.start_time.required' => 'Start time is required for each shift',
            'data.*.start_time.date' => 'Start time must be a valid date',
            'data.*.end_time.date' => 'End time must be a valid date',
            'data.*.user_id.required' => 'User ID is required for each shift',
            'data.*.user_id.integer' => 'User ID must be an integer',
            'data.*.notes.string' => 'Notes must be a string',
            'data.*.notes.max' => 'Notes must not exceed 1000 characters',
            'data.*.close_type.required' => 'Close type is required for each shift',
            'data.*.close_type.string' => 'Close type must be a string',
            'data.*.close_type.in' => 'Close type must be either manual or auto',
            'data.*.status.required' => 'Status is required for each shift',
            'data.*.status.string' => 'Status must be a string',
            'data.*.status.in' => 'Status must be either started or completed',
            'data.*.auto_close_time.date' => 'Auto close time must be a valid date',
            'data.*.start_time_utc.date' => 'Start time UTC must be a valid date',
            'data.*.end_time_utc.date' => 'End time UTC must be a valid date',
            'data.*.auto_close_time_utc.date' => 'Auto close time UTC must be a valid date',
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
            'data' => 'shift data',
            'data.*.id' => 'shift ID',
            'data.*.start_time' => 'start time',
            'data.*.end_time' => 'end time',
            'data.*.user_id' => 'user ID',
            'data.*.notes' => 'notes',
            'data.*.close_type' => 'close type',
            'data.*.status' => 'status',
            'data.*.auto_close_time' => 'auto close time',
            'data.*.start_time_utc' => 'start time UTC',
            'data.*.end_time_utc' => 'end time UTC',
            'data.*.auto_close_time_utc' => 'auto close time UTC',
        ];
    }
}
