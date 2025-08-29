<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\VehicleType;

class ParkVehicleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Since the requirements state all requests come from trusted sources,
        // we'll allow all requests
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $vehicleTypes = implode(',', VehicleType::values());

        return [
            'vehicle_type' => "required|string|in:{$vehicleTypes}",
            'license_plate' => 'nullable|string|max:20|regex:/^[A-Z0-9\-\s]+$/i',
            'make' => 'nullable|string|max:50',
            'model' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:30',
            'ai_detection_data' => 'nullable|array',
            'ai_detection_data.confidence' => 'nullable|numeric|between:0,1',
            'ai_detection_data.detection_timestamp' => 'nullable|date',
            'ai_detection_data.camera_id' => 'nullable|string|max:50',
            'ai_detection_data.image_url' => 'nullable|url',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'vehicle_type.required' => 'Vehicle type is required.',
            'vehicle_type.in' => 'Vehicle type must be one of: motorcycle, car, van.',
            'license_plate.regex' => 'License plate format is invalid.',
            'ai_detection_data.confidence.between' => 'AI detection confidence must be between 0 and 1.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'vehicle_type' => 'vehicle type',
            'license_plate' => 'license plate',
            'ai_detection_data.confidence' => 'AI detection confidence',
            'ai_detection_data.detection_timestamp' => 'detection timestamp',
            'ai_detection_data.camera_id' => 'camera ID',
            'ai_detection_data.image_url' => 'image URL',
        ];
    }
}
