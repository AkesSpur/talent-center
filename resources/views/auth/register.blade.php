<x-guest-layout>
    <h2 class="font-serif text-2xl font-bold text-dark text-center mb-6">Создать аккаунт</h2>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Фамилия -->
        <div>
            <x-input-label for="last_name" value="Фамилия" />
            <x-text-input id="last_name" class="block mt-1 w-full" type="text" name="last_name" :value="old('last_name')" required autofocus autocomplete="family-name" />
            <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
        </div>

        <!-- Имя -->
        <div class="mt-4">
            <x-input-label for="first_name" value="Имя" />
            <x-text-input id="first_name" class="block mt-1 w-full" type="text" name="first_name" :value="old('first_name')" required autocomplete="given-name" />
            <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
        </div>

        <!-- Отчество -->
        <div class="mt-4">
            <x-input-label for="patronymic" value="Отчество (необязательно)" />
            <x-text-input id="patronymic" class="block mt-1 w-full" type="text" name="patronymic" :value="old('patronymic')" autocomplete="additional-name" />
            <x-input-error :messages="$errors->get('patronymic')" class="mt-2" />
        </div>

        <!-- Email -->
        <div class="mt-4">
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Пароль -->
        <div class="mt-4">
            <x-input-label for="password" value="Пароль" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Подтвердите пароль -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" value="Подтвердите пароль" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-5" x-data="{ agreed: false }">
            <label class="flex items-start gap-3 cursor-pointer select-none">
                <input type="checkbox" x-model="agreed"
                    class="mt-0.5 rounded border-primary/30 text-primary focus:ring-primary/30 w-4 h-4 shrink-0">
                <span class="text-sm text-warm-gray leading-snug">
                    Даю согласие на обработку персональных данных в соответствии с
                    <a href="{{ route('privacy-policy') }}" target="_blank" rel="noopener noreferrer"
                       class="text-primary underline hover:opacity-80">политикой конфиденциальности</a>
                </span>
            </label>

            <div class="mt-4">
                <x-primary-button class="w-full justify-center" x-bind:disabled="!agreed"
                    x-bind:class="!agreed ? 'opacity-50 cursor-not-allowed' : ''">
                    Зарегистрироваться
                </x-primary-button>
            </div>
        </div>

        <div class="mt-4 text-center">
            <a class="underline text-sm text-warm-gray hover:text-primary rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary" href="{{ route('login') }}">
                Уже зарегистрированы?
            </a>
        </div>
    </form>
</x-guest-layout>
