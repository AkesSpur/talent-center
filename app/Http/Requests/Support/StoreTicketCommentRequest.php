<?php

declare(strict_types=1);

namespace App\Http\Requests\Support;

use App\Services\SupportAttachmentService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreTicketCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'content'     => ['required', 'string', 'max:20000'],
            'is_internal' => ['nullable', 'boolean'],
            'files'       => ['nullable', 'array', 'max:' . SupportAttachmentService::MAX_FILES],
            'files.*'     => StoreSupportTicketRequest::fileRules(),
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $total = collect($this->file('files') ?? [])->sum(fn ($f) => $f->getSize());

            if ($total > SupportAttachmentService::MAX_TOTAL_KB * 1024) {
                $validator->errors()->add('files', 'Суммарный размер файлов не должен превышать 20 МБ.');
            }
        });
    }

    public function attributes(): array
    {
        return StoreSupportTicketRequest::fileAttributes($this);
    }

    public function messages(): array
    {
        return [
            'content.required'    => 'Введите текст сообщения.',
            'content.string'      => 'Введите текст сообщения.',
            'content.max'         => 'Сообщение не должно превышать 20 000 символов.',
            'is_internal.boolean' => 'Не удалось определить тип сообщения — обновите страницу.',
        ] + StoreSupportTicketRequest::fileMessages();
    }
}
