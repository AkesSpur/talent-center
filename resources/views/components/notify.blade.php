@php
    $notification = null;

    // Map session('status') keys to messages and types
    $statusMap = [
        // Organizations
        'organization-created'   => ['Организация успешно создана', 'success'],
        'organization-updated'   => ['Данные организации обновлены', 'success'],
        'organization-verified'  => ['Организация верифицирована', 'success'],
        'organization-blocked'   => ['Организация заблокирована', 'error'],
        'organization-unblocked' => ['Организация разблокирована', 'success'],
        // Representatives
        'representative-added'   => ['Представитель добавлен', 'success'],
        'representative-updated' => ['Права представителя обновлены', 'success'],
        'representative-removed' => ['Представитель удалён', 'success'],
        // Users
        'user-created'           => ['Пользователь успешно создан', 'success'],
        'user-updated'           => ['Данные пользователя обновлены', 'success'],
        // Participants
        'participant-added'      => ['Участник успешно добавлен', 'success'],
        'participant-updated'    => ['Данные участника обновлены', 'success'],
        'participant-deleted'    => ['Участник удалён', 'success'],
        // Profile
        'profile-updated'        => ['Профиль успешно обновлён', 'success'],
        'password-updated'       => ['Пароль успешно изменён', 'success'],
    ];

    if (session('status') && isset($statusMap[session('status')])) {
        $notification = [
            'message' => $statusMap[session('status')][0],
            'type'    => $statusMap[session('status')][1],
        ];
    } elseif (session('success')) {
        $notification = ['message' => session('success'), 'type' => 'success'];
    } elseif (session('error')) {
        $notification = ['message' => session('error'), 'type' => 'error'];
    } elseif (session('warning')) {
        $notification = ['message' => session('warning'), 'type' => 'warning'];
    }
@endphp

@if($notification)
    <div x-data="{
            show: false,
            progress: 100,
            interval: null,
            init() {
                this.$nextTick(() => this.show = true);
                const duration = 4000;
                const step = 50;
                this.interval = setInterval(() => {
                    this.progress -= (step / duration) * 100;
                    if (this.progress <= 0) {
                        clearInterval(this.interval);
                        this.dismiss();
                    }
                }, step);
            },
            dismiss() {
                this.show = false;
                if (this.interval) clearInterval(this.interval);
            }
         }"
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-x-8"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-x-0"
         x-transition:leave-end="opacity-0 translate-x-8"
         style="display: none;"
         class="fixed top-6 right-6 z-[60] w-full max-w-sm"
         @click.away="">

        <div class="bg-white rounded-xl shadow-2xl overflow-hidden border
            {{ $notification['type'] === 'error' ? 'border-red-200' : ($notification['type'] === 'warning' ? 'border-yellow-200' : 'border-green-200') }}">

            {{-- Progress bar --}}
            <div class="h-1 {{ $notification['type'] === 'error' ? 'bg-red-100' : ($notification['type'] === 'warning' ? 'bg-yellow-100' : 'bg-green-100') }}">
                <div class="h-full transition-all duration-50 ease-linear rounded-r
                    {{ $notification['type'] === 'error' ? 'bg-red-500' : ($notification['type'] === 'warning' ? 'bg-yellow-500' : 'bg-green-500') }}"
                    :style="'width: ' + progress + '%'">
                </div>
            </div>

            <div class="p-4 flex items-start gap-3">
                {{-- Icon --}}
                <div class="shrink-0 w-9 h-9 rounded-full flex items-center justify-center
                    {{ $notification['type'] === 'error' ? 'bg-red-100' : ($notification['type'] === 'warning' ? 'bg-yellow-100' : 'bg-green-100') }}">
                    @if($notification['type'] === 'error')
                        <i class="fas fa-times-circle text-red-600"></i>
                    @elseif($notification['type'] === 'warning')
                        <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                    @else
                        <i class="fas fa-check-circle text-green-600"></i>
                    @endif
                </div>

                {{-- Content --}}
                <div class="flex-1 min-w-0 pt-0.5">
                    <p class="text-sm font-semibold text-dark">
                        @if($notification['type'] === 'error')
                            Ошибка
                        @elseif($notification['type'] === 'warning')
                            Внимание
                        @else
                            Успешно
                        @endif
                    </p>
                    <p class="text-sm text-warm-gray mt-0.5">{{ $notification['message'] }}</p>
                </div>

                {{-- Close button --}}
                <button @click="dismiss()" class="shrink-0 w-7 h-7 flex items-center justify-center rounded-lg text-warm-gray hover:text-dark hover:bg-cream transition-colors">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
        </div>
    </div>
@endif
