<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Organization::class);
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255'],
            'avatar'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'description'   => ['nullable', 'string', 'max:2000'],
            'inn'           => ['required', 'string', 'max:12'],
            'ogrn'          => ['nullable', 'string', 'max:15'],
            'legal_address' => ['nullable', 'string', 'max:500'],
            'website'       => ['nullable', 'url', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
        ];
    }
}
