<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ApplicationStatus;
use App\Enums\ContestStatus;
use App\Enums\FileType;
use App\Models\Application;
use App\Models\Contest;
use App\Models\ContestCategory;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds evaluation-stage contests with applications from 3 real email accounts.
 * Purpose: end-to-end testing of Stage 4 (evaluation, PDF diplomas, email notifications).
 *
 * Run: php artisan db:seed --class=Stage4TestSeeder
 *
 * Credentials: akesspur@gmail.com / akidexly@gmail.com / crystallord2005@gmail.com — all use "password"
 */
class Stage4TestSeeder extends Seeder
{
    /** YouTube / cloud links used as demo application submissions */
    private const LINKS = [
        'https://youtu.be/dQw4w9WgXcQ',
        'https://youtu.be/9bZkp7q19f0',
        'https://youtu.be/jNQXAC9IVRw',
        'https://youtu.be/L_jWHffIx5E',
        'https://disk.yandex.ru/d/stage4-test-demo-1',
        'https://disk.yandex.ru/d/stage4-test-demo-2',
        'https://drive.google.com/file/d/stage4-test-demo-3/view',
        'https://drive.google.com/file/d/stage4-test-demo-4/view',
    ];

    private const BACKGROUNDS = [
        'contests/backgrounds/Create_background_image_for_diploma_73cc7b6542.jpeg',
        'contests/backgrounds/Create_the_background_image_for_a_dimplom_i_want_t_delpmaspu.png',
        'contests/backgrounds/Generate_more_like_this_1418e547c3.jpeg',
        'contests/backgrounds/Generate_more_like_this_7aad449f8d.jpeg',
    ];

    private int $linkIdx = 0;

