<x-guest-layout>
    <h2 class="font-serif text-2xl font-bold text-dark text-center mb-6">Подтверждение email</h2>

    <div class="mb-4 text-sm text-warm-gray">
        Спасибо за регистрацию! Перед тем как начать, подтвердите свой адрес электронной почты, перейдя по ссылке из письма, которое мы вам отправили. Если письмо не пришло, мы можем отправить его повторно.
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600">
            Новая ссылка для подтверждения отправлена на ваш адрес электронной почты.
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <div>
                <x-primary-button>
                    Отправить письмо повторно
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="underline text-sm text-warm-gray hover:text-primary rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                Выйти
            </button>
        </form>
    </div>
</x-guest-layout>
