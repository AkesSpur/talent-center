{{--
    Cabinet sidebar. Docked on wide screens (xl, 1280px+); an off-canvas drawer below that,
    opened from the top bar's menu button. Docked, the same top-bar button narrows it to
    icons (the xl:sidebar-compact:… classes). Open/close and narrow state live in the appShell
    Alpine component on the layout root (resources/js/app-shell.js).
--}}
<aside id="app-sidebar"
    class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-white border-r border-gold/15
           -translate-x-full xl:translate-x-0 transition-[transform,width] duration-300 ease-[cubic-bezier(0.32,0.72,0,1)] motion-reduce:transition-none
           xl:sidebar-compact:w-20"
    :class="{ '!translate-x-0 shadow-2xl': sidebarOpen }"
    aria-label="Меню личного кабинета">

    {{-- Brand --}}
    <div class="flex h-16 shrink-0 items-center gap-3 border-b border-gold/10 px-5 xl:sidebar-compact:justify-center xl:sidebar-compact:px-0">
        <a href="/" class="flex min-w-0 items-center gap-3">
            @if(!empty($siteSettings[\App\Models\SiteSettings::SITE_LOGO]))
                <img src="{{ asset('storage/' . $siteSettings[\App\Models\SiteSettings::SITE_LOGO]) }}"
                    alt="" class="h-9 w-auto max-w-[36px] shrink-0 object-contain">
            @else
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full gradient-gold shadow-sm">
                    <i class="fas fa-award text-sm text-white" aria-hidden="true"></i>
                </div>
            @endif
            <div class="min-w-0 xl:sidebar-compact:sr-only">
                <p class="truncate font-serif text-base font-bold leading-tight"
                    style="color: {{ $siteSettings[\App\Models\SiteSettings::SITE_NAME_COLOR] ?? '#8B4513' }}">
                    {{ $siteSettings[\App\Models\SiteSettings::SITE_NAME] ?? 'Талант-центр' }}
                </p>
                <p class="line-clamp-2 text-[11px] leading-tight"
                    style="color: {{ $siteSettings[\App\Models\SiteSettings::SITE_SUBTITLE_COLOR] ?? '#9A8B7A' }}">
                    {{ $siteSettings[\App\Models\SiteSettings::SITE_SUBTITLE] ?? 'Всероссийский центр талантов' }}
                </p>
            </div>
        </a>
        <button type="button" x-ref="sidebarClose" @click="closeSidebar()"
            class="ml-auto inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-warm-gray transition hover:bg-cream hover:text-dark active:scale-95 xl:hidden"
            aria-label="Закрыть меню">
            <i class="fas fa-xmark text-lg" aria-hidden="true"></i>
        </button>
    </div>

    {{-- Sections --}}
    @php $firstDocked = collect($sections)->search(fn ($section) => ! $section['mobileOnly']); @endphp
    <nav class="flex-1 space-y-5 overflow-y-auto overscroll-contain px-3 py-5 xl:sidebar-compact:space-y-3" aria-label="Разделы"
        @scroll.passive="hideTip()">
        @foreach($sections as $section)
            @php $startsFolded = $section['collapsedByDefault'] && ! $section['hasActive']; @endphp
            <div x-data="navSection(@js($section['key']), @js($section['hasActive']), @js($section['collapsedByDefault']))"
                @class([
                    'xl:hidden' => $section['mobileOnly'],
                    // Narrow, the headings are hidden and a line separates the sections instead
                    'xl:sidebar-compact:border-t xl:sidebar-compact:border-gold/15 xl:sidebar-compact:pt-3' => $loop->index > $firstDocked,
                ])>
                <button type="button" @click="toggle()"
                    :aria-expanded="open.toString()"
                    aria-expanded="{{ $startsFolded ? 'false' : 'true' }}"
                    aria-controls="nav-section-{{ $section['key'] }}"
                    class="flex w-full items-center justify-between rounded-md px-3 py-1 text-xs font-semibold uppercase tracking-wider text-warm-gray transition-colors hover:text-dark xl:sidebar-compact:hidden">
                    <span>{{ $section['label'] }}</span>
                    <i @class(['fas fa-chevron-down text-[10px]', '-rotate-90' => $startsFolded])
                        :class="{ '-rotate-90': !open, 'transition-transform duration-200': animate }" aria-hidden="true"></i>
                </button>

                {{-- Server renders the default fold state so the menu doesn't jump when JS starts.
                     Narrow, every section shows: without headings a folded one couldn't be reopened. --}}
                <ul id="nav-section-{{ $section['key'] }}" x-show="open" class="mt-1 space-y-0.5 xl:sidebar-compact:mt-0 xl:sidebar-compact:!block"
                    @if($startsFolded) style="display: none" @endif>
                    @foreach($section['items'] as $item)
                        <li>
                            <a href="{{ $item['url'] }}"
                                @if($item['active']) aria-current="page" @endif
                                data-tip="{{ $item['label'] }}"
                                @mouseenter="showTip($el)" @mouseleave="hideTip()" @focus="showTip($el)" @blur="hideTip()"
                                @class([
                                    'group relative flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition-colors active:bg-primary/15 xl:sidebar-compact:justify-center xl:sidebar-compact:px-0 xl:sidebar-compact:py-2.5',
                                    'bg-primary/10 font-medium text-primary' => $item['active'],
                                    'text-dark/80 hover:bg-cream hover:text-dark' => ! $item['active'],
                                ])>
                                @if($item['active'])
                                    <span class="absolute left-0 top-1/2 h-5 w-1 -translate-y-1/2 rounded-r bg-primary" aria-hidden="true"></span>
                                @endif
                                <i @class([
                                    'fas w-5 shrink-0 text-center xl:sidebar-compact:text-base xl:sidebar-compact:leading-none', $item['icon'],
                                    'text-primary' => $item['active'],
                                    'text-warm-gray transition-colors group-hover:text-primary' => ! $item['active'],
                                ]) aria-hidden="true"></i>
                                <span class="flex-1 leading-snug xl:sidebar-compact:sr-only">{{ $item['label'] }}</span>
                                @if($item['badge'] > 0)
                                    <span @class([
                                        'inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full px-1.5 text-[11px] font-semibold',
                                        // Narrow, the count sits on the icon's corner
                                        'xl:sidebar-compact:absolute xl:sidebar-compact:right-1 xl:sidebar-compact:top-0 xl:sidebar-compact:h-4 xl:sidebar-compact:min-w-[1rem] xl:sidebar-compact:px-1 xl:sidebar-compact:text-[10px]',
                                        'bg-orange-100 text-orange-700' => $item['badgeTone'] === 'attention',
                                        'bg-gold/20 text-primary' => $item['badgeTone'] === 'queue',
                                    ]) title="Заявки с новыми сообщениями">
                                        {{ $item['badge'] > 99 ? '99+' : $item['badge'] }}
                                    </span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </nav>

    {{-- Account --}}
    @if($user)
        @php $shortName = $user->last_name . ' ' . mb_substr($user->first_name, 0, 1) . '.' . ($user->patronymic ? mb_substr($user->patronymic, 0, 1) . '.' : ''); @endphp
        <div class="shrink-0 border-t border-gold/10 p-3">
            <a href="{{ route('profile.edit') }}"
                data-tip="{{ $shortName }}"
                @mouseenter="showTip($el)" @mouseleave="hideTip()" @focus="showTip($el)" @blur="hideTip()"
                class="flex items-center gap-3 rounded-lg p-2 transition-colors hover:bg-cream xl:sidebar-compact:justify-center">
                <x-user-avatar :user="$user" size="md" />
                <div class="min-w-0 xl:sidebar-compact:sr-only">
                    <p class="truncate text-sm font-medium text-dark">{{ $shortName }}</p>
                    <p class="truncate text-xs text-warm-gray">{{ $user->role->label() }}</p>
                </div>
            </a>
            <form method="POST" action="{{ route('logout') }}" class="mt-1">
                @csrf
                <button type="submit"
                    data-tip="Выйти"
                    @mouseenter="showTip($el)" @mouseleave="hideTip()" @focus="showTip($el)" @blur="hideTip()"
                    class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm text-red-600 transition-colors hover:bg-red-50 active:bg-red-100 xl:sidebar-compact:justify-center xl:sidebar-compact:px-0">
                    <i class="fas fa-arrow-right-from-bracket w-5 text-center" aria-hidden="true"></i>
                    <span class="xl:sidebar-compact:sr-only">Выйти</span>
                </button>
            </form>
        </div>
    @endif

    {{-- Names the icon under the pointer or focus while the sidebar is narrow. Visual only:
         each link keeps its label for screen readers. --}}
    <div x-show="tip.show" x-cloak x-text="tip.text" :style="`top: ${tip.top}px`"
        class="pointer-events-none absolute left-full z-10 ml-2 -translate-y-1/2 whitespace-nowrap rounded-md bg-dark px-2.5 py-1.5 text-xs font-medium text-white shadow-lg"
        aria-hidden="true"></div>
</aside>
