<x-guest-layout>
    <h2 class="font-serif text-2xl font-bold text-dark text-center mb-6">Вход в систему</h2>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email -->
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Пароль -->
        <div class="mt-4">
            <x-input-label for="password" value="Пароль" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Запомнить меня -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-warm-gray/40 text-primary shadow-sm focus:ring-primary" name="remember">
                <span class="ms-2 text-sm text-warm-gray">Запомнить меня</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-warm-gray hover:text-primary rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary" href="{{ route('password.request') }}">
                    Забыли пароль?
                </a>
            @endif

            <x-primary-button class="ms-3">
                Войти
            </x-primary-button>
        </div>
    </form>

    <div class="mt-6 text-center">
        <p class="text-sm text-warm-gray">
            Нет аккаунта?
            <a href="{{ route('register') }}" class="text-primary hover:text-primary-dark font-medium">Зарегистрироваться</a>
        </p>
    </div>
</x-guest-layout>
