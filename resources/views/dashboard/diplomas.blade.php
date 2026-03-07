<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark">Мои награды</h2>
                <p class="text-warm-gray mt-1">Дипломы и сертификаты по итогам конкурсов</p>
            </div>
            <a href="{{ route('contests.index') }}" class="self-start sm:self-auto px-5 py-2.5 border border-primary/20 text-primary text-sm font-medium rounded-lg hover:bg-primary/5 transition-colors whitespace-nowrap">
                <i class="fas fa-search mr-2"></i>Найти конкурсы
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if($diplomas->count())
                <div class="bg-white rounded-xl shadow-lg border border-gold/10 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm min-w-[600px]">
                            <thead>
                                <tr class="border-b border-gold/10 text-warm-gray text-xs uppercase tracking-wider">
                                    <th class="text-left px-6 py-3 font-semibold">Конкурс</th>
                                    <th class="text-left px-6 py-3 font-semibold hidden md:table-cell">Участник</th>
                                    <th class="text-left px-6 py-3 font-semibold hidden lg:table-cell">Номинация</th>
                                    <th class="text-left px-6 py-3 font-semibold">Результат</th>
                                    <th class="text-left px-6 py-3 font-semibold hidden md:table-cell">Дата</th>
                                    <th class="text-left px-6 py-3 font-semibold">Диплом</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gold/10">
                                @foreach($diplomas as $diploma)
                                    <tr class="hover:bg-cream/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <a href="{{ route('contests.show', $diploma->contest) }}"
                                                class="font-semibold text-dark hover:text-primary transition-colors block">
                                                {{ $diploma->contest->title }}
                                            </a>
                                            <p class="text-xs text-warm-gray mt-0.5">{{ $diploma->contest->organization->name }}</p>
                                        </td>
                                        <td class="px-6 py-4 hidden md:table-cell">
                                            <div class="flex items-center gap-2">
                                                <x-user-avatar :user="$diploma->user" size="xs" />
                                                <span class="text-warm-gray text-sm">{{ $diploma->user->full_name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 hidden lg:table-cell text-warm-gray text-sm">
                                            {{ $diploma->application->category?->name ?? '—' }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center text-xs font-medium px-2.5 py-1 rounded-full {{ $diploma->application->status->color() }}">
                                                {{ $diploma->application->status->label() }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 hidden md:table-cell text-warm-gray text-sm">
                                            {{ $diploma->created_at->format('d.m.Y') }}
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($diploma->file_path)
                                                <a href="{{ route('diplomas.download', $diploma) }}"
                                                    class="inline-flex items-center text-xs font-medium px-3 py-1.5 gradient-gold text-dark rounded-lg hover:opacity-90 transition">
                                                    <i class="fas fa-download mr-1.5"></i>Скачать
                                                </a>
                                            @else
                                                <span class="text-warm-gray text-xs">Формируется...</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-6">{{ $diplomas->links() }}</div>

            @else
                <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                    <div class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-award text-3xl text-primary"></i>
                    </div>
                    <h3 class="font-serif text-xl font-semibold text-dark mb-2">Дипломов пока нет</h3>
                    <p class="text-warm-gray mb-6">Принимайте участие в конкурсах — дипломы появятся здесь после подведения итогов.</p>
                    <a href="{{ route('contests.index') }}" class="inline-block px-6 py-3 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity">
                        <i class="fas fa-search mr-2"></i>Найти конкурсы
                    </a>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
