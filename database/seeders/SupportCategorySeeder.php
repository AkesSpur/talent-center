<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SupportCategory;
use Illuminate\Database\Seeder;

/**
 * Preset categories and subcategories from the TZ, section 4.1.
 * Idempotent: re-running updates the preset rows instead of duplicating them.
 */
class SupportCategorySeeder extends Seeder
{
    public function run(): void
    {
        $structure = [
            'Оплата и возврат средств' => [
                'description' => 'Вопросы по транзакциям, чекам, возвратам и статусам платежей',
                'children' => [
                    'Возврат средств',
                    'Статус оплаты',
                    'Ошибка оплаты',
                    'Запрос чека/документа',
                    'Перерасчёт стоимости',
                ],
            ],
            'Изменение данных в заявке' => [
                'description' => 'Корректировки персональных данных, состава участников, файлов и названий работ',
                'children' => [
                    'Смена ФИО участника',
                    'Смена контактных данных',
                    'Корректировка состава участников',
                    'Замена прикреплённых файлов',
                    'Смена названия конкурсной работы',
                ],
            ],
            'Сроки и статусы конкурса' => [
                'description' => 'Вопросы по календарю этапов, дедлайнам и текущему статусу заявки',
                'children' => [
                    'Сроки приёма работ',
                    'Сроки подведения итогов',
                    'Статус моей заявки',
                    'Перенос сроков проведения',
                    'Отмена/приостановка конкурса',
                ],
            ],
            'Функционал платформы и технические проблемы' => [
                'description' => 'Ошибки интерфейса, проблемы с загрузкой файлов, авторизацией и отображением данных',
                'children' => [
                    'Ошибка/баг интерфейса',
                    'Не загружается файл',
                    'Не работает кнопка/форма',
                    'Непонятный интерфейс (запрос инструкции)',
                    'Проблема с входом/авторизацией',
                ],
            ],
            'Организационные вопросы' => [
                'description' => 'Общие вопросы по правилам, документам, аккредитации и партнёрству',
                'children' => [
                    'Правила участия и требования',
                    'Документы и справки',
                    'Аккредитация/статус площадки',
                    'Партнёрство и сотрудничество',
                    'Другое (прочее)',
                ],
            ],
        ];

        $order = 0;

        foreach ($structure as $name => $data) {
            $order += 10;

            $parent = SupportCategory::updateOrCreate(
                ['name' => $name, 'parent_id' => null],
                ['description' => $data['description'], 'sort_order' => $order, 'is_active' => true],
            );

            $childOrder = 0;

            foreach ($data['children'] as $childName) {
                $childOrder += 10;

                SupportCategory::updateOrCreate(
                    ['name' => $childName, 'parent_id' => $parent->id],
                    ['sort_order' => $childOrder, 'is_active' => true],
                );
            }
        }
    }
}
