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
            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('admin.organizations.edit', $organization) }}" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors text-sm">
                    <i class="fas fa-pen mr-2"></i>Редактировать
                </a>
                <a href="{{ route('admin.organizations.index') }}" class="px-4 py-2 text-warm-gray hover:text-primary transition-colors text-sm">
                    <i class="fas fa-arrow-left mr-2"></i>К списку
                </a>
            </div>
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

    {{-- Confirm Block Modal --}}
    <x-confirm-modal
        name="block-org"
        title="{{ $organization->isBlocked() ? 'Разблокировать организацию' : 'Заблокировать организацию' }}"
        message="{{ $organization->isBlocked()
            ? 'Вы уверены, что хотите разблокировать организацию «' . $organization->name . '»? Организация снова сможет работать в системе.'
            : 'Вы уверены, что хотите заблокировать организацию «' . $organization->name . '»? Организация не сможет создавать конкурсы и принимать заявки.' }}"
        icon="{{ $organization->isBlocked() ? 'fa-unlock' : 'fa-ban' }}"
        iconColor="{{ $organization->isBlocked() ? 'text-green-600' : 'text-red-600' }}"
        iconBg="{{ $organization->isBlocked() ? 'bg-green-100' : 'bg-red-100' }}"
        confirmText="{{ $organization->isBlocked() ? 'Разблокировать' : 'Заблокировать' }}"
        confirmClass="{{ $organization->isBlocked() ? 'gradient-gold text-dark' : 'bg-red-600 text-white' }}"
    />

    <div x-data class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- Left: Org Info --}}
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

                    {{-- Representatives with management --}}
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h3 class="font-serif text-xl font-semibold text-dark mb-4">
                            Представители
                            <span class="text-sm font-normal text-warm-gray ml-2">({{ $organization->representatives->count() }})</span>
                        </h3>

                        <div class="space-y-3 mb-6">
                            @foreach($organization->representatives as $rep)
                                <div class="p-3 rounded-lg border border-primary/10 hover:bg-cream/50 transition-colors">
                                    {{-- User row --}}
                                    <div class="flex items-center gap-3 mb-2">
                                        <x-user-avatar :user="$rep" size="sm" />
                                        <div class="flex-1 min-w-0">
                                            <a href="{{ route('admin.users.show', $rep) }}" class="font-medium text-primary hover:underline text-sm truncate block">{{ $rep->full_name }}</a>
                                            <p class="text-xs text-warm-gray truncate">{{ $rep->email }}</p>
                                        </div>
                                        @if($organization->created_by !== $rep->id)
                                            <button type="button"
                                                @click="$dispatch('confirm-delete-rep', { action: '{{ route('admin.organizations.representatives.destroy', [$organization, $rep]) }}' })"
                                                class="p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors shrink-0" title="Удалить">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        @else
                                            <span class="p-1.5 shrink-0" title="Создатель организации">
                                                <i class="fas fa-crown text-gold text-sm"></i>
                                            </span>
                                        @endif
                                    </div>
                                    {{-- Permissions form --}}
                                    <form method="POST" action="{{ route('admin.organizations.representatives.update', [$organization, $rep]) }}"
                                        class="flex flex-wrap items-center gap-2">
                                        @csrf
                                        @method('PUT')
                                        <label class="flex items-center gap-1 cursor-pointer text-xs" title="Создание конкурсов">
                                            <input type="hidden" name="can_create" value="0">
                                            <input type="checkbox" name="can_create" value="1" {{ $rep->pivot->can_create ? 'checked' : '' }}
                                                class="rounded border-primary/30 text-blue-600 focus:ring-blue-500 w-3.5 h-3.5 shrink-0" />
                                            <span class="text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded whitespace-nowrap"><i class="fas fa-plus-circle mr-0.5"></i> Создание</span>
                                        </label>
                                        <label class="flex items-center gap-1 cursor-pointer text-xs" title="Управление организацией">
                                            <input type="hidden" name="can_manage" value="0">
                                            <input type="checkbox" name="can_manage" value="1" {{ $rep->pivot->can_manage ? 'checked' : '' }}
                                                class="rounded border-primary/30 text-purple-600 focus:ring-purple-500 w-3.5 h-3.5 shrink-0" />
                                            <span class="text-purple-700 bg-purple-50 px-1.5 py-0.5 rounded whitespace-nowrap"><i class="fas fa-cog mr-0.5"></i> Управление</span>
                                        </label>
                                        <label class="flex items-center gap-1 cursor-pointer text-xs" title="Оценка заявок">
                                            <input type="hidden" name="can_evaluate" value="0">
                                            <input type="checkbox" name="can_evaluate" value="1" {{ $rep->pivot->can_evaluate ? 'checked' : '' }}
                                                class="rounded border-primary/30 text-orange-600 focus:ring-orange-500 w-3.5 h-3.5 shrink-0" />
                                            <span class="text-orange-700 bg-orange-50 px-1.5 py-0.5 rounded whitespace-nowrap"><i class="fas fa-star mr-0.5"></i> Оценка</span>
                                        </label>
                                        <button type="submit" class="ml-auto px-2.5 py-0.5 text-xs text-primary border border-primary/30 hover:bg-primary/5 rounded-lg transition-colors whitespace-nowrap" title="Сохранить права">
                                            <i class="fas fa-save mr-1"></i>Сохранить
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>

                        {{-- Add Representative --}}
                        <div class="border-t border-primary/10 pt-4">
                            <h4 class="font-medium text-dark mb-3"><i class="fas fa-user-plus text-primary mr-2"></i>Добавить представителя</h4>
                            <form method="POST" action="{{ route('admin.organizations.representatives.store', $organization) }}" class="space-y-3">
                                @csrf
                                <div class="flex gap-3">
                                    <input name="email" type="email" placeholder="Email пользователя" required
                                        class="flex-1 px-4 py-2.5 border border-primary/20 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                                </div>
                                <div class="flex flex-wrap gap-4">
                                    <label class="flex items-center gap-2 cursor-pointer text-sm">
                                        <input type="hidden" name="can_create" value="0">
                                        <input type="checkbox" name="can_create" value="1"
                                            class="rounded border-primary/30 text-blue-600 focus:ring-blue-500" />
                                        <span><i class="fas fa-plus-circle text-blue-600 mr-1"></i> Создание конкурсов</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer text-sm">
                                        <input type="hidden" name="can_manage" value="0">
                                        <input type="checkbox" name="can_manage" value="1"
                                            class="rounded border-primary/30 text-purple-600 focus:ring-purple-500" />
                                        <span><i class="fas fa-cog text-purple-600 mr-1"></i> Управление организацией</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer text-sm">
                                        <input type="hidden" name="can_evaluate" value="0">
                                        <input type="checkbox" name="can_evaluate" value="1"
                                            class="rounded border-primary/30 text-orange-600 focus:ring-orange-500" />
                                        <span><i class="fas fa-star text-orange-600 mr-1"></i> Оценка заявок</span>
                                    </label>
                                </div>
                                <button type="submit" class="px-5 py-2 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity text-sm">
                                    <i class="fas fa-plus mr-2"></i>Добавить
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Right: Status & Actions --}}
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
                                    <a href="{{ route('admin.users.show', $organization->createdBy) }}" class="text-primary hover:underline">{{ $organization->createdBy->full_name }}</a>
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

                    {{-- Action Buttons --}}
                    <div class="space-y-3">
                        @if(!$organization->isVerified() && !$organization->isBlocked())
                            <button type="button"
                                @click="$dispatch('confirm-verify-org', { action: '{{ route('admin.organizations.verify', $organization) }}' })"
                                class="w-full py-3 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity text-sm">
                                <i class="fas fa-check-circle mr-2"></i>Верифицировать
                            </button>
                        @endif

                        <button type="button"
                            @click="$dispatch('confirm-block-org', { action: '{{ route('admin.organizations.toggle-block', $organization) }}' })"
                            class="w-full py-3 {{ $organization->isBlocked() ? 'bg-green-600 hover:bg-green-700 text-white' : 'bg-red-600 hover:bg-red-700 text-white' }} font-semibold rounded-lg transition-colors text-sm">
                            @if($organization->isBlocked())
                                <i class="fas fa-unlock mr-2"></i>Разблокировать
                            @else
                                <i class="fas fa-ban mr-2"></i>Заблокировать
                            @endif
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
