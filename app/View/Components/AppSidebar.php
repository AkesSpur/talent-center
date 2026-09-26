<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * The cabinet sidebar. This is the single definition of the logged-in
 * navigation: sections are built per role, so every role sees exactly the
 * pages it can open.
 */
class AppSidebar extends Component
{
    /** @var array<int, array{key: string, label: string, items: array<int, array<string, mixed>>, hasActive: bool, mobileOnly: bool, collapsedByDefault: bool}> */
    public array $sections;

    public ?User $user;

    public function __construct()
    {
        $this->user = Auth::user();
        $this->sections = $this->user ? $this->buildSections($this->user) : [];
    }

    public function render(): View
    {
        return view('components.app-sidebar');
    }

    /** @return array<int, array<string, mixed>> */
    private function buildSections(User $user): array
    {
        $sections = [];

        // Site links live in the top bar; below 1280px the drawer is the main
        // navigation, so repeat them there (hidden once the sidebar is docked).
        $sections[] = [
            'key'        => 'site',
            'label'      => 'Сайт',
            'mobileOnly' => true,
            'items'      => [
                $this->item('Главная', 'fa-house', 'home', ['home']),
                $this->item('Каталог конкурсов', 'fa-compass', 'contests.index', ['contests.index', 'contests.show']),
            ],
        ];

        // Order is the client's (26.09): the helpdesk first, then the rest of the
        // admin area, then the two personal sections — most important at the top.

        if ($user->isSupport()) {
            $sections[] = [
                'key'   => 'support',
                'label' => 'Поддержка',
                'items' => [
                    $this->item('Панель поддержки', 'fa-headset', 'support.dashboard', ['support.dashboard']),
                    $this->item('Заявки поддержки', 'fa-inbox', 'admin.support.tickets.index', ['admin.support.tickets.*'], $this->unreadForStaff(), 'queue'),
                    $this->item('Аналитика', 'fa-chart-line', 'admin.support.analytics', ['admin.support.analytics']),
                    $this->item('Пользователи', 'fa-users', 'support.users.index', ['support.users.*']),
                    $this->item('Организации', 'fa-sitemap', 'support.organizations.index', ['support.organizations.*']),
                    $this->item('Конкурсы', 'fa-trophy', 'support.contests.index', ['support.contests.*']),
                ],
            ];
        }

        if ($user->isAdmin()) {
            // The helpdesk gets its own section rather than sitting among the
            // fourteen «Администрирование» links.
            $sections[] = [
                'key'   => 'support',
                'label' => 'Поддержка',
                'items' => [
                    $this->item('Заявки поддержки', 'fa-inbox', 'admin.support.tickets.index', ['admin.support.tickets.*'], $this->unreadForStaff(), 'queue'),
                    $this->item('Аналитика', 'fa-chart-line', 'admin.support.analytics', ['admin.support.analytics']),
                    $this->item('Настройки поддержки', 'fa-sliders', 'admin.support.categories.index', ['admin.support.categories.*']),
                ],
            ];

            $sections[] = [
                'key'   => 'admin',
                'label' => 'Администрирование',
                'items' => [
                    $this->item('Админ-панель', 'fa-chart-pie', 'admin.dashboard', ['admin.dashboard']),
                    $this->item('Пользователи', 'fa-users', 'admin.users.index', ['admin.users.*']),
                    $this->item('Организации', 'fa-sitemap', 'admin.organizations.index', ['admin.organizations.*']),
                    $this->item('Конкурсы', 'fa-trophy', 'admin.contests.index', ['admin.contests.*', 'admin.evaluation.*']),
                    $this->item('Заявки', 'fa-file-alt', 'admin.applications.index', ['admin.applications.*']),
                    $this->item('Платежи', 'fa-credit-card', 'admin.payments.index', ['admin.payments.*']),
                    $this->item('Реестр выплат', 'fa-file-invoice-dollar', 'admin.payout-registries.index', ['admin.payout-registries.*']),
                    $this->item('Жанры', 'fa-tags', 'admin.platform-categories.index', ['admin.platform-categories.*']),
                    $this->item('Обложки конкурсов', 'fa-images', 'admin.contest-covers.index', ['admin.contest-covers.*']),
                    $this->item('Фоны дипломов', 'fa-image', 'admin.diploma-backgrounds.index', ['admin.diploma-backgrounds.*']),
                    $this->item('Журнал действий', 'fa-list-check', 'admin.action-logs.index', ['admin.action-logs.*']),
                    $this->item('Общие настройки', 'fa-gear', 'admin.settings.index', ['admin.settings.*']),
                ],
            ];
        }

        $sections[] = [
            'key'   => 'organizer',
            'label' => 'Организатор конкурсов',
            'items' => [
                $this->item('Конкурсы', 'fa-award', 'dashboard.contests', ['dashboard.contests', 'contests.create', 'contests.edit']),
                $this->item('Управление организацией', 'fa-sitemap', 'organizations.index', ['organizations.*', 'evaluation.*']),
            ],
        ];

        $sections[] = [
            'key'   => 'participant',
            'label' => 'Участник конкурсов',
            'items' => [
                $this->item('Обзор', 'fa-gauge-high', 'dashboard', ['dashboard']),
                $this->item('Профиль', 'fa-user-circle', 'profile.edit', ['profile.*']),
                $this->item('Заявки', 'fa-file-alt', 'dashboard.applications', ['dashboard.applications', 'applications.*']),
                $this->item('Награды', 'fa-trophy', 'dashboard.diplomas', ['dashboard.diplomas']),
                $this->item('Участники', 'fa-users', 'participants.index', ['participants.*']),
                // Stays «Поддержка»: ТЗ 10.1 names this section in the user's cabinet.
                $this->item('Поддержка', 'fa-headset', 'tickets.index', ['tickets.*'], $this->unreadForUser($user), 'attention'),
            ],
        ];

        // Staff come here to work: their personal sections start folded so the
        // admin/support menu is visible without scrolling. Users can reopen them.
        $isStaff = $user->isAdmin() || $user->isSupport();

        foreach ($sections as &$section) {
            $section['hasActive'] = collect($section['items'])->contains('active', true);
            $section['mobileOnly'] ??= false;
            $section['collapsedByDefault'] = $isStaff && in_array($section['key'], ['participant', 'organizer'], true);
        }

        return $sections;
    }

    /**
     * @param  array<int, string>  $activePatterns  route-name patterns that highlight this item
     * @return array<string, mixed>
     */
    private function item(
        string $label,
        string $icon,
        string $route,
        array $activePatterns,
        int $badge = 0,
        string $badgeTone = 'queue',
    ): array {
        return [
            'label'     => $label,
            'icon'      => $icon,
            'url'       => route($route),
            'active'    => request()->routeIs(...$activePatterns),
            'badge'     => $badge,
            'badgeTone' => $badgeTone,
        ];
    }

    /** The user's tickets where support replied after their last visit. */
    private function unreadForUser(User $user): int
    {
        return SupportTicket::where('user_id', $user->id)->unreadForOwner()->count();
    }

    /** Open tickets waiting on the helpdesk: never opened, or the user wrote since. */
    private function unreadForStaff(): int
    {
        return once(fn () => SupportTicket::open()->unreadForStaff()->count());
    }
}
