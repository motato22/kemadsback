<?php

namespace App\Http\Requests\Advertising;

use Illuminate\Foundation\Http\FormRequest;

class HeartbeatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // La autorización se maneja en el middleware Sanctum
    }

    public function rules(): array
    {
        return [
            'device_id'       => ['required', 'string', 'max:64'],
            'app_version'     => ['required', 'string', 'max:16'],
            'battery_level'   => ['nullable', 'integer', 'min:0', 'max:100'],
            'driver_shift_id' => ['nullable', 'integer', 'exists:adv_driver_shifts,id'],
            'lat'             => ['nullable', 'numeric', 'between:-90,90'],
            'lng'             => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }

    public function messages(): array
    {
        return [
            'device_id.required'   => 'El device_id es requerido.',
            'app_version.required' => 'La versión de la app es requerida.',
            'battery_level.min'    => 'El nivel de batería no puede ser negativo.',
            'battery_level.max'    => 'El nivel de batería no puede superar 100.',
        ];
    }
}
