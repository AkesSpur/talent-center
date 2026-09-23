<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Support;

use App\Http\Controllers\Controller;
use App\Models\SiteSettings;
use App\Models\SupportCategory;
use App\Services\ActionLogService;
use App\Services\SlaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * "Настройки поддержки" — categories, subcategories and helpdesk settings.
 */
class SupportCategoryController extends Controller
{
    public function index(): View
    {
        $categories = SupportCategory::roots()->ordered()
            ->with(['children' => fn ($q) => $q->orderBy('sort_order')->orderBy('name')])
            ->withCount('tickets')
            ->get();

        return view('admin.support.categories.index', [
            'categories'   => $categories,
            'slaHours'     => app(SlaService::class)->hours(),
            'supportEmail' => SiteSettings::get(SiteSettings::SUPPORT_EMAIL, ''),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateCategory($request);

        $category = SupportCategory::create([
            'parent_id'   => $data['parent_id'] ?? null,
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'sort_order'  => $data['sort_order'] ?? 0,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        ActionLogService::log('support.category.created', $category, [
            'name'      => $category->name,
            'parent_id' => $category->parent_id,
        ]);

        return redirect()->route('admin.support.categories.index')
            ->with('status', 'support-category-created');
    }

    public function update(Request $request, SupportCategory $category): RedirectResponse
    {
        $data = $this->validateCategory($request, $category);

        $old = $category->only(['name', 'sort_order', 'is_active', 'parent_id']);

        $category->update([
            'parent_id'   => $data['parent_id'] ?? null,
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'sort_order'  => $data['sort_order'] ?? 0,
            // No default here: an archived category must stay archived when edited.
            'is_active'   => $request->boolean('is_active'),
        ]);

        ActionLogService::log('support.category.updated', $category, [
            'old' => $old,
            'new' => $category->only(['name', 'sort_order', 'is_active', 'parent_id']),
        ]);

        return redirect()->route('admin.support.categories.index')
            ->with('status', 'support-category-updated');
    }

    /** Archiving is the safe alternative to deleting (TZ section 4.2). */
    public function toggleArchive(SupportCategory $category): RedirectResponse
    {
        $category->update(['is_active' => ! $category->is_active]);

        if (! $category->is_active) {
            // Archiving a category hides its subcategories from the ticket form too.
            $category->children()->update(['is_active' => false]);
        }

        ActionLogService::log('support.category.archived', $category, [
            'is_active' => $category->is_active,
        ]);

        return redirect()->route('admin.support.categories.index')
            ->with('status', $category->is_active ? 'support-category-restored' : 'support-category-archived');
    }

    public function destroy(SupportCategory $category): RedirectResponse
    {
        $linked = $category->linkedTicketsCount();

        if ($linked > 0) {
            return redirect()->route('admin.support.categories.index')
                ->with('error', 'В этой категории есть заявки. Для удаления сначала перенесите их в другую категорию или заархивируйте.');
        }

        if ($category->children()->exists()) {
            return redirect()->route('admin.support.categories.index')
                ->with('error', 'Сначала удалите или перенесите подкатегории.');
        }

        ActionLogService::log('support.category.deleted', null, [
            'name' => $category->name,
            'id'   => $category->id,
        ]);

        $category->delete();

        return redirect()->route('admin.support.categories.index')
            ->with('status', 'support-category-deleted');
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'support_sla_hours' => ['required', 'integer', 'min:1', 'max:720'],
            'support_email'     => ['nullable', 'email', 'max:255'],
        ], [
            'support_sla_hours.required' => 'Укажите срок SLA в часах.',
            'support_sla_hours.integer'  => 'Срок SLA — целое число часов.',
            'support_sla_hours.min'      => 'Срок SLA должен быть не меньше 1 часа.',
            'support_sla_hours.max'      => 'Срок SLA должен быть не больше 720 часов (30 дней).',
            'support_email.email'        => 'Укажите корректный email.',
            'support_email.max'          => 'Email не должен превышать 255 символов.',
        ]);

        SiteSettings::set(SiteSettings::SUPPORT_SLA_HOURS, (string) $data['support_sla_hours']);
        SiteSettings::set(SiteSettings::SUPPORT_EMAIL, $data['support_email'] ?? null);
        cache()->forget('site_settings');

        ActionLogService::log('support.settings.updated', null, $data);

        return redirect()->route('admin.support.categories.index')
            ->with('status', 'support-settings-updated');
    }

    /** @return array<string, mixed> */
    private function validateCategory(Request $request, ?SupportCategory $category = null): array
    {
        return $request->validate([
            'parent_id'   => [
                'nullable', 'integer',
                Rule::exists('support_categories', 'id')->whereNull('parent_id'),
                // A category cannot become its own parent.
                Rule::notIn($category ? [$category->id] : []),
                // ...nor can a category that already has children become a child itself.
                function (string $attribute, $value, \Closure $fail) use ($category) {
                    if ($value && $category && $category->children()->exists()) {
                        $fail('Нельзя вложить категорию, у которой есть подкатегории.');
                    }
                },
            ],
            'name'        => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['nullable', 'boolean'],
        ], [
            'parent_id.integer'  => 'Выберите родительскую категорию из списка.',
            'parent_id.exists'   => 'Родительская категория не найдена или сама является подкатегорией.',
            'parent_id.not_in'   => 'Категория не может быть вложена сама в себя.',
            'name.required'      => 'Укажите название.',
            'name.string'        => 'Укажите название.',
            'name.max'           => 'Название не должно превышать 50 символов.',
            'description.string' => 'Описание должно быть текстом.',
            'description.max'    => 'Описание не должно превышать 1000 символов.',
            'sort_order.integer' => 'Порядок — целое число.',
            'sort_order.min'     => 'Порядок не может быть отрицательным.',
            'is_active.boolean'  => 'Не удалось прочитать статус «Активна» — обновите страницу.',
        ]);
    }
}
