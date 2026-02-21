<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <x-org-avatar :organization="$organization" size="md" class="shrink-0" />
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark break-words">{{ $organization->name }}</h2>
                        @if($organization->isBlocked())
                            <span class="text-xs bg-red-100 text-red-700 px-2.5 py-1 rounded-full font-medium shrink-0">
                                <i class="fas fa-ban mr-1"></i>Заблокирована
                            </span>
                        @elseif($organization->isVerified())
                            <span class="text-xs bg-green-100 text-green-700 px-2.5 py-1 rounded-full font-medium shrink-0">
                                <i class="fas fa-check-circle mr-1"></i>Верифицирована
                            </span>
                        @else
                            <span class="text-xs bg-yellow-100 text-yellow-700 px-2.5 py-1 rounded-full font-medium shrink-0">
                                <i class="fas fa-clock mr-1"></i>На проверке
                            </span>
                        @endif
                    </div>
                    <p class="text-warm-gray text-sm">ИНН: {{ $organization->inn }}</p>
                </div>
            </div>
            <a href="{{ route('support.organizations.index') }}" class="self-start sm:self-auto px-4 py-2 text-warm-gray hover:text-primary transition-colors text-sm shrink-0">
                <i class="fas fa-arrow-left mr-2"></i>К списку
            </a>
        </div>
    </x-slot>

    {{-- Confirm Verify Modal --}}
    <x-confirm-modal
        name="verify-org"
        title="Верифицировать организацию"
        message="Вы уверены, что хотите верифицировать организацию «{{ $organization->name }}»? После верификации организация сможет создавать конкурсы."
        icon="fa-check-circle"
        iconColor="text-green-600"
        iconBg="bg-green-100"
        confirmText="Верифицировать"
    />

    {{-- Confirm Remove Representative Modal --}}
    <x-confirm-modal
        name="delete-rep"
        title="Удалить представителя"
        message="Вы уверены, что хотите удалить этого представителя из организации?"
        icon="fa-user-minus"
        iconColor="text-red-600"
        iconBg="bg-red-100"
        confirmText="Удалить"
        confirmClass="bg-red-600 text-white hover:bg-red-700"
        method="DELETE"
    />

    <div x-data class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if($organization->isBlocked())
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700">
                    <i class="fas fa-ban mr-2"></i>Эта организация заблокирована администратором. Создание конкурсов и приём заявок недоступны.
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h3 class="font-serif text-xl font-semibold text-dark mb-4">Информация</h3>
                        <div class="space-y-4">
                            @if($organization->description)
                                <p class="text-dark">{{ $organization->description }}</p>
                            @endif
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-warm-gray">ИНН:</span>
                                    <span class="text-dark ml-2">{{ $organization->inn }}</span>
                                </div>
                                @if($organization->ogrn)
                                    <div>
                                        <span class="text-warm-gray">ОГРН:</span>
                                        <span class="text-dark ml-2">{{ $organization->ogrn }}</span>
                                    </div>
                                @endif
                                <div>
                                    <span class="text-warm-gray">Контактный email:</span>
                                    <span class="text-dark ml-2">{{ $organization->contact_email }}</span>
                                </div>
                                @if($organization->contact_phone)
                                    <div>
                                        <span class="text-warm-gray">Телефон:</span>
                                        <span class="text-dark ml-2">{{ $organization->contact_phone }}</span>
                                    </div>
                                @endif
                                @if($organization->website)
                                    <div>
                                        <span class="text-warm-gray">Веб-сайт:</span>
                                        <a href="{{ $organization->website }}" target="_blank" rel="noopener" class="text-primary hover:underline ml-2 break-all">{{ $organization->website }}</a>
                                    </div>
                                @endif
                                @if($organization->legal_address)
                                    <div class="md:col-span-2">
                                        <span class="text-warm-gray">Юридический адрес:</span>
                                        <span class="text-dark ml-2">{{ $organization->legal_address }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h3 class="font-serif text-xl font-semibold text-dark mb-4">Представители</h3>
                        <div class="space-y-2">
                            @foreach($organization->representatives as $rep)
                                <div class="p-3 rounded-lg hover:bg-cream/50">
                                    <div class="flex items-center gap-3 mb-1.5">
                                        <x-user-avatar :user="$rep" size="sm" />
                                        <div class="min-w-0 flex-1">
                                            <a href="{{ route('support.users.show', $rep) }}" class="font-medium text-primary hover:underline text-sm truncate block">{{ $rep->full_name }}</a>
                                            <p class="text-xs text-warm-gray truncate">{{ $rep->email }}</p>
                                        </div>
                                        @if($organization->created_by !== $rep->id)
                                            <button type="button"
                                                @click="$dispatch('confirm-delete-rep', { action: '{{ route('support.organizations.representatives.destroy', [$organization, $rep]) }}' })"
                                                class="p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors shrink-0" title="Удалить">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        @else
                                            <span class="p-1.5 shrink-0" title="Создатель организации">
                                                <i class="fas fa-crown text-gold text-sm"></i>
                                            </span>
                                        @endif
                                    </div>
                                    @if($rep->pivot->can_create || $rep->pivot->can_manage || $rep->pivot->can_evaluate)
                                        <div class="flex flex-wrap gap-1.5 pl-9">
                                            @if($rep->pivot->can_create)
                                                <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full whitespace-nowrap">Создание</span>
                                            @endif
                                            @if($rep->pivot->can_manage)
                                                <span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full whitespace-nowrap">Управление</span>
                                            @endif
                                            @if($rep->pivot->can_evaluate)
                                                <span class="text-xs bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full whitespace-nowrap">Оценка</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div x-data class="space-y-6">
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h3 class="font-semibold text-dark mb-4">Статус</h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-warm-gray">Статус:</span>
                                @if($organization->isBlocked())
                                    <span class="text-red-600 font-medium">Заблокирована</span>
                                @elseif($organization->isVerified())
                                    <span class="text-green-600 font-medium">Верифицирована</span>
                                @else
                                    <span class="text-yellow-600 font-medium">Ожидает проверки</span>
                                @endif
                            </div>
                            <div class="flex justify-between">
                                <span class="text-warm-gray">Создана:</span>
                                <span class="text-dark">{{ $organization->created_at->format('d.m.Y') }}</span>
                            </div>
                            @if($organization->createdBy)
                                <div class="flex justify-between">
                                    <span class="text-warm-gray">Создатель:</span>
                                    <a href="{{ route('support.users.show', $organization->createdBy) }}" class="text-primary hover:underline">{{ $organization->createdBy->full_name }}</a>
                                </div>
                            @endif
                            @if($organization->isVerified() && $organization->verifiedBy)
                                <div class="flex justify-between">
                                    <span class="text-warm-gray">Верифицировал:</span>
                                    <span class="text-dark">{{ $organization->verifiedBy->full_name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-warm-gray">Дата верификации:</span>
                                    <span class="text-dark">{{ $organization->verified_at->format('d.m.Y') }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between">
                                <span class="text-warm-gray">Представителей:</span>
                                <span class="font-semibold text-dark">{{ $organization->representatives->count() }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-warm-gray">Конкурсов:</span>
                                <span class="font-semibold text-dark">{{ $organization->contests()->count() }}</span>
                            </div>
                        </div>
                    </div>

                    @if(!$organization->isVerified() && !$organization->isBlocked())
                        <button type="button"
                            @click="$dispatch('confirm-verify-org', { action: '{{ route('support.organizations.verify', $organization) }}' })"
                            class="w-full py-3 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity text-sm">
                            <i class="fas fa-check-circle mr-2"></i>Верифицировать
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
