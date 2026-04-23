<?php

namespace App\Http\Requests\Advertising;

use Illuminate\Foundation\Http\FormRequest;

class ProvisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Endpoint público, validado por provision_secret
    }

    public function rules(): array
    {
        return [
            'device_id'        => ['required', 'string', 'max:64'],
            'unit_id'          => ['required', 'string', 'max:32', 'exists:adv_tablets,unit_id'],
            'provision_secret' => ['required', 'string', 'size:64'],
        ];
    }
}
