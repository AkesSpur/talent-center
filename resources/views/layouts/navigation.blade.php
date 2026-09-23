{{--
    Top bar of the cabinet. Section navigation moved to the sidebar
    (components/app-sidebar); this bar keeps the menu button (opens the drawer on
    small screens, narrows the docked sidebar on wide ones), the links back to the
    public site and the account shortcut.
--}}
<header class="app-topbar sticky top-0 z-30 h-16 border-b border-gold/10 bg-cream/80 backdrop-blur-md">
    <div class="flex h-full items-center gap-3 px-4 sm:px-6 lg:px-8">

        <button type="button" x-ref="menuButton" @click="openSidebar()"
            class="-ml-2 inline-flex h-10 w-10 items-center justify-center rounded-lg text-dark transition hover:bg-cream-dark active:scale-95 xl:hidden"
            aria-controls="app-sidebar" :aria-expanded="sidebarOpen.toString()" aria-label="Открыть меню">
            <i class="fas fa-bars text-lg" aria-hidden="true"></i>
        </button>

        <button type="button" @click="toggleCompact()"
            class="-ml-2 hidden h-10 w-10 items-center justify-center rounded-lg text-dark transition hover:bg-cream-dark active:scale-95 xl:inline-flex"
            aria-controls="app-sidebar" aria-label="Свернуть меню" title="Свернуть меню"
            :aria-label="compact ? 'Развернуть меню' : 'Свернуть меню'" :title="compact ? 'Развернуть меню' : 'Свернуть меню'">
            <i class="fas fa-bars text-lg" aria-hidden="true"></i>
        </button>

        {{-- Brand: the docked sidebar shows it on desktop --}}
        <a href="/" class="flex min-w-0 items-center gap-2 xl:hidden">
            @if(!empty($siteSettings[\App\Models\SiteSettings::SITE_LOGO]))
                <img src="{{ asset('storage/' . $siteSettings[\App\Models\SiteSettings::SITE_LOGO]) }}"
                    alt="" class="h-8 w-auto max-w-[32px] shrink-0 object-contain">
            @else
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full gradient-gold">
                    <i class="fas fa-award text-xs text-white"></i>
                </div>
            @endif
            <span class="hidden truncate font-serif text-base font-bold sm:inline"
                style="color: {{ $siteSettings[\App\Models\SiteSettings::SITE_NAME_COLOR] ?? '#8B4513' }}">
                {{ $siteSettings[\App\Models\SiteSettings::SITE_NAME] ?? 'Талант-центр' }}
            </span>
        </a>

        <nav class="ml-4 hidden items-center gap-6 md:flex xl:ml-2" aria-label="Сайт">
            <a href="/" class="text-sm font-medium text-warm-gray transition-colors hover:text-primary">Главная</a>
            <a href="{{ route('contests.index') }}" class="text-sm font-medium text-warm-gray transition-colors hover:text-primary">Конкурсы</a>
        </nav>

        <a href="{{ route('profile.edit') }}"
            class="ml-auto flex items-center gap-2 rounded-full py-1 pl-1 pr-1 transition-colors hover:bg-cream-dark sm:pr-3"
            title="Профиль">
            <x-user-avatar :user="Auth::user()" size="sm" />
            <span class="hidden text-sm font-medium text-dark sm:inline">
                {{ Auth::user()->last_name }} {{ mb_substr(Auth::user()->first_name, 0, 1) }}.{{ Auth::user()->patronymic ? mb_substr(Auth::user()->patronymic, 0, 1) . '.' : '' }}
            </span>
        </a>
    </div>
</header>
