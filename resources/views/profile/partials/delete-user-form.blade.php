<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-dark">
            Удаление аккаунта
        </h2>

        <p class="mt-1 text-sm text-warm-gray">
            После удаления аккаунта все данные будут безвозвратно удалены. Перед удалением сохраните все важные данные.
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >Удалить аккаунт</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-dark">
                Вы уверены, что хотите удалить аккаунт?
            </h2>

            <p class="mt-1 text-sm text-warm-gray">
                После удаления все данные будут безвозвратно утеряны. Введите ваш пароль для подтверждения.
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="Пароль" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="Пароль"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Отмена
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    Удалить аккаунт
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
