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

            <!-- Desktop Nav Links -->
            <div class="hidden sm:flex sm:items-center sm:space-x-6">
                @if(auth()->user()->isAdmin())
                    <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                        <i class="fas fa-tachometer-alt mr-1.5"></i> Панель администратора
                    </x-nav-link>
                @elseif(auth()->user()->isSupport())
                    <x-nav-link :href="route('support.dashboard')" :active="request()->routeIs('support.dashboard')">
                        <i class="fas fa-headset mr-1.5"></i> Панель поддержки
                    </x-nav-link>
                @else
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        <i class="fas fa-home mr-1.5"></i> Личный кабинет
                    </x-nav-link>
                @endif
            </div>

            <!-- Right Side: Role Badge + User Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:space-x-4">
                <!-- Role Badge -->
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    @if(auth()->user()->isAdmin()) gradient-gold text-dark
                    @elseif(auth()->user()->isSupport()) bg-primary/10 text-primary
                    @else bg-cream-dark text-warm-gray border border-gold/20
                    @endif">
                    {{ auth()->user()->role->label() }}
                </span>

                <!-- User Dropdown -->
                <x-dropdown align="right" width="56">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center space-x-2 px-3 py-2 text-sm font-medium text-dark hover:text-primary focus:outline-none transition duration-150">
                            <div class="w-7 h-7 gradient-gold rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-white text-xs"></i>
                            </div>
                            <span>{{ Auth::user()->first_name }}</span>
                            <i class="fas fa-chevron-down text-xs text-warm-gray"></i>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            <i class="fas fa-user-circle mr-2 text-warm-gray"></i> Мой профиль
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();"
                                    class="text-red-600 hover:bg-red-50">
                                <i class="fas fa-sign-out-alt mr-2"></i> Выйти
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Mobile Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = !open" class="inline-flex items-center justify-center p-2 rounded-md text-warm-gray hover:text-primary hover:bg-cream-dark focus:outline-none transition duration-150">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': !open}" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': !open, 'inline-flex': open}" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div :class="{'block': open, 'hidden': !open}" class="hidden sm:hidden bg-cream-dark border-t border-gold/20">
        <div class="pt-2 pb-3 space-y-1">
            @if(auth()->user()->isAdmin())
                <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                    <i class="fas fa-tachometer-alt mr-2"></i> Панель администратора
                </x-responsive-nav-link>
            @elseif(auth()->user()->isSupport())
                <x-responsive-nav-link :href="route('support.dashboard')" :active="request()->routeIs('support.dashboard')">
                    <i class="fas fa-headset mr-2"></i> Панель поддержки
                </x-responsive-nav-link>
            @else
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    <i class="fas fa-home mr-2"></i> Личный кабинет
                </x-responsive-nav-link>
            @endif
        </div>

        <div class="pt-4 pb-1 border-t border-gold/20">
            <div class="px-4 mb-3">
                <div class="font-medium text-base text-dark">{{ Auth::user()->full_name }}</div>
                <div class="font-medium text-sm text-warm-gray">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-1 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    <i class="fas fa-user-circle mr-2"></i> Мой профиль
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                        <i class="fas fa-sign-out-alt mr-2 text-red-500"></i> Выйти
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
