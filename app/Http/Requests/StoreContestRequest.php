<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContestRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Policy check done in controller; org-level middleware guards route.
        return true;
    }

    public function rules(): array
    {
        return [
            'organization_id'          => ['required', 'integer', 'exists:organizations,id'],
            'platform_category_id'     => ['nullable', 'integer', 'exists:platform_categories,id'],
            'title'                    => ['required', 'string', 'max:255'],
            'description'              => ['required', 'string', 'max:5000'],
            'rules'                    => ['nullable', 'string', 'max:5000'],
            'applications_start_at'    => ['required', 'date'],
            'applications_end_at'      => ['required', 'date', 'after:applications_start_at'],
            'evaluation_end_at'        => ['required', 'date', 'after:applications_end_at'],
            'diploma_background'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'cover_image'              => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'categories'               => ['nullable', 'array'],
            'categories.*.name'        => ['required_with:categories', 'string', 'max:255'],
            'categories.*.description' => ['nullable', 'string', 'max:500'],
            'juries'                   => ['nullable', 'array'],
            'juries.*'                 => ['integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'organization_id.required'        => 'Выберите организацию.',
            'organization_id.exists'          => 'Выбранная организация не найдена.',
            'platform_category_id.exists'     => 'Выбранная категория платформы не найдена.',
            'title.required'                  => 'Укажите название конкурса.',
            'title.max'                        => 'Название не должно превышать 255 символов.',
            'description.required'             => 'Добавьте описание конкурса.',
            'description.max'                  => 'Описание не должно превышать 5000 символов.',
            'applications_start_at.required'   => 'Укажите дату начала приёма заявок.',
            'applications_start_at.date'       => 'Некорректный формат даты начала приёма заявок.',
            'applications_end_at.required'     => 'Укажите дату окончания приёма заявок.',
            'applications_end_at.date'         => 'Некорректный формат даты окончания приёма заявок.',
            'applications_end_at.after'        => 'Дата окончания приёма заявок должна быть позже даты начала.',
            'evaluation_end_at.required'       => 'Укажите дату публикации результатов.',
            'evaluation_end_at.date'           => 'Некорректный формат даты публикации результатов.',
            'evaluation_end_at.after'          => 'Дата публикации результатов должна быть позже даты окончания приёма заявок.',
            'diploma_background.image'         => 'Фон диплома должен быть изображением.',
            'diploma_background.mimes'         => 'Допустимые форматы фона: JPG, PNG, WebP.',
            'diploma_background.max'           => 'Файл фона диплома не должен превышать 2 МБ.',
            'cover_image.image'                => 'Обложка должна быть изображением.',
            'cover_image.mimes'                => 'Допустимые форматы обложки: JPG, PNG, WebP.',
            'cover_image.max'                  => 'Файл обложки не должен превышать 2 МБ.',
            'categories.*.name.required_with'  => 'Укажите название категории.',
            'categories.*.name.max'            => 'Название категории не должно превышать 255 символов.',
            'juries.*.exists'                  => 'Один из выбранных членов жюри не найден.',
        ];
    }
}