    public function run(): void
    {
        $past   = fn (int $days): Carbon => Carbon::now()->subDays($days)->startOfDay();
        $future = fn (int $days): Carbon => Carbon::now()->addDays($days)->endOfDay();

        // ── Step 1: Create the 3 real-email parent users ──────────────────
        $realParents = $this->createRealParents();

        // ── Step 2: Create children for each real parent ─────────────────
        $childrenByParent = $this->createChildren($realParents);

        // ── Step 3: Resolve system users ──────────────────────────────────
        $admin       = User::where('email', 'admin@talentcenter.ru')->first();
        $support     = User::where('email', 'support@talentcenter.ru')->first();
        $participant = User::where('email', 'participant@example.com')->first();

        if (! $admin || ! $support) {
            $this->command->error('Admin/support users not found — run AdminSeeder + TestDataSeeder first.');
            return;
        }

        // ── Step 4: Create organisations (one per role) ───────────────────
        $adminOrg   = $this->ensureOrg($admin, [
            'inn'           => '9901110001',
            'name'          => 'Центр искусств «Администратор-Тест»',
            'contact_email' => 'admin-test@talentcenter.ru',
            'legal_address' => 'г. Москва, ул. Тестовая, д. 1',
        ]);

        $supportOrg = $this->ensureOrg($support, [
            'inn'           => '9902220002',
            'name'          => 'Организация «Поддержка-Тест»',
            'contact_email' => 'support-test@talentcenter.ru',
            'legal_address' => 'г. Москва, ул. Тестовая, д. 2',
        ]);

        // Reuse existing participant org if present, else create a fallback
        $participantOrg = Organization::where('inn', '7701234561')->first()
            ?? ($participant ? $this->ensureOrg($participant, [
                'inn'           => '9903330003',
                'name'          => 'Академия «Участник-Тест»',
                'contact_email' => 'participant-test@talentcenter.ru',
                'legal_address' => 'г. Москва, ул. Тестовая, д. 3',
            ]) : $adminOrg);

        // ── Step 5: Create evaluation-stage contests ───────────────────────
        $contestDefs = [
            [
                'org'    => $adminOrg,
                'owner'  => $admin,
                'title'  => '[ТЕСТ] Конкурс юных музыкантов «Мелодия» (оценка)',
                'desc'   => 'Тестовый конкурс для проверки системы оценки, генерации PDF-дипломов и email-уведомлений. Конкурс принадлежит администратору платформы.',
                'rules'  => 'Участники прикрепляют ссылку на видеозапись выступления (YouTube, Яндекс.Диск и т.д.) длительностью от 3 до 10 минут. Не более одной заявки в каждой категории.',
                'bg'     => self::BACKGROUNDS[0],
                'start'  => $past(40),
                'end'    => $past(6),
                'eval'   => $future(30),
                'cats'   => [
                    ['name' => 'Фортепиано', 'description' => 'Сольное исполнение на фортепиано'],
                    ['name' => 'Скрипка',    'description' => 'Сольное исполнение на скрипке'],
                    ['name' => 'Вокал',      'description' => 'Академический и эстрадный вокал'],
                ],
            ],
            [
                'org'    => $supportOrg,
                'owner'  => $support,
                'title'  => '[ТЕСТ] Конкурс юных художников «Краски мира» (оценка)',
                'desc'   => 'Тестовый конкурс по изобразительному искусству. Принадлежит сотруднику поддержки платформы.',
                'rules'  => 'Участники прикрепляют ссылку на фотографию работы (Google Фото, Яндекс.Диск и т.д.). Размер оригинала — не менее A4.',
                'bg'     => self::BACKGROUNDS[1],
                'start'  => $past(35),
                'end'    => $past(4),
                'eval'   => $future(25),
                'cats'   => [
                    ['name' => 'Живопись',     'description' => 'Акварель, масло, гуашь, акрил'],
                    ['name' => 'Графика',      'description' => 'Карандаш, тушь, пастель, уголь'],
                    ['name' => 'Прикладное',   'description' => 'Лепка, аппликация, оригами'],
                ],
            ],
            [
                'org'    => $participantOrg,
                'owner'  => $participant ?? $admin,
                'title'  => '[ТЕСТ] Конкурс исследовательских работ «Открытие» (оценка)',
                'desc'   => 'Тестовый конкурс исследовательских работ для полной проверки системы. Принадлежит тестовому участнику.',
                'rules'  => 'Участники прикрепляют ссылку на видеопрезентацию или текст работы в облачном хранилище.',
                'bg'     => self::BACKGROUNDS[2],
                'start'  => $past(45),
                'end'    => $past(8),
                'eval'   => $future(20),
                'cats'   => [
                    ['name' => 'Науки о природе',  'description' => 'Биология, химия, физика, экология'],
                    ['name' => 'Техника и IT',      'description' => 'Математика, информатика, робототехника'],
                ],
            ],
        ];

        $contests = [];
        foreach ($contestDefs as $def) {
            $contests[] = $this->ensureContest($def);
        }

        // ── Step 6: Create applications ────────────────────────────────────
        // Each real parent applies themselves to every category in every contest.
        // Each child applies to the first category of every contest.

        $appCount = 0;

        foreach ($contests as [$contest, $categories]) {
            /** @var Contest $contest */
            /** @var ContestCategory[] $categories */

            foreach ($realParents as $parent) {
                // Parent applies to every category
                foreach ($categories as $category) {
                    if ($this->applyIfAbsent($contest, $category, $parent)) {
                        $appCount++;
                    }
                }

                // Children apply to the first category
                foreach ($childrenByParent[$parent->email] ?? [] as $child) {
                    if ($this->applyIfAbsent($contest, $categories[0], $child)) {
                        $appCount++;
                    }
                }
            }
        }

        // ── Summary ────────────────────────────────────────────────────────
        $childTotal = array_sum(array_map('count', $childrenByParent));

        $this->command->info('Stage 4 test data seeded successfully.');
        $this->command->newLine();
        $this->command->table(
            ['Item', 'Count'],
            [
                ['Real-email parents', count($realParents)],
                ['Children', $childTotal],
                ['Evaluation contests', count($contests)],
                ['Applications created', $appCount],
            ]
        );
        $this->command->newLine();
        $this->command->line('Test accounts (password: "password"):');
        $this->command->line('  akesspur@gmail.com        (3 children)');
        $this->command->line('  akidexly@gmail.com        (2 children)');
        $this->command->line('  crystallord2005@gmail.com (2 children)');
        $this->command->newLine();
        $this->command->line('Evaluation contests:');
        foreach ($contests as [$contest]) {
            $this->command->line("  [{$contest->id}] {$contest->title}");
        }
    }

    // ── Private helpers ────────────────────────────────────────────────────

    /** @return array<string, User> keyed by email */
    private function createRealParents(): array
    {
        $defs = [
            [
                'first_name' => 'Акесс',
                'last_name'  => 'Пур',
                'patronymic' => 'Тестович',
                'email'      => 'akesspur@gmail.com',
                'phone'      => '+79001110001',
            ],
            [
                'first_name' => 'Аки',
                'last_name'  => 'Декс',
                'patronymic' => null,
                'email'      => 'akidexly@gmail.com',
                'phone'      => '+79002220002',
            ],
            [
                'first_name' => 'Кристал',
                'last_name'  => 'Лорд',
                'patronymic' => null,
                'email'      => 'crystallord2005@gmail.com',
                'phone'      => '+79003330003',
            ],
        ];

        $parents = [];
        foreach ($defs as $d) {
            $parents[$d['email']] = User::firstOrCreate(
                ['email' => $d['email']],
                array_merge($d, [
                    'password'            => Hash::make('password'),
                    'role'                => 'participant',
                    'email_verified_at'   => now(),
                    'email_notifications' => true,
                ])
            );

            // Ensure email_notifications is enabled on existing accounts
            $parents[$d['email']]->update(['email_notifications' => true]);
        }

        return $parents;
    }

