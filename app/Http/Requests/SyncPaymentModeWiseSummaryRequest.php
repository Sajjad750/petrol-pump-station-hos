<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncPaymentModeWiseSummaryRequest extends FormRequest
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
            'data.*.mop' => 'required|string|max:50',
            'data.*.volume' => 'required|numeric|min:0',
            'data.*.amount' => 'required|numeric|min:0',
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
            'data.required' => 'Payment mode wise summary data is required',
            'data.array' => 'Payment mode wise summary data must be an array',
            'data.min' => 'At least one payment mode wise summary must be provided',
            'data.*.id.required' => 'Summary ID is required for each summary',
            'data.*.id.integer' => 'Summary ID must be an integer',
            'data.*.uuid.required' => 'Summary UUID is required for each summary',
            'data.*.uuid.uuid' => 'Summary UUID must be a valid UUID',
            'data.*.shift_id.required' => 'Shift ID is required for each summary',
            'data.*.shift_id.integer' => 'Shift ID must be an integer',
            'data.*.mop.required' => 'Method of payment is required for each summary',
            'data.*.mop.string' => 'Method of payment must be a string',
            'data.*.mop.max' => 'Method of payment must not exceed 50 characters',
            'data.*.volume.required' => 'Volume is required for each summary',
            'data.*.volume.numeric' => 'Volume must be a number',
            'data.*.volume.min' => 'Volume must be greater than or equal to 0',
            'data.*.amount.required' => 'Amount is required for each summary',
            'data.*.amount.numeric' => 'Amount must be a number',
            'data.*.amount.min' => 'Amount must be greater than or equal to 0',
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
            'data' => 'payment mode wise summary data',
            'data.*.id' => 'summary ID',
            'data.*.uuid' => 'summary UUID',
            'data.*.shift_id' => 'shift ID',
            'data.*.mop' => 'method of payment',
            'data.*.volume' => 'volume',
            'data.*.amount' => 'amount',
        ];
    }
}
