<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ApplicationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class EvaluateApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::in([
                    ApplicationStatus::Participant->value,
                    ApplicationStatus::Place1->value,
                    ApplicationStatus::Place2->value,
                    ApplicationStatus::Place3->value,
                    ApplicationStatus::Rejected->value,
                ]),
            ],
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $status = $this->input('status');
            if ($status === ApplicationStatus::Rejected->value && ! $this->filled('rejection_reason')) {
                $validator->errors()->add('rejection_reason', 'Укажите причину отклонения заявки.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Выберите результат оценки.',
            'status.in'       => 'Недопустимый статус оценки.',
            'rejection_reason.max' => 'Причина отклонения не должна превышать 1000 символов.',
        ];
    }
}
