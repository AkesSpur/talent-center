<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        // ── Support accounts ────────────────────────────

        $supportData = [
            ['first_name' => 'Алексей',    'last_name' => 'Смирнов',    'patronymic' => 'Васильевич', 'email' => 'support@talentcenter.ru'],
            ['first_name' => 'Марина',     'last_name' => 'Козлова',    'patronymic' => 'Ивановна',   'email' => 'moderator@talentcenter.ru'],
            ['first_name' => 'Дмитрий',   'last_name' => 'Фёдоров',    'patronymic' => 'Олегович',   'email' => 'helper@talentcenter.ru'],
        ];

        $supportUsers = [];
        foreach ($supportData as $data) {
            $supportUsers[] = User::firstOrCreate(
                ['email' => $data['email']],
                array_merge($data, [
                    'password'           => Hash::make('password'),
                    'role'               => 'support',
                    'email_verified_at'  => now(),
                    'phone'              => '+7900' . rand(1000000, 9999999),
                ])
            );
        }

        $this->command->info('Support accounts: ' . count($supportData) . ' created/found');

        // ── Parent participants (with children) ─────────

        $parentsData = [
            ['first_name' => 'Мария',      'last_name' => 'Иванова',     'patronymic' => 'Петровна',    'email' => 'parent@example.com',       'city' => 'Москва',            'phone' => '+79001234567'],
            ['first_name' => 'Наталья',   'last_name' => 'Соколова',    'patronymic' => 'Андреевна',   'email' => 'sokolova.n@example.com',   'city' => 'Санкт-Петербург',   'phone' => '+79119876543'],
            ['first_name' => 'Виктор',    'last_name' => 'Крылов',      'patronymic' => 'Николаевич',  'email' => 'krylov.v@example.com',     'city' => 'Новосибирск',       'phone' => '+79221112233'],
            ['first_name' => 'Ольга',     'last_name' => 'Захарова',    'patronymic' => 'Сергеевна',   'email' => 'zaharova.o@example.com',   'city' => 'Казань',            'phone' => '+79334445566'],
            ['first_name' => 'Андрей',    'last_name' => 'Лебедев',     'patronymic' => 'Викторович',  'email' => 'lebedev.a@example.com',    'city' => 'Екатеринбург',      'phone' => '+79445556677'],
            ['first_name' => 'Светлана',  'last_name' => 'Морозова',    'patronymic' => 'Николаевна',  'email' => 'morozova.s@example.com',   'city' => 'Краснодар',         'phone' => '+79556667788'],
            ['first_name' => 'Игорь',     'last_name' => 'Попов',       'patronymic' => 'Антонович',   'email' => 'popov.i@example.com',      'city' => 'Нижний Новгород',   'phone' => '+79667778899'],
            ['first_name' => 'Татьяна',   'last_name' => 'Волкова',     'patronymic' => 'Ивановна',    'email' => 'volkova.t@example.com',    'city' => 'Самара',            'phone' => '+79778889900'],
            ['first_name' => 'Сергей',    'last_name' => 'Новиков',     'patronymic' => 'Дмитриевич',  'email' => 'novikov.s@example.com',    'city' => 'Тюмень',            'phone' => '+79889990011'],
            ['first_name' => 'Елена',     'last_name' => 'Яковлева',    'patronymic' => 'Романовна',   'email' => 'yakovleva.e@example.com',  'city' => 'Ростов-на-Дону',    'phone' => '+79990001122'],
        ];

        $childrenMap = [
            'parent@example.com' => [
                ['first_name' => 'Алексей',  'last_name' => 'Иванов',    'birth_date' => '2011-03-12', 'organization' => 'ДШИ №5',       'group' => '4А', 'city' => 'Москва'],
                ['first_name' => 'Елена',    'last_name' => 'Иванова',   'birth_date' => '2014-07-24', 'organization' => 'ДШИ №5',       'group' => '1Б', 'city' => 'Москва'],
            ],
            'sokolova.n@example.com' => [
                ['first_name' => 'Кирилл',   'last_name' => 'Соколов',   'birth_date' => '2010-11-05', 'organization' => 'СДШИ №2',      'group' => '5В', 'city' => 'Санкт-Петербург'],
                ['first_name' => 'Анастасия','last_name' => 'Соколова',  'birth_date' => '2013-02-18', 'organization' => 'СДШИ №2',      'group' => '2А', 'city' => 'Санкт-Петербург'],
                ['first_name' => 'Максим',   'last_name' => 'Соколов',   'birth_date' => '2016-09-30', 'organization' => 'СДШИ №2',      'group' => 'Подготовка', 'city' => 'Санкт-Петербург'],
            ],
            'krylov.v@example.com' => [
                ['first_name' => 'Дарья',    'last_name' => 'Крылова',   'birth_date' => '2012-06-14', 'organization' => 'ДМШ №10',      'group' => '3Б', 'city' => 'Новосибирск'],
                ['first_name' => 'Никита',   'last_name' => 'Крылов',    'birth_date' => '2015-01-22', 'organization' => 'ДМШ №10',      'group' => '1А', 'city' => 'Новосибирск'],
            ],
            'zaharova.o@example.com' => [
                ['first_name' => 'Полина',   'last_name' => 'Захарова',  'birth_date' => '2011-08-09', 'organization' => 'ДХШ №1',       'group' => '4В', 'city' => 'Казань'],
                ['first_name' => 'Иван',     'last_name' => 'Захаров',   'birth_date' => '2013-12-31', 'organization' => 'ДХШ №1',       'group' => '2Б', 'city' => 'Казань'],
            ],
            'lebedev.a@example.com' => [
                ['first_name' => 'Виктория', 'last_name' => 'Лебедева',  'birth_date' => '2010-04-17', 'organization' => 'ДШИ Гармония', 'group' => '5А', 'city' => 'Екатеринбург'],
                ['first_name' => 'Роман',    'last_name' => 'Лебедев',   'birth_date' => '2014-10-03', 'organization' => 'ДШИ Гармония', 'group' => '1В', 'city' => 'Екатеринбург'],
                ['first_name' => 'Алина',    'last_name' => 'Лебедева',  'birth_date' => '2017-05-28', 'organization' => 'ДШИ Гармония', 'group' => 'Дошкольная', 'city' => 'Екатеринбург'],
            ],
            'morozova.s@example.com' => [
                ['first_name' => 'Артём',    'last_name' => 'Морозов',   'birth_date' => '2012-02-07', 'organization' => 'ЦДТ Краснодар','group' => '3А', 'city' => 'Краснодар'],
                ['first_name' => 'Ксения',   'last_name' => 'Морозова',  'birth_date' => '2015-11-19', 'organization' => 'ЦДТ Краснодар','group' => '1Б', 'city' => 'Краснодар'],
            ],
            'popov.i@example.com' => [
                ['first_name' => 'Михаил',   'last_name' => 'Попов',     'birth_date' => '2011-07-25', 'organization' => 'ДМШ им. Глинки','group' => '4Б', 'city' => 'Нижний Новгород'],
                ['first_name' => 'Софья',    'last_name' => 'Попова',    'birth_date' => '2013-03-14', 'organization' => 'ДМШ им. Глинки','group' => '2В', 'city' => 'Нижний Новгород'],
            ],
            'volkova.t@example.com' => [
                ['first_name' => 'Денис',    'last_name' => 'Волков',    'birth_date' => '2010-09-08', 'organization' => 'ДШИ №7',       'group' => '5Б', 'city' => 'Самара'],
                ['first_name' => 'Вероника', 'last_name' => 'Волкова',   'birth_date' => '2014-06-22', 'organization' => 'ДШИ №7',       'group' => '1А', 'city' => 'Самара'],
            ],
            'novikov.s@example.com' => [
                ['first_name' => 'Тимофей',  'last_name' => 'Новиков',   'birth_date' => '2012-01-30', 'organization' => 'ДХШИ Тюмень',  'group' => '3В', 'city' => 'Тюмень'],
                ['first_name' => 'Милена',   'last_name' => 'Новикова',  'birth_date' => '2016-08-11', 'organization' => 'ДХШИ Тюмень',  'group' => 'Подготовка', 'city' => 'Тюмень'],
            ],
            'yakovleva.e@example.com' => [
                ['first_name' => 'Глеб',     'last_name' => 'Яковлев',   'birth_date' => '2011-05-03', 'organization' => 'ДМШ №3',       'group' => '4А', 'city' => 'Ростов-на-Дону'],
                ['first_name' => 'Ульяна',   'last_name' => 'Яковлева',  'birth_date' => '2014-12-16', 'organization' => 'ДМШ №3',       'group' => '1Б', 'city' => 'Ростов-на-Дону'],
            ],
        ];

        $parents = [];
        foreach ($parentsData as $data) {
            $parents[$data['email']] = User::firstOrCreate(
                ['email' => $data['email']],
                array_merge($data, [
                    'password'          => Hash::make('password'),
                    'role'              => 'participant',
                    'email_verified_at' => now(),
                ])
            );
        }

        $this->command->info('Parents: ' . count($parentsData) . ' created/found');

        // Create children
        $childCount = 0;
        foreach ($childrenMap as $parentEmail => $children) {
            $parent = $parents[$parentEmail] ?? null;
            if (! $parent) {
                continue;
            }
            foreach ($children as $idx => $child) {
                $childEmail = 'child.' . $parent->id . '.' . ($idx + 1) . '@talentcenter.local';
                User::firstOrCreate(
                    ['email' => $childEmail],
                    array_merge($child, [
                        'email'             => $childEmail,
                        'password'          => Hash::make(str()->random(32)),
                        'role'              => 'participant',
                        'parent_id'         => $parent->id,
                    ])
                );
                $childCount++;
            }
        }

        $this->command->info("Children: {$childCount} created/found");

        // ── Solo participants (no children) ─────────────

        $soloData = [
            ['first_name' => 'Дмитрий',   'last_name' => 'Петров',      'patronymic' => 'Сергеевич',   'email' => 'participant@example.com',      'city' => 'Москва',          'phone' => '+79001234567'],
            ['first_name' => 'Анна',      'last_name' => 'Семёнова',    'patronymic' => 'Витальевна',  'email' => 'semenova.a@example.com',       'city' => 'Москва',          'phone' => '+79112345678'],
            ['first_name' => 'Николай',   'last_name' => 'Алексеев',    'patronymic' => 'Павлович',    'email' => 'alexeev.n@example.com',        'city' => 'Санкт-Петербург', 'phone' => '+79223456789'],
            ['first_name' => 'Юлия',      'last_name' => 'Виноградова', 'patronymic' => 'Андреевна',   'email' => 'vinogradova.y@example.com',    'city' => 'Казань'],
            ['first_name' => 'Евгений',   'last_name' => 'Степанов',    'patronymic' => 'Михайлович',  'email' => 'stepanov.e@example.com',       'city' => 'Нижний Новгород', 'phone' => '+79334567890'],
            ['first_name' => 'Инна',      'last_name' => 'Белова',      'patronymic' => 'Кирилловна',  'email' => 'belova.i@example.com',         'city' => 'Екатеринбург'],
            ['first_name' => 'Павел',     'last_name' => 'Гаврилов',    'patronymic' => 'Романович',   'email' => 'gavrilov.p@example.com',       'city' => 'Краснодар',       'phone' => '+79445678901'],
            ['first_name' => 'Кристина',  'last_name' => 'Осипова',     'patronymic' => 'Вячеславовна','email' => 'osipova.k@example.com',        'city' => 'Самара'],
            ['first_name' => 'Антон',     'last_name' => 'Кузьмин',     'patronymic' => 'Владимирович','email' => 'kuzmin.a@example.com',         'city' => 'Тюмень',          'phone' => '+79556789012'],
            ['first_name' => 'Людмила',   'last_name' => 'Тихонова',    'patronymic' => 'Борисовна',   'email' => 'tihonova.l@example.com',       'city' => 'Ростов-на-Дону'],
            ['first_name' => 'Роман',     'last_name' => 'Журавлёв',    'patronymic' => 'Константинович','email' => 'zhuravlev.r@example.com',     'city' => 'Москва',          'phone' => '+79667890123'],
            ['first_name' => 'Валентина', 'last_name' => 'Никитина',    'patronymic' => 'Евгеньевна',  'email' => 'nikitina.v@example.com',       'city' => 'Санкт-Петербург'],
            ['first_name' => 'Илья',      'last_name' => 'Рябов',       'patronymic' => 'Артёмович',   'email' => 'ryabov.i@example.com',         'city' => 'Новосибирск',     'phone' => '+79778901234'],
            ['first_name' => 'Марина',    'last_name' => 'Зайцева',     'patronymic' => 'Денисовна',   'email' => 'zaitseva.m@example.com',       'city' => 'Казань'],
            ['first_name' => 'Владимир',  'last_name' => 'Соловьёв',    'patronymic' => 'Игоревич',    'email' => 'solovyev.v@example.com',       'city' => 'Нижний Новгород', 'phone' => '+79889012345'],
            ['first_name' => 'Дарья',     'last_name' => 'Михайлова',   'patronymic' => 'Олеговна',    'email' => 'mihailova.d@example.com',      'city' => 'Екатеринбург'],
            ['first_name' => 'Артём',     'last_name' => 'Беляев',      'patronymic' => 'Сергеевич',   'email' => 'belyaev.a@example.com',        'city' => 'Краснодар',       'phone' => '+79990123456'],
            ['first_name' => 'Наталия',   'last_name' => 'Громова',     'patronymic' => 'Алексеевна',  'email' => 'gromova.n@example.com',        'city' => 'Самара'],
            ['first_name' => 'Максим',    'last_name' => 'Воронов',     'patronymic' => 'Павлович',    'email' => 'voronov.m@example.com',        'city' => 'Тюмень',          'phone' => '+79001357924'],
            ['first_name' => 'Ирина',     'last_name' => 'Фомина',      'patronymic' => 'Александровна','email' => 'fomina.i@example.com',        'city' => 'Ростов-на-Дону'],
        ];

        $soloParticipants = [];
        foreach ($soloData as $data) {
            $soloParticipants[$data['email']] = User::firstOrCreate(
                ['email' => $data['email']],
                array_merge($data, [
                    'password'          => Hash::make('password'),
                    'role'              => 'participant',
                    'email_verified_at' => now(),
                ])
            );
        }

        $this->command->info('Solo participants: ' . count($soloData) . ' created/found');

        // ── Organizations ────────────────────────────────

        $orgsData = [
            [
                'name'          => 'Академия творческих искусств «Вдохновение»',
                'inn'           => '7701234561',
                'ogrn'          => '1037700123451',
                'description'   => 'Ведущая академия в области творческого образования детей и молодёжи. Проводим конкурсы по музыке, живописи и хореографии.',
                'legal_address' => 'г. Москва, ул. Арбат, д. 12',
                'contact_email' => 'info@vdohnovenie-academy.ru',
                'contact_phone' => '+74951234567',
                'website'       => 'https://vdohnovenie-academy.ru',
                'status'        => 'verified',
                'creator_email' => 'participant@example.com',
                'reps'          => [
                    'sokolova.n@example.com' => ['can_create' => false, 'can_manage' => false, 'can_evaluate' => true],
                    'alexeev.n@example.com'  => ['can_create' => true,  'can_manage' => false, 'can_evaluate' => false],
                ],
            ],
            [
                'name'          => 'Школа искусств «Юный талант»',
                'inn'           => '7812345672',
                'ogrn'          => '1037812345672',
                'description'   => 'Школа для одарённых детей в области музыки и изобразительного искусства. Конкурсы регионального и всероссийского уровня.',
                'legal_address' => 'г. Санкт-Петербург, ул. Невская, д. 45',
                'contact_email' => 'info@yuniy-talant.spb.ru',
                'contact_phone' => '+78123456789',
                'website'       => 'https://yuniy-talant.spb.ru',
                'status'        => 'verified',
                'creator_email' => 'sokolova.n@example.com',
                'reps'          => [
                    'semenova.a@example.com' => ['can_create' => true, 'can_manage' => true, 'can_evaluate' => false],
                ],
            ],
            [
                'name'          => 'Центр развития детского творчества',
                'inn'           => '5403456783',
                'ogrn'          => '1035403456783',
                'description'   => 'Многопрофильный центр детского и юношеского творчества Новосибирской области.',
                'legal_address' => 'г. Новосибирск, пр. Красный, д. 67',
                'contact_email' => 'crdt@nsk.ru',
                'contact_phone' => '+73832345678',
                'website'       => null,
                'status'        => 'verified',
                'creator_email' => 'krylov.v@example.com',
                'reps'          => [
                    'ryabov.i@example.com'  => ['can_create' => true,  'can_manage' => false, 'can_evaluate' => true],
                    'belova.i@example.com'  => ['can_create' => false, 'can_manage' => false, 'can_evaluate' => true],
                ],
            ],
            [
                'name'          => 'Музыкальная школа им. П.И. Чайковского',
                'inn'           => '1655678904',
                'ogrn'          => '1031655678904',
                'description'   => 'Старейшая музыкальная школа Республики Татарстан. Специализируемся на классической музыке и конкурсах академического уровня.',
                'legal_address' => 'г. Казань, ул. Баумана, д. 23',
                'contact_email' => 'tchaikovsky@kazan-music.ru',
                'contact_phone' => '+78432567890',
                'website'       => 'https://kazan-music.ru',
                'status'        => 'verified',
                'creator_email' => 'zaharova.o@example.com',
                'reps'          => [
                    'vinogradova.y@example.com' => ['can_create' => true, 'can_manage' => true, 'can_evaluate' => true],
                    'zaitseva.m@example.com'    => ['can_create' => false, 'can_manage' => false, 'can_evaluate' => true],
                ],
            ],
            [
                'name'          => 'Академия танца и хореографии «Пируэт»',
                'inn'           => '6671789015',
                'ogrn'          => '1036671789015',
                'description'   => 'Профессиональная академия хореографического искусства. Проводим конкурсы по всем направлениям танца.',
                'legal_address' => 'г. Екатеринбург, ул. Ленина, д. 89',
                'contact_email' => 'info@piruet-academy.ru',
                'contact_phone' => '+73432789012',
                'website'       => 'https://piruet-academy.ru',
                'status'        => 'verified',
                'creator_email' => 'lebedev.a@example.com',
                'reps'          => [
                    'osipova.k@example.com'  => ['can_create' => true,  'can_manage' => false, 'can_evaluate' => false],
                    'nikitina.v@example.com' => ['can_create' => false, 'can_manage' => false, 'can_evaluate' => true],
                ],
            ],
            [
                'name'          => 'Центр эстрадного искусства «Звезда»',
                'inn'           => '2308901226',
                'ogrn'          => '1032308901226',
                'description'   => 'Краснодарский центр эстрадного и вокального искусства. Организуем региональные и всероссийские конкурсы.',
                'legal_address' => 'г. Краснодар, ул. Красная, д. 101',
                'contact_email' => 'zvezda@krd-arts.ru',
                'contact_phone' => '+78612890123',
                'website'       => null,
                'status'        => 'verified',
                'creator_email' => 'morozova.s@example.com',
                'reps'          => [
                    'gavrilov.p@example.com' => ['can_create' => true, 'can_manage' => true, 'can_evaluate' => false],
                ],
            ],
            [
                'name'          => 'Образовательный центр «Олимп»',
                'inn'           => '5258012337',
                'ogrn'          => '1035258012337',
                'description'   => 'Многопрофильный образовательный центр. Конкурсы по науке, технике и творчеству.',
                'legal_address' => 'г. Нижний Новгород, ул. Горького, д. 34',
                'contact_email' => 'olimp@nn-edu.ru',
                'contact_phone' => '+78313012345',
                'website'       => 'https://olimp-nn.ru',
                'status'        => 'verified',
                'creator_email' => 'popov.i@example.com',
                'reps'          => [
                    'stepanov.e@example.com'  => ['can_create' => true,  'can_manage' => true,  'can_evaluate' => true],
                    'solovyev.v@example.com'  => ['can_create' => false, 'can_manage' => false, 'can_evaluate' => true],
                ],
            ],
            [
                'name'          => 'Детский театральный центр «Маски»',
                'inn'           => '6317123448',
                'ogrn'          => '1036317123448',
                'description'   => 'Театральный центр для детей и молодёжи. Проводим конкурсы любительских театральных постановок.',
                'legal_address' => 'г. Самара, пр. Ленина, д. 56',
                'contact_email' => 'maski@samara-theatre.ru',
                'contact_phone' => '+78462123456',
                'website'       => 'https://samara-theatre.ru',
                'status'        => 'verified',
                'creator_email' => 'volkova.t@example.com',
                'reps'          => [
                    'kuzmin.a@example.com'    => ['can_create' => true,  'can_manage' => false, 'can_evaluate' => true],
                    'tihonova.l@example.com'  => ['can_create' => false, 'can_manage' => false, 'can_evaluate' => true],
                ],
            ],
            [
                'name'          => 'Ассоциация молодых художников Тюмени',
                'inn'           => '7204234559',
                'ogrn'          => '1037204234559',
                'description'   => 'Региональная ассоциация поддержки молодых художников. Организуем выставки и художественные конкурсы.',
                'legal_address' => 'г. Тюмень, ул. Республики, д. 78',
                'contact_email' => 'amht@art-tyumen.ru',
                'contact_phone' => '+73452234567',
                'website'       => null,
                'status'        => 'pending',
                'creator_email' => 'novikov.s@example.com',
                'reps'          => [],
            ],
            [
                'name'          => 'Школа цифровых искусств «Медиум»',
                'inn'           => '6165345660',
                'ogrn'          => null,
                'description'   => 'Современная школа цифровых и медиаискусств. Конкурсы по веб-дизайну, анимации и цифровой живописи.',
                'legal_address' => 'г. Ростов-на-Дону, пр. Садовый, д. 90',
                'contact_email' => 'medium@rostov-digital.ru',
                'contact_phone' => null,
                'website'       => 'https://medium-school.ru',
                'status'        => 'pending',
                'creator_email' => 'yakovleva.e@example.com',
                'reps'          => [],
            ],
            [
                'name'          => 'Региональный центр развития одарённости',
                'inn'           => '7727456771',
                'ogrn'          => '1037727456771',
                'description'   => 'Специализированный центр поддержки и развития одарённых детей Московской области.',
                'legal_address' => 'г. Москва, ул. Садовая-Кудринская, д. 11',
                'contact_email' => 'rcro@gifted-children.ru',
                'contact_phone' => '+74957890123',
                'website'       => 'https://gifted-children.ru',
                'status'        => 'pending',
                'creator_email' => 'voronov.m@example.com',
                'reps'          => [
                    'zhuravlev.r@example.com'  => ['can_create' => true, 'can_manage' => true, 'can_evaluate' => false],
                ],
            ],
            [
                'name'          => 'Творческое объединение «Радуга»',
                'inn'           => '0987654321',
                'ogrn'          => null,
                'description'   => 'Межрегиональное творческое объединение педагогов и детей.',
                'legal_address' => null,
                'contact_email' => 'raduga@creative-union.ru',
                'contact_phone' => null,
                'website'       => null,
                'status'        => 'pending',
                'creator_email' => 'parent@example.com',
                'reps'          => [],
            ],
        ];

        foreach ($orgsData as $orgData) {
            $creatorEmail  = $orgData['creator_email'];
            $reps          = $orgData['reps'];
            $isVerified    = $orgData['status'] === 'verified';
            $creator       = $parents[$creatorEmail]
                ?? $soloParticipants[$creatorEmail]
                ?? User::where('email', $creatorEmail)->first();

            if (! $creator) {
                continue;
            }

            $org = Organization::firstOrCreate(
                ['inn' => $orgData['inn']],
                [
                    'name'          => $orgData['name'],
                    'ogrn'          => $orgData['ogrn'] ?? null,
                    'description'   => $orgData['description'],
                    'legal_address' => $orgData['legal_address'] ?? null,
                    'contact_email' => $orgData['contact_email'],
                    'contact_phone' => $orgData['contact_phone'] ?? null,
                    'website'       => $orgData['website'] ?? null,
                    'status'        => $orgData['status'],
                    'created_by'    => $creator->id,
                    'verified_by'   => $isVerified ? ($admin?->id ?? $creator->id) : null,
                    'verified_at'   => $isVerified ? now() : null,
                ]
            );

            // Creator always gets full permissions
            $org->representatives()->syncWithoutDetaching([
                $creator->id => [
                    'can_create'   => true,
                    'can_manage'   => true,
                    'can_evaluate' => true,
                ],
            ]);

            // Additional reps
            foreach ($reps as $repEmail => $perms) {
                $rep = $parents[$repEmail]
                    ?? $soloParticipants[$repEmail]
                    ?? User::where('email', $repEmail)->first();
                if ($rep) {
                    $org->representatives()->syncWithoutDetaching([
                        $rep->id => $perms,
                    ]);
                }
            }
        }

        $orgCount = count($orgsData);
        $this->command->info("Organizations: {$orgCount} created/found");

        // ── Summary ─────────────────────────────────────

        $total = User::count();
        $this->command->newLine();
        $this->command->info("✓ Total users in DB: {$total}");
        $this->command->info("✓ Total organizations in DB: " . Organization::count());
        $this->command->newLine();
        $this->command->line('  admin@talentcenter.ru    / password  (Admin)');
        $this->command->line('  support@talentcenter.ru  / password  (Support)');
        $this->command->line('  moderator@talentcenter.ru/ password  (Support)');
        $this->command->line('  helper@talentcenter.ru   / password  (Support)');
        $this->command->line('  participant@example.com  / password  (Participant, has org + children)');
        $this->command->line('  parent@example.com       / password  (Participant, 2 children)');
        $this->command->line('  sokolova.n@example.com   / password  (Participant, 3 children, org owner)');
    }
}
