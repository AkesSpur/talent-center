<nav x-data="{ open: false }" class="bg-cream shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">

            <!-- Logo -->
            <a href="/" class="flex items-center space-x-3">
                <div class="w-11 h-11 gradient-gold rounded-full flex items-center justify-center shadow-sm">
                    <i class="fas fa-award text-white text-lg"></i>
                </div>
                <div>
                    <h1 class="font-serif text-lg font-bold text-primary leading-tight">Талант-центр</h1>
                    <p class="text-xs text-warm-gray leading-tight">Всероссийский центр талантов</p>
                </div>
            </a>

            <!-- Right Side: User Dropdown (all screens) -->
            <x-dropdown align="right" width="72">
                <x-slot name="trigger">
                    <button class="inline-flex items-center space-x-2 px-3 py-2 text-sm font-medium text-dark hover:text-primary focus:outline-none transition duration-150">
                        <x-user-avatar :user="Auth::user()" size="sm" />
                        <span class="hidden sm:inline">{{ Auth::user()->email }}</span>
                        <i class="fas fa-chevron-down text-xs text-warm-gray"></i>
                    </button>
                </x-slot>

                <x-slot name="content">
                    <!-- Personal section -->
                    <x-dropdown-link :href="route('profile.edit')">
                        <i class="fas fa-user-circle mr-2 text-warm-gray w-5 text-center"></i> Мой профиль
                    </x-dropdown-link>
                    <x-dropdown-link href="#">
                        <i class="fas fa-file-alt mr-2 text-warm-gray w-5 text-center"></i> Мои заявки
                    </x-dropdown-link>
                    <x-dropdown-link href="#">
                        <i class="fas fa-trophy mr-2 text-warm-gray w-5 text-center"></i> Мои награды
                    </x-dropdown-link>
                    <x-dropdown-link :href="route('participants.index')">
                        <i class="fas fa-users mr-2 text-warm-gray w-5 text-center"></i> Мои участники
                    </x-dropdown-link>

                    <!-- Organizer section -->
                    <div class="border-t border-gold/10 mt-1 pt-1">
                        <div class="px-4 py-2 text-xs font-semibold text-warm-gray uppercase tracking-wider">Организатор</div>
                        <x-dropdown-link :href="route('dashboard')">
                            <i class="fas fa-th-large mr-2 text-warm-gray w-5 text-center"></i> Личный кабинет
                        </x-dropdown-link>
                        <x-dropdown-link href="#">
                            <i class="fas fa-trophy mr-2 text-warm-gray w-5 text-center"></i> Мои конкурсы
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('organizations.index')">
                            <i class="fas fa-sitemap mr-2 text-warm-gray w-5 text-center"></i> Управление организацией
                        </x-dropdown-link>
                    </div>

                    <!-- Admin section -->
                    @if(auth()->user()->isAdmin())
                        <div class="border-t border-gold/10 mt-1 pt-1">
                            <div class="px-4 py-2 text-xs font-semibold text-warm-gray uppercase tracking-wider">Администрирование</div>
                            <x-dropdown-link :href="route('admin.dashboard')">
                                <i class="fas fa-cog mr-2 text-warm-gray w-5 text-center"></i> Админ-панель
                            </x-dropdown-link>
                        </div>
                    @endif

                    <!-- Support section -->
                    @if(auth()->user()->isSupport())
                        <div class="border-t border-gold/10 mt-1 pt-1">
                            <div class="px-4 py-2 text-xs font-semibold text-warm-gray uppercase tracking-wider">Поддержка</div>
                            <x-dropdown-link :href="route('support.dashboard')">
                                <i class="fas fa-headset mr-2 text-warm-gray w-5 text-center"></i> Панель поддержки
                            </x-dropdown-link>
                        </div>
                    @endif

                    <!-- Logout -->
                    <div class="border-t border-gold/10 mt-1 pt-1">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();"
                                    class="text-red-600 hover:bg-red-50">
                                <i class="fas fa-sign-out-alt mr-2 w-5 text-center"></i> Выйти
                            </x-dropdown-link>
                        </form>
                    </div>
                </x-slot>
            </x-dropdown>
        </div>
    </div>
</nav>
