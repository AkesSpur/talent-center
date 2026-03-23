<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark">Мои конкурсы</h2>
                <p class="text-warm-gray mt-1">Конкурсы, созданные вами</p>
            </div>
            <a href="{{ route('contests.create') }}"
                class="inline-flex items-center px-5 py-2.5 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity text-sm">
                <i class="fas fa-plus mr-2"></i>Создать конкурс
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if($contests->count())
                <div class="bg-white rounded-xl shadow-sm border border-gold/10 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm min-w-[600px]">
                            <thead>
                                <tr class="border-b border-gold/10 text-warm-gray text-xs uppercase tracking-wider">
                                    <th class="text-left px-6 py-3 font-semibold">Конкурс</th>
                                    <th class="text-left px-6 py-3 font-semibold">Статус</th>
                                    <th class="text-left px-6 py-3 font-semibold hidden lg:table-cell">Приём заявок</th>
                                    <th class="text-center px-6 py-3 font-semibold hidden sm:table-cell">Заявки</th>
                                    <th class="text-right px-6 py-3 font-semibold">Действия</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gold/10">
                                @foreach($contests as $contest)
                                    <tr class="hover:bg-cream/30 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                @if($contest->cover_image)
                                                    <img src="{{ asset('storage/' . $contest->cover_image) }}"
                                                        class="w-12 h-9 object-cover rounded-lg shrink-0" alt="">
                                                @else
                                                    <div class="w-12 h-9 bg-primary/10 rounded-lg flex items-center justify-center shrink-0">
                                                        <i class="fas fa-trophy text-primary text-sm"></i>
                                                    </div>
                                                @endif
                                                <div class="min-w-0">
                                                    <a href="{{ route('contests.show', $contest) }}"
                                                        class="font-medium text-dark hover:text-primary transition-colors block break-words">
                                                        {{ $contest->title }}
                                                    </a>
                                                    @if($contest->platformCategory)
                                                        <span class="text-xs text-warm-gray">{{ $contest->platformCategory->name }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center text-xs font-medium px-2.5 py-1 rounded-full {{ $contest->status->color() }}">
                                                {{ $contest->status->label() }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 hidden lg:table-cell text-warm-gray text-sm">
                                            {{ $contest->applications_start_at->format('d.m.Y') }} — {{ $contest->applications_end_at->format('d.m.Y') }}
                                        </td>
                                        <td class="px-6 py-4 hidden sm:table-cell text-center">
                                            <a href="{{ route('organizations.applications', $contest->organization) }}"
                                                class="inline-flex items-center gap-1.5 text-sm font-semibold text-dark hover:text-primary transition-colors">
                                                <i class="fas fa-file-alt text-warm-gray text-xs"></i>
                                                {{ $contest->applications_count }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                @if($contest->isEvaluation() && auth()->user()->canInOrg('evaluate', $contest->organization))
                                                    <a href="{{ route('evaluation.show', [$contest->organization, $contest]) }}"
                                                        title="Оценить заявки"
                                                        class="w-8 h-8 flex items-center justify-center border border-gold/30 text-gold rounded-lg hover:bg-gold/10 transition-colors">
                                                        <i class="fas fa-star text-xs"></i>
                                                    </a>
                                                @endif
                                                @if(auth()->user()->isAdmin())
                                                    <a href="{{ route('admin.evaluation.show', $contest) }}"
                                                        title="Управление оценкой (Админ)"
                                                        class="w-8 h-8 flex items-center justify-center border border-primary/30 text-primary rounded-lg hover:bg-primary/10 transition-colors">
                                                        <i class="fas fa-sliders-h text-xs"></i>
                                                    </a>
                                                @endif
                                                <a href="{{ route('organizations.applications', $contest->organization) }}"
                                                    title="Заявки"
                                                    class="w-8 h-8 flex items-center justify-center border border-primary/20 text-primary rounded-lg hover:bg-primary/5 transition-colors">
                                                    <i class="fas fa-inbox text-xs"></i>
                                                </a>
                                                @can('update', $contest)
                                                    <a href="{{ route('contests.edit', $contest) }}"
                                                        title="Изменить"
                                                        class="w-8 h-8 flex items-center justify-center border border-primary/20 text-primary rounded-lg hover:bg-primary/5 transition-colors">
                                                        <i class="fas fa-pen text-xs"></i>
                                                    </a>
                                                @endcan
                                                <a href="{{ route('contests.show', $contest) }}"
                                                    title="Просмотр"
                                                    class="w-8 h-8 flex items-center justify-center border border-gold/20 text-warm-gray rounded-lg hover:bg-cream/50 transition-colors">
                                                    <i class="fas fa-eye text-xs"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-4">{{ $contests->links() }}</div>

            @else
                <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                    <div class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-trophy text-3xl text-primary"></i>
                    </div>
                    <h3 class="font-serif text-xl font-semibold text-dark mb-2">Конкурсов нет</h3>
                    <p class="text-warm-gray mb-6">Вы ещё не создавали конкурсы. Создайте первый!</p>
                    <a href="{{ route('contests.create') }}"
                        class="inline-flex items-center px-6 py-3 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity">
                        <i class="fas fa-plus mr-2"></i>Создать конкурс
                    </a>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
