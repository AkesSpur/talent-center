<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user->isParticipant() || $user->isAdmin() || $user->isSupport();
    }

    public function rules(): array
    {
        return [
            'contest_id'            => ['required', 'integer', 'exists:contests,id'],
            'category_id'           => ['nullable', 'integer', 'exists:contest_categories,id'],
            'submitted_for_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'file'                  => ['nullable', 'file', 'max:4096', 'mimes:jpg,jpeg,png,gif,pdf,doc,docx'],
            'external_link'         => ['nullable', 'url', 'max:500'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // Require at least one of: file upload or external link
            if (! $this->hasFile('file') && ! $this->filled('external_link')) {
                $validator->errors()->add('file', 'Прикрепите файл или укажите ссылку на работу.');
            }

            // submitted_for_user_id must be the user themselves or one of their children
            if ($this->filled('submitted_for_user_id')) {
                $userId  = (int) $this->input('submitted_for_user_id');
                $authId  = $this->user()->id;
                $childIds = $this->user()->children()->pluck('id')->toArray();

                if ($userId !== $authId && ! in_array($userId, $childIds, true)) {
                    $validator->errors()->add('submitted_for_user_id', 'Недопустимый участник.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'contest_id.required'   => 'Конкурс не указан.',
            'contest_id.exists'     => 'Конкурс не найден.',
            'category_id.exists'    => 'Выбранная категория не найдена.',
            'file.max'              => 'Размер файла не должен превышать 4 МБ.',
            'file.mimes'            => 'Допустимые форматы: JPG, PNG, GIF, PDF, DOC, DOCX.',
            'external_link.url'     => 'Укажите корректную ссылку (начинающуюся с https://).',
            'external_link.max'     => 'Ссылка слишком длинная.',
        ];
    }
}
