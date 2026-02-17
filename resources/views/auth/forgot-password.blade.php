<x-guest-layout>
    <h2 class="font-serif text-2xl font-bold text-dark text-center mb-6">Сброс пароля</h2>

    <div class="mb-4 text-sm text-warm-gray">
        Забыли пароль? Не беда — введите ваш адрес электронной почты, и мы пришлём ссылку для сброса пароля.
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email -->
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                Отправить ссылку для сброса
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