    /**
     * @param  array<string, User> $parents
     * @return array<string, User[]> keyed by parent email
     */
    private function createChildren(array $parents): array
    {
        $defs = [
            'akesspur@gmail.com' => [
                ['first_name' => 'Алибек',  'last_name' => 'Пур',  'birth_date' => '2013-05-10'],
                ['first_name' => 'Айгерим', 'last_name' => 'Пур',  'birth_date' => '2015-09-22'],
                ['first_name' => 'Дастан',  'last_name' => 'Пур',  'birth_date' => '2011-02-14'],
            ],
            'akidexly@gmail.com' => [
                ['first_name' => 'Лёня',    'last_name' => 'Декс', 'birth_date' => '2012-07-03'],
                ['first_name' => 'Вика',    'last_name' => 'Декс', 'birth_date' => '2016-11-18'],
            ],
            'crystallord2005@gmail.com' => [
                ['first_name' => 'Кирилл',  'last_name' => 'Лорд', 'birth_date' => '2014-03-25'],
                ['first_name' => 'Полина',  'last_name' => 'Лорд', 'birth_date' => '2012-12-09'],
            ],
        ];

        $result = [];

        foreach ($defs as $parentEmail => $childDefs) {
            $parent = $parents[$parentEmail];
            $result[$parentEmail] = [];

            foreach ($childDefs as $idx => $def) {
                $childEmail = 'stage4.child.' . $parent->id . '.' . ($idx + 1) . '@talentcenter.local';
                $result[$parentEmail][] = User::firstOrCreate(
                    ['email' => $childEmail],
                    array_merge($def, [
                        'email'            => $childEmail,
                        'password'         => Hash::make(str()->random(32)),
                        'role'             => 'participant',
                        'parent_id'        => $parent->id,
                        'email_verified_at' => now(),
                    ])
                );
            }
        }

        return $result;
    }

    private function ensureOrg(User $creator, array $data): Organization
    {
        $org = Organization::firstOrCreate(
            ['inn' => $data['inn']],
            [
                'name'          => $data['name'],
                'contact_email' => $data['contact_email'],
                'legal_address' => $data['legal_address'] ?? null,
                'status'        => 'verified',
                'created_by'    => $creator->id,
                'verified_by'   => $creator->id,
                'verified_at'   => now(),
            ]
        );

        $org->representatives()->syncWithoutDetaching([
            $creator->id => [
                'can_create'   => true,
                'can_manage'   => true,
                'can_evaluate' => true,
            ],
        ]);

        return $org;
    }

    /**
     * @return array{Contest, ContestCategory[]}
     */
    private function ensureContest(array $def): array
    {
        /** @var Organization $org */
        $org   = $def['org'];
        $owner = $def['owner'];

        $contest = Contest::firstOrCreate(
            ['organization_id' => $org->id, 'title' => $def['title']],
            [
                'created_by'            => $owner->id,
                'description'           => $def['desc'],
                'rules'                 => $def['rules'] ?? null,
                'status'                => ContestStatus::Evaluation,
                'applications_start_at' => $def['start'],
                'applications_end_at'   => $def['end'],
                'evaluation_end_at'     => $def['eval'],
                'diploma_background'    => $def['bg'] ?? null,
            ]
        );

        // Load or create categories
        if ($contest->wasRecentlyCreated) {
            $categories = [];
            foreach ($def['cats'] as $cat) {
                $categories[] = ContestCategory::create([
                    'contest_id'  => $contest->id,
                    'name'        => $cat['name'],
                    'description' => $cat['description'] ?? null,
                ]);
            }
        } else {
            $categories = $contest->categories()->get()->all();
            // If categories were wiped, re-seed them
            if (empty($categories)) {
                foreach ($def['cats'] as $cat) {
                    $categories[] = ContestCategory::create([
                        'contest_id'  => $contest->id,
                        'name'        => $cat['name'],
                        'description' => $cat['description'] ?? null,
                    ]);
                }
            }
        }

        return [$contest, $categories];
    }

    private function applyIfAbsent(Contest $contest, ContestCategory $category, User $user): bool
    {
        $exists = Application::where('contest_id', $contest->id)
            ->where('user_id', $user->id)
            ->where('category_id', $category->id)
            ->exists();

        if ($exists) {
            return false;
        }

        Application::create([
            'contest_id'    => $contest->id,
            'category_id'   => $category->id,
            'user_id'       => $user->id,
            'status'        => ApplicationStatus::New,
            'external_link' => self::LINKS[$this->linkIdx % count(self::LINKS)],
            'file_type'     => FileType::Link,
        ]);

        $this->linkIdx++;
        return true;
    }
}
