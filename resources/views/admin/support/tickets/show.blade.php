@php use App\Enums\SupportTicketStatus; @endphp
<x-app-layout>
    <x-slot name="header">
        <x-breadcrumbs :items="[
            ['label' => 'Поддержка', 'url' => route('admin.support.tickets.index')],
            ['label' => 'Заявка ' . $ticket->number],
        ]" />
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
            <div class="min-w-0">
                <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark break-words">{{ $ticket->subject }}</h2>
                <p class="text-warm-gray mt-1">
                    {{ $ticket->number }} · создана {{ $ticket->created_at->timezone('Europe/Moscow')->format('d.m.Y, H:i') }} МСК
                </p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                @if($ticket->isOverdueNow())
                    <span class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-full bg-red-100 text-red-700">
                        <i class="fas fa-triangle-exclamation"></i>Просрочена
                    </span>
                @endif
                <span class="inline-flex items-center text-xs font-medium px-3 py-1.5 rounded-full {{ $ticket->status->color() }}">
                    {{ $ticket->status->label() }}
                </span>
            </div>
        </div>
    </x-slot>

    @if($ticket->assigned_to && $ticket->assigned_to !== auth()->id())
        <x-confirm-modal
            name="reassign-ticket"
            title="Переназначить заявку"
            message="{{ 'Заявка назначена на сотрудника «' . ($ticket->assignee?->full_name ?? '—') . '». Переназначить её на себя?' }}"
            icon="fa-hand"
            confirmText="Переназначить"
        />
    @endif

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-notify />

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">

                {{-- Conversation --}}
                <div class="lg:col-span-2 space-y-6" x-data="{ tab: '{{ request('tab', 'conversation') }}' }">
                    <div class="bg-white rounded-xl shadow-sm border border-gold/10 overflow-hidden">
                        <div class="flex overflow-x-auto border-b border-gold/10">
                            <button type="button" @click="tab = 'conversation'"
                                :class="tab === 'conversation' ? 'border-b-2 border-gold text-primary font-semibold' : 'text-warm-gray hover:text-dark'"
                                class="flex items-center gap-2 px-5 py-3.5 text-sm whitespace-nowrap transition-colors shrink-0">
                                <i class="fas fa-comments text-xs" aria-hidden="true"></i>Переписка
                            </button>
                            <button type="button" @click="tab = 'notifications'"
                                :class="tab === 'notifications' ? 'border-b-2 border-gold text-primary font-semibold' : 'text-warm-gray hover:text-dark'"
                                class="flex items-center gap-2 px-5 py-3.5 text-sm whitespace-nowrap transition-colors shrink-0">
                                <i class="fas fa-envelope text-xs" aria-hidden="true"></i>История уведомлений
                                @if($notificationLogs->isNotEmpty())
                                    <span class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full bg-primary/10 text-primary text-[11px] font-semibold">{{ $notificationLogs->count() }}</span>
                                @endif
                            </button>
                        </div>
                    </div>

                    <div x-show="tab === 'conversation'" x-cloak class="space-y-6">
                        {{-- The tab above already names this card. --}}
                        <div class="bg-white rounded-xl shadow-sm border border-gold/10 overflow-hidden">
                            <div class="divide-y divide-gold/10">
                                <article class="p-6">
                                    <div class="flex items-center gap-3 mb-3">
                                        <x-user-avatar :user="$ticket->user" size="sm" />
                                        <div>
                                            <p class="text-sm font-medium text-dark">{{ $ticket->user?->full_name ?? 'Гость' }}</p>
                                            <p class="text-xs text-warm-gray">{{ $ticket->created_at->timezone('Europe/Moscow')->format('d.m.Y, H:i') }}</p>
                                        </div>
                                    </div>
                                    <div class="text-sm text-dark whitespace-pre-line leading-relaxed">{{ $ticket->description }}</div>
                                    @include('support.tickets.partials.attachments', ['attachments' => $ticket->attachments])
                                </article>

                                @foreach($ticket->comments as $comment)
                                    <article id="comment-{{ $comment->id }}" class="p-6 scroll-mt-24 {{ $comment->is_internal ? 'bg-yellow-50' : ($comment->isFromUser() ? '' : 'bg-cream/40') }}">
                                        <div class="flex items-center gap-3 mb-3">
                                            <x-user-avatar :user="$comment->author" size="sm" />
                                            <div class="min-w-0">
                                                <p class="text-sm font-medium text-dark">
                                                    {{ $comment->author?->full_name ?? 'Система' }}
                                                    @if($comment->is_internal)
                                                        <span class="ml-2 inline-flex items-center text-[11px] font-medium px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-800">
                                                            <i class="fas fa-lock mr-1"></i>Внутренняя заметка
                                                        </span>
                                                    @elseif(! $comment->isFromUser())
                                                        <span class="ml-2 text-xs text-warm-gray">поддержка</span>
                                                    @endif
                                                </p>
                                                <p class="text-xs text-warm-gray">{{ $comment->created_at->timezone('Europe/Moscow')->format('d.m.Y, H:i') }}</p>
                                            </div>
                                        </div>
                                        <div class="text-sm text-dark whitespace-pre-line leading-relaxed">{{ $comment->content }}</div>
                                        @include('support.tickets.partials.attachments', ['attachments' => $comment->attachments])
                                    </article>
                                @endforeach
                            </div>
                        </div>

                        {{-- Reply --}}
                        @if($ticket->status !== SupportTicketStatus::Closed)
                            <div class="bg-white rounded-xl shadow-sm border border-gold/10 p-6"
                                x-data="{ internal: @js((bool) old('is_internal')) }">
                                <h3 class="font-serif text-lg font-semibold text-dark mb-4"
                                    x-text="internal ? 'Внутренняя заметка' : 'Ответ пользователю'">Ответ пользователю</h3>
                                <x-support.upload-form :action="route('admin.support.tickets.reply', $ticket)" class="space-y-4">
                                    <div>
                                        <label for="reply-content" class="sr-only">Текст ответа</label>
                                        <textarea id="reply-content" name="content" rows="5" required maxlength="20000"
                                            :placeholder="internal ? 'Заметка видна только сотрудникам поддержки...' : 'Ответ пользователю...'"
                                            @paste="pasted($event)"
                                            class="w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 text-sm resize-y transition-colors"
                                            :class="internal ? 'border-yellow-300 bg-yellow-50/50 focus:ring-yellow-300/40' : 'border-primary/20 focus:ring-primary/30'">{{ old('content') }}</textarea>
                                        <x-support.field-error field="content" class="mt-2" />
                                    </div>

                                    <x-support.file-picker id="admin-files" compact />

                                    <div>
                                        <label class="inline-flex items-center gap-2 text-sm text-dark cursor-pointer">
                                            <input type="checkbox" name="is_internal" value="1" x-model="internal"
                                                class="rounded border-primary/30 text-primary">
                                            Внутренняя заметка
                                        </label>
                                        <p class="text-xs text-warm-gray mt-1" x-show="internal" x-cloak>
                                            Заметка не видна пользователю и не меняет статус заявки.
                                        </p>
                                    </div>

                                    <x-support.upload-status />

                                    <button type="submit" :disabled="sending"
                                        class="px-6 py-2.5 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity active:scale-[0.98] text-sm disabled:opacity-60 disabled:cursor-wait">
                                        <span x-show="!sending"><i class="fas fa-paper-plane mr-2" aria-hidden="true"></i>Отправить</span>
                                        <span x-show="sending" x-cloak><i class="fas fa-circle-notch fa-spin mr-2" aria-hidden="true"></i>Отправка…</span>
                                    </button>
                                </x-support.upload-form>
                            </div>
                        @endif
                    </div>

                    {{-- Notification history (ТЗ 11.2) --}}
                    <div x-show="tab === 'notifications'" x-cloak>
                        <div class="bg-white rounded-xl shadow-sm border border-gold/10 overflow-hidden">
                            @if($notificationLogs->isEmpty())
                                <p class="p-6 text-sm text-warm-gray">Писем по этой заявке ещё не отправлялось.</p>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm">
                                        <thead>
                                            <tr class="border-b border-gold/10 text-warm-gray text-xs uppercase tracking-wider">
                                                <th class="text-left px-6 py-3 font-semibold">Кому</th>
                                                <th class="text-left px-6 py-3 font-semibold">Письмо</th>
                                                <th class="text-left px-6 py-3 font-semibold w-40">Когда</th>
                                                <th class="text-left px-6 py-3 font-semibold w-36">Статус</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gold/10">
                                            @foreach($notificationLogs as $log)
                                                <tr>
                                                    <td class="px-6 py-3 text-dark break-all">{{ $log->recipient_email }}</td>
                                                    <td class="px-6 py-3 text-dark">{{ $log->template_label }}</td>
                                                    <td class="px-6 py-3 text-warm-gray whitespace-nowrap">
                                                        {{ $log->created_at?->timezone('Europe/Moscow')->format('d.m.Y, H:i') }} МСК
                                                    </td>
                                                    <td class="px-6 py-3">
                                                        @if($log->wasSent())
                                                            <span class="inline-flex items-center text-xs font-medium px-2.5 py-1 rounded-full bg-green-100 text-green-700">Отправлено</span>
                                                        @else
                                                            <span class="inline-flex items-center text-xs font-medium px-2.5 py-1 rounded-full bg-red-100 text-red-700">Ошибка</span>
                                                            @if($log->error)
                                                                <p class="text-xs text-warm-gray mt-1 break-all">{{ $log->error }}</p>
                                                            @endif
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">

                    {{-- User --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gold/10 p-6">
                        <h3 class="font-serif text-base font-semibold text-dark mb-4">Пользователь</h3>
                        <div class="flex items-center gap-3 mb-3">
                            <x-user-avatar :user="$ticket->user" size="sm" />
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-dark truncate">{{ $ticket->user?->full_name ?? 'Гость' }}</p>
                                <p class="text-xs text-warm-gray truncate">{{ $ticket->contact_email ?? '—' }}</p>
                            </div>
                        </div>
                        @if($ticket->user)
                            {{-- Support-role operators have their own user pages; /admin/users is admin-only --}}
                            <a href="{{ auth()->user()->isAdmin()
                                    ? route('admin.users.show', $ticket->user)
                                    : route('support.users.show', $ticket->user) }}"
                                class="text-xs text-primary hover:underline">Профиль пользователя →</a>
                        @endif
                    </div>

                    {{-- Assignment + SLA --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gold/10 p-6 space-y-4">
                        <div>
                            <p class="text-warm-gray text-xs uppercase tracking-wider mb-1">Ответственный</p>
                            <p class="text-sm font-medium text-dark">{{ $ticket->assignee?->full_name ?? 'Не назначен' }}</p>
                        </div>
                        @if($ticket->assigned_to && $ticket->assigned_to !== auth()->id())
                            <button type="button"
                                @click="$dispatch('confirm-reassign-ticket', { action: '{{ route('admin.support.tickets.assign', $ticket) }}' })"
                                class="w-full px-4 py-2.5 border border-primary/20 text-primary font-medium rounded-lg hover:bg-primary/5 transition-colors active:scale-[0.98] text-sm">
                                <i class="fas fa-hand mr-2"></i>Переназначить на себя
                            </button>
                        @elseif(! $ticket->assigned_to)
                            <form method="POST" action="{{ route('admin.support.tickets.assign', $ticket) }}">
                                @csrf
                                <button type="submit"
                                    class="w-full px-4 py-2.5 border border-primary/20 text-primary font-medium rounded-lg hover:bg-primary/5 transition-colors active:scale-[0.98] text-sm">
                                    <i class="fas fa-hand mr-2"></i>Взять в работу
                                </button>
                            </form>
                        @endif
                        <div class="pt-3 border-t border-gold/10">
                            <p class="text-warm-gray text-xs uppercase tracking-wider mb-1">Срок SLA</p>
                            <p class="text-sm font-medium {{ $ticket->isOverdueNow() ? 'text-red-600' : 'text-dark' }}">
                                {{ $ticket->sla_deadline?->timezone('Europe/Moscow')->format('d.m.Y, H:i') ?? '—' }} МСК
                            </p>
                        </div>
                        @if($ticket->csat_score)
                            <div class="pt-3 border-t border-gold/10">
                                <p class="text-warm-gray text-xs uppercase tracking-wider mb-1">Оценка пользователя</p>
                                <p class="text-sm">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star {{ $i <= $ticket->csat_score ? 'text-gold' : 'text-warm-gray/30' }}"></i>
                                    @endfor
                                </p>
                            </div>
                        @endif
                    </div>

                    {{-- Status --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gold/10 p-6">
                        <h3 class="font-serif text-base font-semibold text-dark mb-4">Статус</h3>
                        @if(count($transitions))
                            <form method="POST" action="{{ route('admin.support.tickets.status', $ticket) }}" class="space-y-3">
                                @csrf @method('PATCH')
                                <select name="status" required
                                    class="w-full px-4 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm">
                                    @foreach($transitions as $next)
                                        <option value="{{ $next->value }}">{{ $next->label() }}</option>
                                    @endforeach
                                </select>
                                <button type="submit"
                                    class="w-full px-4 py-2.5 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity active:scale-[0.98] text-sm">
                                    Сменить статус
                                </button>
                            </form>
                        @else
                            <p class="text-sm text-warm-gray">Заявка закрыта — смена статуса недоступна.</p>
                        @endif
                    </div>

                    {{-- Category --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gold/10 p-6"
                        x-data="{
                            categoryId: '{{ $ticket->category_id }}',
                            subs: @js($categories->mapWithKeys(fn($c) => [$c->id => $c->children->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'is_active' => $s->is_active])])),
                        }">
                        <h3 class="font-serif text-base font-semibold text-dark mb-4">Категория</h3>
                        <form method="POST" action="{{ route('admin.support.tickets.category', $ticket) }}" class="space-y-3">
                            @csrf @method('PATCH')
                            <div>
                                <label for="cat" class="block text-xs font-medium text-warm-gray mb-1">Категория</label>
                                <select id="cat" name="category_id" required x-model="categoryId"
                                    class="w-full px-4 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm">
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" @selected($ticket->category_id === $category->id)>
                                            {{ $category->name }}{{ $category->is_active ? '' : ' (архив)' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="subcat" class="block text-xs font-medium text-warm-gray mb-1">Подкатегория</label>
                                <select id="subcat" name="subcategory_id"
                                    class="w-full px-4 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm">
                                    <option value="">Не выбрана</option>
                                    <template x-for="sub in (subs[categoryId] || [])" :key="sub.id">
                                        <option :value="sub.id" :selected="sub.id === {{ $ticket->subcategory_id ?? 'null' }}"
                                            x-text="sub.name + (sub.is_active ? '' : ' (архив)')"></option>
                                    </template>
                                </select>
                            </div>
                            <button type="submit"
                                class="w-full px-4 py-2.5 border border-primary/20 text-primary font-medium rounded-lg hover:bg-primary/5 transition-colors active:scale-[0.98] text-sm">
                                Сохранить
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-support.lightbox />
</x-app-layout>
