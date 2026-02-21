<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <x-org-avatar :organization="$organization" size="md" class="shrink-0" />
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark break-words">{{ $organization->name }}</h2>
                        @if($organization->isVerified())
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
            <div class="flex gap-3 shrink-0">
                @can('update', $organization)
                    <a href="{{ route('organizations.edit', $organization) }}" class="px-4 py-2 border border-primary/30 text-primary font-medium rounded-lg hover:bg-primary/5 transition-colors text-sm">
                        <i class="fas fa-edit mr-2"></i>Редактировать
                    </a>
                @endcan
                <a href="{{ route('organizations.index') }}" class="px-4 py-2 text-warm-gray hover:text-primary transition-colors text-sm">
                    <i class="fas fa-arrow-left mr-2"></i>Назад
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            @if(session('status') === 'organization-updated')
                <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)"
                    class="bg-green-50 border border-green-200 rounded-lg p-4 text-green-700 mb-6">
                    <i class="fas fa-check-circle mr-2"></i>Данные организации обновлены
                </div>
            @endif

            @if(session('status') === 'representative-added')
                <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)"
                    class="bg-green-50 border border-green-200 rounded-lg p-4 text-green-700 mb-6">
                    <i class="fas fa-check-circle mr-2"></i>Представитель добавлен
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- Left: Org Info --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Description --}}
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

                    {{-- Representatives --}}
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-serif text-xl font-semibold text-dark">Представители</h3>
                            @if(auth()->user()->canInOrg('manage', $organization) || auth()->user()->isAdmin())
                                <a href="{{ route('organizations.representatives.index', $organization) }}"
                                    class="text-sm text-primary hover:underline">
                                    <i class="fas fa-cog mr-1"></i>Управление
                                </a>
                            @endif
                        </div>
                        <div class="space-y-2">
                            @foreach($organization->representatives as $rep)
                                <div class="p-3 rounded-lg hover:bg-cream/50">
                                    <div class="flex items-center gap-3 mb-1.5">
                                        <x-user-avatar :user="$rep" size="sm" />
                                        <div class="min-w-0">
                                            <p class="font-medium text-dark text-sm truncate">{{ $rep->full_name }}</p>
                                            <p class="text-xs text-warm-gray truncate">{{ $rep->email }}</p>
                                        </div>
                                    </div>
                                    @if($rep->pivot->can_create || $rep->pivot->can_manage || $rep->pivot->can_evaluate)
                                        <div class="flex flex-wrap gap-1.5 pl-9">
                                            @if($rep->pivot->can_create)
                                                <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full whitespace-nowrap">
                                                    <i class="fas fa-plus-circle mr-0.5"></i> Создание
                                                </span>
                                            @endif
                                            @if($rep->pivot->can_manage)
                                                <span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full whitespace-nowrap">
                                                    <i class="fas fa-cog mr-0.5"></i> Управление
                                                </span>
                                            @endif
                                            @if($rep->pivot->can_evaluate)
                                                <span class="text-xs bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full whitespace-nowrap">
                                                    <i class="fas fa-star mr-0.5"></i> Оценка
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Right: Status & Stats --}}
                <div class="space-y-6">
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h3 class="font-semibold text-dark mb-4">Статус</h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-warm-gray">Статус:</span>
                                @if($organization->isVerified())
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
                                    <span class="text-dark">{{ $organization->createdBy->full_name }}</span>
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
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h3 class="font-semibold text-dark mb-4">Статистика</h3>
                        <div class="space-y-3 text-sm">
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

                    @if(!$organization->isVerified())
                        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-sm text-yellow-700">
                            <i class="fas fa-info-circle mr-2"></i>
                            Организация ожидает верификации администратором. После подтверждения вы сможете создавать конкурсы.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
