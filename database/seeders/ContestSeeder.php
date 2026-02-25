<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ContestStatus;
use App\Models\Contest;
use App\Models\ContestCategory;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ContestSeeder extends Seeder
{
    public function run(): void
    {
        // Date helpers relative to today so the seeder stays meaningful on re-runs
        $past   = fn (int $days): Carbon => Carbon::now()->subDays($days)->startOfDay();
        $future = fn (int $days): Carbon => Carbon::now()->addDays($days)->endOfDay();

        // ── Resolve orgs by INN (must match TestDataSeeder) ─────────────
        $orgs = Organization::whereIn('inn', [
            '7701234561', // Академия «Вдохновение»
            '7812345672', // Школа «Юный талант»
            '5403456783', // Центр развития детского творчества
            '1655678904', // Музыкальная школа Чайковского
            '6671789015', // Академия «Пируэт»
            '2308901226', // Центр «Звезда»
            '5258012337', // Образовательный центр «Олимп»
            '6317123448', // Театральный центр «Маски»
        ])->get()->keyBy('inn');

        if ($orgs->isEmpty()) {
            $this->command->warn('No organizations found — run TestDataSeeder first.');
            return;
        }

        // ── Contest definitions ──────────────────────────────────────────
        //
        //  status is derived from dates; for cancelled it is forced explicitly.
        //
        $contests = [

            // ── ACCEPTING ────────────────────────────────────────────────

            [
                'org_inn'    => '7701234561',
                'creator'    => 'participant@example.com',
                'title'      => 'Всероссийский конкурс юных музыкантов «Золотые струны»',
                'description' => 'Ежегодный всероссийский конкурс для юных музыкантов в возрасте от 6 до 18 лет. Конкурс проводится с целью выявления и поддержки музыкально одарённых детей и молодёжи.',
                'rules'      => "Участники должны исполнить:\n— обязательное произведение из предложенного списка;\n— произведение по собственному выбору.\nЗапись должна быть не старше 3 месяцев. Допускается до двух участников от одного педагога в каждой категории.",
                'start'      => $past(20),
                'end'        => $future(30),
                'eval_end'   => $future(60),
                'status'     => ContestStatus::Accepting,
                'categories' => [
                    ['name' => 'Фортепиано',       'description' => 'Сольное исполнение на фортепиано'],
                    ['name' => 'Скрипка',           'description' => 'Сольное исполнение на скрипке'],
                    ['name' => 'Духовые инструменты','description' => 'Флейта, кларнет, гобой, саксофон и др.'],
                    ['name' => 'Народные инструменты','description' => 'Балалайка, домра, баян, аккордеон и др.'],
                ],
            ],

            [
                'org_inn'    => '6671789015',
                'creator'    => 'lebedev.a@example.com',
                'title'      => 'Открытый конкурс хореографии «Ритм и грация»',
                'description' => 'Открытый конкурс для хореографических коллективов и солистов. Принимаются работы по всем направлениям танцевального искусства.',
                'rules'      => "Продолжительность номера — от 2 до 7 минут. Видеозапись должна быть сделана в полный рост, с чётким изображением. Фонограмма должна быть высокого качества. К участию допускаются коллективы и солисты от 4 лет.",
                'start'      => $past(15),
                'end'        => $future(35),
                'eval_end'   => $future(65),
                'status'     => ContestStatus::Accepting,
                'categories' => [
                    ['name' => 'Классический танец', 'description' => 'Классический и неоклассический балет'],
                    ['name' => 'Народный танец',     'description' => 'Русский народный и другие народные стили'],
                    ['name' => 'Современный танец',  'description' => 'Contemporary, джаз-модерн, эстрадный'],
                    ['name' => 'Спортивные танцы',   'description' => 'Бальные и латиноамериканские программы'],
                ],
            ],

            [
                'org_inn'    => '6317123448',
                'creator'    => 'volkova.t@example.com',
                'title'      => 'Конкурс театрального мастерства «Маска»',
                'description' => 'Всероссийский конкурс для любительских детских и юношеских театральных коллективов. Цель — развитие театральной культуры и выявление талантливых юных актёров.',
                'rules'      => "Продолжительность постановки — от 5 до 20 минут. Заявка включает видеозапись спектакля или сцены, краткое описание постановки и состав труппы. Участники: от 6 до 18 лет.",
                'start'      => $past(10),
                'end'        => $future(25),
                'eval_end'   => $future(55),
                'status'     => ContestStatus::Accepting,
                'categories' => [
                    ['name' => 'Драматический театр', 'description' => 'Спектакли драматического жанра'],
                    ['name' => 'Музыкальный театр',   'description' => 'Мюзикл, опера, оперетта'],
                    ['name' => 'Кукольный театр',     'description' => 'Все виды кукольных постановок'],
                ],
            ],

            [
                'org_inn'    => '5258012337',
                'creator'    => 'popov.i@example.com',
                'title'      => 'Конкурс юных исследователей «Мир науки»',
                'description' => 'Конкурс исследовательских и проектных работ обучающихся в возрасте от 7 до 18 лет. Поддерживаем интерес детей к науке и инженерному делу.',
                'rules'      => "Участник представляет исследовательскую или проектную работу в электронном виде (PDF или DOCX, до 4 МБ) либо ссылку на видеопрезентацию. Работы проходят двухэтапную проверку жюри.",
                'start'      => $past(18),
                'end'        => $future(28),
                'eval_end'   => $future(50),
                'status'     => ContestStatus::Accepting,
                'categories' => [
                    ['name' => 'Естественные науки', 'description' => 'Биология, химия, физика, экология'],
                    ['name' => 'Технические науки',  'description' => 'Математика, информатика, робототехника'],
                    ['name' => 'Гуманитарные науки', 'description' => 'История, литература, обществознание'],
                    ['name' => 'Творческие проекты', 'description' => 'Декоративно-прикладное и другое творчество'],
                ],
            ],

            // ── PENDING ──────────────────────────────────────────────────

            [
                'org_inn'    => '7812345672',
                'creator'    => 'sokolova.n@example.com',
                'title'      => 'VIII Всероссийский конкурс изобразительного искусства «Палитра»',
                'description' => 'Восьмой ежегодный конкурс детского и юношеского изобразительного искусства. Приглашаются участники от 5 до 18 лет.',
                'rules'      => "Работа должна быть авторской и выполненной не ранее текущего учебного года. Принимаются фотографии работ (JPG, PNG) с разрешением не менее 1500×1500 пикселей. Тема конкурса: «Родная природа».",
                'start'      => $future(20),
                'end'        => $future(65),
                'eval_end'   => $future(95),
                'status'     => ContestStatus::Pending,
                'categories' => [
                    ['name' => 'Живопись',              'description' => 'Масло, акварель, гуашь, акрил'],
                    ['name' => 'Графика',               'description' => 'Карандаш, тушь, пастель, уголь'],
                    ['name' => 'Декоративно-прикладное','description' => 'Лепка, вышивка, аппликация и др.'],
                    ['name' => 'Цифровое искусство',    'description' => 'Иллюстрация и дизайн, выполненные в цифровых программах'],
                ],
            ],

            [
                'org_inn'    => '2308901226',
                'creator'    => 'morozova.s@example.com',
                'title'      => 'Конкурс вокального искусства «Звонкий голос»',
                'description' => 'Региональный конкурс эстрадного и академического вокала для детей от 5 до 18 лет. Проводится с целью развития вокальных и сценических навыков.',
                'rules'      => "Участник представляет видеозапись выступления длительностью от 1 до 5 минут. Допускается использование минусовой фонограммы. Запись должна быть сделана в хорошем качестве звука и изображения.",
                'start'      => $future(6),
                'end'        => $future(50),
                'eval_end'   => $future(80),
                'status'     => ContestStatus::Pending,
                'categories' => [
                    ['name' => 'Эстрадный вокал',   'description' => 'Поп, рок, джаз, авторская песня'],
                    ['name' => 'Академический вокал','description' => 'Классические произведения, народные песни'],
                    ['name' => 'Ансамблевое пение',  'description' => 'Дуэт, трио или малая группа (до 5 человек)'],
                ],
            ],

            // ── EVALUATION ───────────────────────────────────────────────

            [
                'org_inn'    => '1655678904',
                'creator'    => 'zaharova.o@example.com',
                'title'      => 'Конкурс юных пианистов «Осенняя соната»',
                'description' => 'Конкурс для юных пианистов, обучающихся в детских музыкальных школах и школах искусств. Оценивается техника, музыкальность и художественная выразительность исполнения.',
                'rules'      => "Программа включает два произведения: полифонию (И.С. Бах или его современники) и произведение по выбору участника. Запись продолжительностью от 5 до 15 минут.",
                'start'      => $past(50),
                'end'        => $past(8),
                'eval_end'   => $future(18),
                'status'     => ContestStatus::Evaluation,
                'categories' => [
                    ['name' => 'Младшая группа (6–10 лет)', 'description' => 'Учащиеся 1–4 класса ДМШ'],
                    ['name' => 'Средняя группа (11–14 лет)', 'description' => 'Учащиеся 5–7 класса ДМШ'],
                    ['name' => 'Старшая группа (15–18 лет)', 'description' => 'Учащиеся 8 класса и выпускники ДМШ'],
                ],
            ],

            [
                'org_inn'    => '5403456783',
                'creator'    => 'krylov.v@example.com',
                'title'      => 'Межрегиональный конкурс детского рисунка «Мир глазами детей»',
                'description' => 'Конкурс объединяет детей из разных регионов России через изобразительное искусство. Тема этого года: «Моя семья».',
                'rules'      => "Принимаются рисунки, выполненные любой техникой. Работа должна быть авторской, размером не менее A4. К участию допускаются дети от 5 до 14 лет. Прикрепите фотографию работы (JPG/PNG).",
                'start'      => $past(45),
                'end'        => $past(5),
                'eval_end'   => $future(22),
                'status'     => ContestStatus::Evaluation,
                'categories' => [
                    ['name' => 'Дошкольники (5–6 лет)',    'description' => null],
                    ['name' => 'Младший школьный (7–10 лет)', 'description' => null],
                    ['name' => 'Средний школьный (11–14 лет)', 'description' => null],
                ],
            ],

            // ── ARCHIVE ──────────────────────────────────────────────────

            [
                'org_inn'    => '5258012337',
                'creator'    => 'popov.i@example.com',
                'title'      => 'IV Конкурс юных талантов «Звёздный час»',
                'description' => 'Традиционный ежегодный конкурс, объединяющий лучших молодых исполнителей в области музыки, хореографии и вокала. Конкурс завершён.',
                'rules'      => null,
                'start'      => $past(150),
                'end'        => $past(90),
                'eval_end'   => $past(60),
                'status'     => ContestStatus::Archive,
                'categories' => [],
            ],

            [
                'org_inn'    => '7812345672',
                'creator'    => 'sokolova.n@example.com',
                'title'      => 'Всероссийский конкурс детского творчества «Радуга талантов»',
                'description' => 'Завершённый всероссийский конкурс детского творчества. Принимались работы по 4 направлениям: живопись, музыка, хореография, театр.',
                'rules'      => null,
                'start'      => $past(180),
                'end'        => $past(120),
                'eval_end'   => $past(90),
                'status'     => ContestStatus::Archive,
                'categories' => [
                    ['name' => 'Живопись',    'description' => null],
                    ['name' => 'Музыка',      'description' => null],
                    ['name' => 'Хореография', 'description' => null],
                    ['name' => 'Театр',       'description' => null],
                ],
            ],

            [
                'org_inn'    => '7701234561',
                'creator'    => 'participant@example.com',
                'title'      => 'III Межрегиональный конкурс юных исполнителей «Классика»',
                'description' => 'Завершённый конкурс академического музыкального исполнительства. Результаты объявлены, дипломы вручены.',
                'rules'      => null,
                'start'      => $past(200),
                'end'        => $past(140),
                'eval_end'   => $past(100),
                'status'     => ContestStatus::Archive,
                'categories' => [
                    ['name' => 'Фортепиано', 'description' => null],
                    ['name' => 'Струнные',   'description' => null],
                    ['name' => 'Духовые',    'description' => null],
                ],
            ],

            // ── CANCELLED ────────────────────────────────────────────────

            [
                'org_inn'    => '2308901226',
                'creator'    => 'morozova.s@example.com',
                'title'      => 'Конкурс эстрадного вокала «Южная звезда» (отменён)',
                'description' => 'Конкурс был отменён по организационным причинам. Приносим извинения участникам.',
                'rules'      => null,
                'start'      => $past(15),
                'end'        => $future(30),
                'eval_end'   => $future(60),
                'status'     => ContestStatus::Cancelled,  // forced — override date-derived status
                'categories' => [],
            ],

        ];

        // ── Seed ────────────────────────────────────────────────────────

        $created = 0;
        $skipped = 0;

        foreach ($contests as $def) {
            $org = $orgs->get($def['org_inn']);
            if (! $org) {
                $this->command->warn("Org INN {$def['org_inn']} not found — skipping \"{$def['title']}\"");
                continue;
            }

            $creator = User::where('email', $def['creator'])->first();
            if (! $creator) {
                $this->command->warn("Creator {$def['creator']} not found — skipping \"{$def['title']}\"");
                continue;
            }

            [$contest, $wasCreated] = $this->firstOrCreateContest($org->id, $creator->id, $def);

            if ($wasCreated) {
                $this->seedCategories($contest, $def['categories']);
                $created++;
            } else {
                $skipped++;
            }
        }

        $this->command->info("Contests: {$created} created, {$skipped} already existed");
        $this->command->info('Total contests in DB: ' . Contest::count());
    }

    /**
     * @return array{Contest, bool}  [contest, wasCreated]
     */
    private function firstOrCreateContest(int $orgId, int $creatorId, array $def): array
    {
        $existing = Contest::where('organization_id', $orgId)
            ->where('title', $def['title'])
            ->first();

        if ($existing) {
            return [$existing, false];
        }

        $contest = Contest::create([
            'organization_id'      => $orgId,
            'created_by'           => $creatorId,
            'title'                => $def['title'],
            'description'          => $def['description'],
            'rules'                => $def['rules'],
            'status'               => $def['status'],
            'applications_start_at' => $def['start'],
            'applications_end_at'  => $def['end'],
            'evaluation_end_at'    => $def['eval_end'],
        ]);

        return [$contest, true];
    }

    private function seedCategories(Contest $contest, array $categories): void
    {
        foreach ($categories as $cat) {
            ContestCategory::create([
                'contest_id'  => $contest->id,
                'name'        => $cat['name'],
                'description' => $cat['description'] ?? null,
            ]);
        }
    }
}
