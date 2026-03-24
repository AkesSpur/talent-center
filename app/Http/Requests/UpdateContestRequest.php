<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContestRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Policy check (including status.canEdit()) done in controller.
        return true;
    }

    public function rules(): array
    {
        return [
            'platform_category_id'      => ['nullable', 'integer', 'exists:platform_categories,id'],
            'title'                     => ['required', 'string', 'max:255'],
            'description'               => ['required', 'string', 'max:20000'],
            'rules'                     => ['nullable', 'string', 'max:20000'],
            'regulations_url'           => ['nullable', 'url', 'max:2048'],
            'is_permanent'              => ['nullable', 'boolean'],
            'applications_start_at'     => ['required', 'date'],
            'applications_end_at'       => ['nullable', 'required_unless:is_permanent,1', 'date', 'after_or_equal:applications_start_at'],
            'evaluation_end_at'         => ['nullable', 'required_unless:is_permanent,1', 'date', 'after_or_equal:applications_end_at'],
            'diploma_background'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'delete_diploma_background' => ['nullable', 'boolean'],
            'cover_image'               => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'delete_cover_image'        => ['nullable', 'boolean'],
            'categories'                          => ['nullable', 'array'],
            'categories.*.name'                   => ['required_with:categories', 'string', 'max:255'],
            'categories.*.description'            => ['nullable', 'string', 'max:500'],
            'categories.*.age_groups'             => ['nullable', 'array'],
            'categories.*.age_groups.*.name'      => ['required', 'string', 'max:255'],
            'categories.*.age_groups.*.min_age'   => ['nullable', 'integer', 'min:0', 'max:150'],
            'categories.*.age_groups.*.max_age'   => ['nullable', 'integer', 'min:0', 'max:150'],
            'contest_age_groups'                  => ['nullable', 'array'],
            'contest_age_groups.*.name'           => ['required', 'string', 'max:255'],
            'contest_age_groups.*.min_age'        => ['nullable', 'integer', 'min:0', 'max:150'],
            'contest_age_groups.*.max_age'        => ['nullable', 'integer', 'min:0', 'max:150'],
            'juries'                              => ['nullable', 'array'],
            'juries.*'                            => ['integer', 'exists:users,id'],
            'selected_diploma_background_path'    => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'platform_category_id.exists'     => 'Выбранный жанр не найден.',
            'title.required'                  => 'Укажите название конкурса.',
            'title.max'                        => 'Название не должно превышать 255 символов.',
            'description.required'             => 'Добавьте описание конкурса.',
            'description.max'                  => 'Описание не должно превышать 20000 символов.',
            'applications_start_at.required'   => 'Укажите дату начала приёма заявок.',
            'applications_start_at.date'       => 'Некорректный формат даты начала приёма заявок.',
            'applications_end_at.required_unless' => 'Укажите дату окончания приёма заявок.',
            'applications_end_at.date'            => 'Некорректный формат даты окончания приёма заявок.',
            'applications_end_at.after_or_equal'  => 'Дата окончания приёма заявок не может быть раньше даты начала.',
            'evaluation_end_at.required_unless'   => 'Укажите дату рассылки результатов.',
            'evaluation_end_at.date'              => 'Некорректный формат даты рассылки результатов.',
            'evaluation_end_at.after_or_equal'    => 'Дата рассылки результатов не может быть раньше даты окончания приёма заявок.',
            'diploma_background.image'         => 'Фон диплома должен быть изображением.',
            'diploma_background.mimes'         => 'Допустимые форматы фона: JPG, PNG, WebP.',
            'diploma_background.max'           => 'Файл фона диплома не должен превышать 2 МБ.',
            'cover_image.image'                => 'Обложка должна быть изображением.',
            'cover_image.mimes'                => 'Допустимые форматы обложки: JPG, PNG, WebP.',
            'cover_image.max'                  => 'Файл обложки не должен превышать 2 МБ.',
            'regulations_url.url'               => 'Укажите корректную ссылку на положение.',
            'regulations_url.max'               => 'Ссылка не должна превышать 2048 символов.',
            'categories.*.name.required_with'  => 'Укажите название категории.',
            'categories.*.name.max'            => 'Название категории не должно превышать 255 символов.',
            'juries.*.exists'                  => 'Один из выбранных членов жюри не найден.',
        ];
    }
}
