<?php

declare(strict_types=1);

namespace App\Http\Requests\Support;

use App\Models\SupportCategory;
use App\Services\SupportAttachmentService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Validator;

class StoreSupportTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:support_categories,id'],
            'subject'     => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:20000'],
            'files'       => ['nullable', 'array', 'max:' . SupportAttachmentService::MAX_FILES],
            'files.*'     => self::fileRules(),
        ];
    }

    /**
     * Upload rules shared by the ticket form and the comment form. `bail`
     * stops at the first failure, so a bad file gets one message, not two.
     *
     * @return array<int, string>
     */
    public static function fileRules(): array
    {
        return [
            'bail',
            'file',
            'max:' . SupportAttachmentService::MAX_FILE_KB,
            'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,txt,zip',
            'mimetypes:image/jpeg,image/png,image/gif,image/webp,application/pdf,application/msword,'
                . 'application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,'
                . 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/plain,application/zip,'
                . 'application/x-zip-compressed',
        ];
    }

    /**
     * Upload messages shared by the ticket and comment forms. Each one names
     * the file (see fileAttributes()), so with several files it's clear which.
     *
     * @return array<string, string>
     */
    public static function fileMessages(): array
    {
        $notUploaded = 'Файл :attribute не загрузился — возможно, он больше '
            . SupportAttachmentService::formatBytes(SupportAttachmentService::maxFileBytes())
            . '. Прикрепите его ещё раз.';
        $badType = 'Файл :attribute: такой формат прикрепить нельзя. Подходят '
            . SupportAttachmentService::EXTENSIONS_TEXT . '.';

        return [
            'files.array'       => 'Не удалось прочитать вложения — прикрепите файлы ещё раз.',
            'files.max'         => 'Можно прикрепить не более ' . SupportAttachmentService::MAX_FILES . ' файлов.',
            'files.*.uploaded'  => $notUploaded,
            'files.*.file'      => $notUploaded,
            'files.*.max'       => 'Файл :attribute больше 10 МБ — выберите файл поменьше.',
            'files.*.mimes'     => $badType,
            'files.*.mimetypes' => $badType,
        ];
    }

    /**
     * "files.0" → «virus.exe», so per-file messages name the file.
     *
     * @return array<string, string>
     */
    public static function fileAttributes(FormRequest $request): array
    {
        $names = [];

        foreach ((array) $request->file('files', []) as $index => $file) {
            if ($file instanceof UploadedFile) {
                $names["files.{$index}"] = '«' . mb_strimwidth($file->getClientOriginalName(), 0, 80, '…') . '»';
            }
        }

        return $names;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // Only active top-level categories may be chosen by a user.
            $category = SupportCategory::find($this->input('category_id'));

            if ($category && (! $category->is_active || $category->isSubcategory())) {
                $validator->errors()->add('category_id', 'Выберите категорию из списка.');
            }

            $total = collect($this->file('files') ?? [])->sum(fn ($f) => $f->getSize());

            if ($total > SupportAttachmentService::MAX_TOTAL_KB * 1024) {
                $validator->errors()->add('files', 'Суммарный размер файлов не должен превышать 20 МБ.');
            }
        });
    }

    public function attributes(): array
    {
        return self::fileAttributes($this);
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Выберите категорию обращения.',
            'category_id.integer'  => 'Выберите категорию из списка.',
            'category_id.exists'   => 'Выберите категорию из списка.',
            'subject.required'     => 'Укажите тему заявки.',
            'subject.string'       => 'Укажите тему заявки.',
            'subject.max'          => 'Тема не должна превышать 150 символов.',
            'description.required' => 'Опишите ваш вопрос.',
            'description.string'   => 'Опишите ваш вопрос.',
            'description.max'      => 'Описание не должно превышать 20 000 символов.',
        ] + self::fileMessages();
    }
}
