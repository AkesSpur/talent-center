<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\SupportTicketStatus;
use App\Models\SupportCategory;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * The site runs with APP_LOCALE=ru: forms that don't word their own errors
 * fall back to lang/ru, and Laravel's own English messages are translated.
 */
class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_errors_are_in_russian(): void
    {
        User::factory()->create(['email' => 'ivan@example.com']);

        $this->post('/register', [
            'first_name'            => '',
            'last_name'             => 'Иванов',
            'email'                 => 'ivan@example.com',
            'password'              => 'secret123',
            'password_confirmation' => 'secret124',
        ])->assertSessionHasErrors([
            'first_name' => 'Поле «Имя» обязательно для заполнения.',
            'email'      => 'Пользователь с таким email уже зарегистрирован.',
            'password'   => 'Пароли не совпадают.',
        ]);
    }

    public function test_failed_login_is_explained_in_russian(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password'])
            ->assertSessionHasErrors(['email' => 'Неверный email или пароль.']);
    }

    public function test_organization_form_names_its_fields_in_russian(): void
    {
        $user = User::factory()->create(['role' => 'participant']);

        $this->actingAs($user)->post(route('organizations.store'), [
            'name'          => 'Школа искусств',
            'inn'           => '',
            'website'       => 'shkola.ru',
            'contact_email' => 'shkola@example.com',
        ])->assertSessionHasErrors([
            'inn'     => 'Поле «ИНН» обязательно для заполнения.',
            'website' => 'Поле «Веб-сайт» должно содержать корректную ссылку, например https://example.ru.',
        ]);
    }

    public function test_error_summary_in_json_replies_is_in_russian(): void
    {
        $user = User::factory()->create(['role' => 'participant']);

        $this->actingAs($user)
            ->postJson(route('tickets.store'), [])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Выберите категорию обращения. (и ещё 2 ошибки)');
    }

    public function test_access_denied_page_has_no_english_text(): void
    {
        $owner = User::factory()->create(['role' => 'participant']);
        $other = User::factory()->create(['role' => 'participant']);
        $ticket = SupportTicket::create([
            'user_id'     => $owner->id,
            'category_id' => SupportCategory::create(['name' => 'Оплата', 'is_active' => true])->id,
            'subject'     => 'Тема',
            'description' => 'Описание',
            'status'      => SupportTicketStatus::New,
        ]);

        $this->actingAs($other)->get(route('tickets.show', $ticket))
            ->assertForbidden()
            ->assertSee('У вас недостаточно прав для просмотра этой страницы.')
            ->assertDontSee('This action is unauthorized.');
    }

    public function test_error_codes_without_their_own_page_get_a_russian_one(): void
    {
        // As on the live site: without errors/4xx, Symfony's English "Oops! An Error Occurred" page shows here.
        config(['app.debug' => false]);
        Route::get('/_test/bad-request', fn () => abort(400));
        Route::get('/_test/teapot', fn () => abort(418));

        $this->get('/logout')
            ->assertStatus(405)
            ->assertSee('Действие недоступно по ссылке')
            ->assertDontSee('An Error Occurred');
        $this->get('/_test/bad-request')->assertStatus(400)->assertSee('Неверный запрос');
        $this->get('/_test/teapot')->assertStatus(418)->assertSee('Запрос не выполнен');
    }
}
