<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark">Журнал действий</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-lg p-6">

                {{-- Search --}}
                <form method="GET" action="{{ route('admin.action-logs.index') }}" class="flex flex-wrap gap-4 mb-6">
                    <div class="flex-1 min-w-[200px]">
                        <input name="search" type="text" value="{{ request('search') }}"
                            class="w-full px-4 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                            placeholder="Поиск по действию или пользователю..." />
                    </div>
                    <button type="submit" class="px-5 py-2.5 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity">
                        <i class="fas fa-search mr-2"></i>Найти
                    </button>
                    @if(request('search'))
                        <a href="{{ route('admin.action-logs.index') }}" class="px-5 py-2.5 border border-primary/30 text-primary rounded-lg hover:bg-primary/5 transition-colors">
                            Сбросить
                        </a>
                    @endif
                </form>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-primary/10">
                                <th class="text-left py-3 px-4 font-semibold text-dark">Время</th>
                                <th class="text-left py-3 px-4 font-semibold text-dark">Пользователь</th>
                                <th class="text-left py-3 px-4 font-semibold text-dark">Действие</th>
                                <th class="text-left py-3 px-4 font-semibold text-dark">Объект</th>
                                <th class="text-left py-3 px-4 font-semibold text-dark">IP</th>
                                <th class="text-left py-3 px-4 font-semibold text-dark">Данные</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-primary/10">
                            @forelse($logs as $log)
                                <tr class="hover:bg-cream/50 transition-colors">
                                    <td class="py-3 px-4 text-warm-gray whitespace-nowrap">{{ $log->created_at->format('d.m.Y H:i') }}</td>
                                    <td class="py-3 px-4">
                                        @if($log->user)
                                            <a href="{{ route('admin.users.show', $log->user) }}" class="text-primary hover:underline">{{ $log->user->full_name }}</a>
                                        @else
                                            <span class="text-warm-gray">—</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="bg-primary/10 text-primary px-2 py-0.5 rounded text-xs font-medium">{{ $log->action }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-warm-gray">
                                        @if($log->target_type)
                                            {{ class_basename($log->target_type) }} #{{ $log->target_id }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-warm-gray text-xs">{{ $log->ip_address }}</td>
                                    <td class="py-3 px-4">
                                        @if($log->metadata)
                                            <button onclick="this.nextElementSibling.classList.toggle('hidden')"
                                                class="text-xs text-primary hover:underline cursor-pointer">
                                                Показать
                                            </button>
                                            <pre class="hidden mt-1 text-xs bg-cream p-2 rounded max-w-xs overflow-x-auto">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        @else
                                            <span class="text-warm-gray">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-warm-gray">Записи не найдены</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
