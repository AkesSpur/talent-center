<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="min-w-0">
                <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark">Представители</h2>
                <p class="text-warm-gray mt-1 truncate">{{ $organization->name }}</p>
            </div>
            <a href="{{ route('organizations.show', $organization) }}" class="self-start sm:self-auto px-4 py-2 text-warm-gray hover:text-primary transition-colors text-sm shrink-0">
                <i class="fas fa-arrow-left mr-2"></i>К организации
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Add Representative --}}
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="font-serif text-xl font-semibold text-dark mb-4">Добавить представителя</h3>
                <form method="POST" action="{{ route('organizations.representatives.store', $organization) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-dark mb-2">Email пользователя <span class="text-red-500">*</span></label>
                        <input name="email" type="email" value="{{ old('email') }}" required
                            class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                            placeholder="user@example.com" />
                        <p class="text-xs text-warm-gray mt-1">Пользователь должен быть зарегистрирован в системе</p>
                    </div>
                    <div class="flex flex-wrap gap-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="hidden" name="can_create" value="0">
                            <input type="checkbox" name="can_create" value="1" {{ old('can_create') ? 'checked' : '' }}
                                class="rounded border-primary/30 text-primary focus:ring-primary/30" />
                            <span class="text-sm text-dark"><i class="fas fa-plus-circle text-blue-600 mr-1"></i> Создание конкурсов</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="hidden" name="can_manage" value="0">
                            <input type="checkbox" name="can_manage" value="1" {{ old('can_manage') ? 'checked' : '' }}
                                class="rounded border-primary/30 text-primary focus:ring-primary/30" />
                            <span class="text-sm text-dark"><i class="fas fa-cog text-purple-600 mr-1"></i> Управление организацией</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="hidden" name="can_evaluate" value="0">
                            <input type="checkbox" name="can_evaluate" value="1" {{ old('can_evaluate') ? 'checked' : '' }}
                                class="rounded border-primary/30 text-primary focus:ring-primary/30" />
                            <span class="text-sm text-dark"><i class="fas fa-star text-orange-600 mr-1"></i> Оценка заявок</span>
                        </label>
                    </div>
                    <button type="submit" class="px-5 py-2.5 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity">
                        <i class="fas fa-plus mr-2"></i>Добавить
                    </button>
                </form>
            </div>

            {{-- Representatives List --}}
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="font-serif text-xl font-semibold text-dark mb-4">
                    Текущие представители
                    <span class="text-sm font-normal text-warm-gray ml-2">({{ $organization->representatives->count() }})</span>
                </h3>

                <div class="space-y-3">
                    @foreach($organization->representatives as $rep)
                        <div class="p-4 border border-primary/10 rounded-lg hover:bg-cream/50 transition-colors">
                            {{-- Top row: user info + delete --}}
                            <div class="flex items-center gap-3 mb-3">
                                <x-user-avatar :user="$rep" size="md" />
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-dark truncate">{{ $rep->full_name }}</p>
                                    <p class="text-sm text-warm-gray truncate">{{ $rep->email }}</p>
                                </div>
                                {{-- Crown or delete --}}
                                @if($organization->created_by !== $rep->id)
                                    <form method="POST" action="{{ route('organizations.representatives.destroy', [$organization, $rep]) }}"
                                        onsubmit="return confirm('Удалить {{ addslashes($rep->full_name) }} из представителей?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors shrink-0" title="Удалить">
                                            <i class="fas fa-trash text-sm"></i>
                                        </button>
                                    </form>
                                @else
                                    <span class="p-2 shrink-0" title="Создатель организации не может быть удалён">
                                        <i class="fas fa-crown text-gold"></i>
                                    </span>
                                @endif
                            </div>

                            {{-- Permissions form --}}
                            <form method="POST" action="{{ route('organizations.representatives.update', [$organization, $rep]) }}"
                                class="flex flex-wrap items-center gap-2">
                                @csrf
                                @method('PUT')
                                <label class="flex items-center gap-1.5 cursor-pointer text-xs">
                                    <input type="hidden" name="can_create" value="0">
                                    <input type="checkbox" name="can_create" value="1" {{ $rep->pivot->can_create ? 'checked' : '' }}
                                        class="rounded border-primary/30 text-primary focus:ring-primary/30 w-4 h-4 shrink-0" />
                                    <span class="text-blue-700 bg-blue-100 px-1.5 py-0.5 rounded whitespace-nowrap"><i class="fas fa-plus-circle mr-0.5"></i> Создание</span>
                                </label>
                                <label class="flex items-center gap-1.5 cursor-pointer text-xs">
                                    <input type="hidden" name="can_manage" value="0">
                                    <input type="checkbox" name="can_manage" value="1" {{ $rep->pivot->can_manage ? 'checked' : '' }}
                                        class="rounded border-primary/30 text-primary focus:ring-primary/30 w-4 h-4 shrink-0" />
                                    <span class="text-purple-700 bg-purple-100 px-1.5 py-0.5 rounded whitespace-nowrap"><i class="fas fa-cog mr-0.5"></i> Управление</span>
                                </label>
                                <label class="flex items-center gap-1.5 cursor-pointer text-xs">
                                    <input type="hidden" name="can_evaluate" value="0">
                                    <input type="checkbox" name="can_evaluate" value="1" {{ $rep->pivot->can_evaluate ? 'checked' : '' }}
                                        class="rounded border-primary/30 text-primary focus:ring-primary/30 w-4 h-4 shrink-0" />
                                    <span class="text-orange-700 bg-orange-100 px-1.5 py-0.5 rounded whitespace-nowrap"><i class="fas fa-star mr-0.5"></i> Оценка</span>
                                </label>
                                <button type="submit" class="px-3 py-1 text-xs text-primary border border-primary/30 hover:bg-primary/5 rounded-lg transition-colors ml-auto" title="Сохранить права">
                                    <i class="fas fa-save mr-1"></i>Сохранить
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
