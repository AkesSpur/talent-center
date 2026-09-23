@php use App\Enums\SupportTicketStatus; @endphp
<x-app-layout>
    <x-slot name="header">
        <x-breadcrumbs :items="[
            ['label' => 'Поддержка', 'url' => route('tickets.index')],
            ['label' => 'Заявка ' . $ticket->number],
        ]" />
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
            <div class="min-w-0">
                <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark break-words">{{ $ticket->subject }}</h2>
                <p class="text-warm-gray mt-1">
                    Заявка {{ $ticket->number }} от {{ $ticket->created_at->timezone('Europe/Moscow')->format('d.m.Y, H:i') }} (МСК)
                </p>
            </div>
            <span class="inline-flex shrink-0 items-center text-xs font-medium px-3 py-1.5 rounded-full {{ $ticket->status->color() }}">
                {{ $ticket->status->label() }}
            </span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <x-notify />

            @if(session('status') === 'ticket-created')
                <div class="bg-green-50 border border-green-200 rounded-xl p-4 flex items-start gap-3">
                    <i class="fas fa-circle-check text-green-600 mt-0.5"></i>
                    <p class="text-sm text-dark">
                        Заявка создана, номер <strong>{{ $ticket->number }}</strong>.
                        Ответ поступит в течение {{ app(\App\Services\SlaService::class)->hours() }} часов.
                    </p>
                </div>
            @endif

            {{-- Meta --}}
            <div class="bg-white rounded-xl shadow-sm border border-gold/10 p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-3 gap-6 text-sm">
                    <div>
                        <dt class="text-warm-gray text-xs uppercase tracking-wider mb-1">Категория</dt>
                        <dd class="text-dark font-medium">
                            {{ $ticket->category?->name ?? '—' }}
                            @if($ticket->subcategory)
                                <span class="block text-warm-gray text-xs mt-0.5">{{ $ticket->subcategory->name }}</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-warm-gray text-xs uppercase tracking-wider mb-1">Срок ответа</dt>
                        <dd class="text-dark font-medium">
                            {{ $ticket->sla_deadline?->timezone('Europe/Moscow')->format('d.m.Y, H:i') ?? '—' }}
                            <span class="text-warm-gray text-xs">МСК</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-warm-gray text-xs uppercase tracking-wider mb-1">Оценка</dt>
                        <dd class="text-dark font-medium">
                            @if($ticket->csat_score)
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star {{ $i <= $ticket->csat_score ? 'text-gold' : 'text-warm-gray/30' }}"></i>
                                @endfor
                            @else
                                <span class="text-warm-gray">Не выставлена</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- Conversation --}}
            <div class="bg-white rounded-xl shadow-sm border border-gold/10 overflow-hidden">
                <div class="px-6 py-4 border-b border-gold/10">
                    <h3 class="font-serif text-lg font-semibold text-dark">Переписка</h3>
                </div>

                <div class="divide-y divide-gold/10">
                    {{-- Original request --}}
                    <article class="p-6">
                        <div class="flex items-center gap-3 mb-3">
                            <x-user-avatar :user="$ticket->user" size="sm" />
                            <div>
                                <p class="text-sm font-medium text-dark">Вы</p>
                                <p class="text-xs text-warm-gray">{{ $ticket->created_at->timezone('Europe/Moscow')->format('d.m.Y, H:i') }}</p>
                            </div>
                        </div>
                        <div class="text-sm text-dark whitespace-pre-line leading-relaxed">{{ $ticket->description }}</div>
                        @include('support.tickets.partials.attachments', ['attachments' => $ticket->attachments])
                    </article>

                    @foreach($ticket->publicComments as $comment)
                        <article id="comment-{{ $comment->id }}" class="p-6 scroll-mt-24 {{ $comment->isFromUser() ? '' : 'bg-cream/40' }}">
                            <div class="flex items-center gap-3 mb-3">
                                @if($comment->isFromUser())
                                    <x-user-avatar :user="$comment->author" size="sm" />
                                @else
                                    <div class="w-8 h-8 rounded-full gradient-gold flex items-center justify-center shrink-0">
                                        <i class="fas fa-headset text-xs text-dark"></i>
                                    </div>
                                @endif
                                <div>
                                    <p class="text-sm font-medium text-dark">
                                        {{ $comment->isFromUser() ? 'Вы' : 'Служба поддержки' }}
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

            {{-- Resolved: confirm + rate. Rating stays open after confirming. --}}
            @if($ticket->canBeRated())
                <div class="bg-white rounded-xl shadow-sm border border-green-200 p-6 space-y-5">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center shrink-0">
                            <i class="fas fa-circle-check text-green-600"></i>
                        </div>
                        <div>
                            <h3 class="font-serif text-lg font-semibold text-dark">
                                {{ $ticket->status === SupportTicketStatus::Resolved ? 'Заявка решена' : 'Спасибо за обращение' }}
                            </h3>
                            <p class="text-warm-gray text-sm mt-0.5">
                                @if($ticket->status === SupportTicketStatus::Resolved)
                                    Если вопрос закрыт — подтвердите решение и оцените ответ.
                                    Если нет — просто напишите нам ниже.
                                @else
                                    Заявка закрыта. Вы всё ещё можете оценить работу поддержки.
                                @endif
                            </p>
                        </div>
                    </div>

                    {{-- Rating: real radios so it works with a keyboard and a screen reader --}}
                    <form method="POST" action="{{ route('tickets.rate', $ticket) }}"
                        x-data="{ hover: 0, value: {{ $ticket->csat_score ?? 0 }} }" class="space-y-3">
                        @csrf
                        <fieldset>
                            <legend class="text-sm font-medium text-dark mb-2">Оцените ответ</legend>
                            <div class="flex items-center gap-1" @mouseleave="hover = 0">
                                @for($i = 1; $i <= 5; $i++)
                                    {{-- The empty colour sits on the label, so the stars don't flash dark before Alpine starts --}}
                                    <label class="cursor-pointer p-1 rounded text-warm-gray/30" @mouseenter="hover = {{ $i }}">
                                        <input type="radio" name="csat_score" value="{{ $i }}" class="sr-only peer"
                                            @checked($ticket->csat_score === $i)
                                            @change="value = {{ $i }}"
                                            aria-label="{{ $i }} из 5">
                                        <i class="fas fa-star text-2xl transition-colors peer-focus-visible:ring-2 peer-focus-visible:ring-primary/40 rounded {{ ($ticket->csat_score ?? 0) >= $i ? 'text-gold' : '' }}"
                                            :class="{ 'text-gold': (hover || value) >= {{ $i }} }" aria-hidden="true"></i>
                                    </label>
                                @endfor
                                <span class="ml-2 text-sm text-warm-gray" x-show="value" x-cloak x-text="value + ' из 5'"></span>
                            </div>
                        </fieldset>
                        <x-input-error :messages="$errors->get('csat_score')" />
                        <div class="flex flex-wrap gap-3">
                            <button type="submit" :disabled="!value"
                                class="px-5 py-2.5 border border-primary/20 text-primary font-medium rounded-lg hover:bg-primary/5 transition-colors active:scale-[0.98] text-sm disabled:opacity-40 disabled:cursor-not-allowed">
                                {{ $ticket->csat_score ? 'Изменить оценку' : 'Отправить оценку' }}
                            </button>
                        </div>
                    </form>

                    @if($ticket->status === SupportTicketStatus::Resolved)
                    <form method="POST" action="{{ route('tickets.confirm', $ticket) }}" class="pt-2 border-t border-gold/10">
                        @csrf
                        <button type="submit"
                            class="px-5 py-2.5 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity active:scale-[0.98] text-sm">
                            <i class="fas fa-check mr-2"></i>Подтвердить решение
                        </button>
                        <p class="text-xs text-warm-gray mt-2">После подтверждения заявка будет закрыта. Оценку можно поставить и после этого.</p>
                    </form>
                    @endif
                </div>
            @endif

            {{-- Reply --}}
            @if($ticket->status !== SupportTicketStatus::Closed)
                <div class="bg-white rounded-xl shadow-sm border border-gold/10 p-6">
                    <h3 class="font-serif text-lg font-semibold text-dark mb-4">
                        {{ $ticket->status === SupportTicketStatus::NeedsClarification ? 'Ответьте на уточнение' : 'Добавить сообщение' }}
                    </h3>
                    <x-support.upload-form :action="route('tickets.comment', $ticket)" class="space-y-4">
                        <div>
                            <label for="comment-content" class="sr-only">Сообщение</label>
                            <textarea id="comment-content" name="content" rows="4" required maxlength="20000"
                                placeholder="Напишите сообщение..." @paste="pasted($event)"
                                class="w-full px-4 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm resize-y">{{ old('content') }}</textarea>
                            <x-support.field-error field="content" class="mt-2" />
                        </div>

                        <x-support.file-picker id="comment-files" compact />

                        <x-support.upload-status />

                        <button type="submit" :disabled="sending"
                            class="px-6 py-2.5 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity active:scale-[0.98] text-sm disabled:opacity-60 disabled:cursor-wait">
                            <span x-show="!sending"><i class="fas fa-paper-plane mr-2" aria-hidden="true"></i>Отправить</span>
                            <span x-show="sending" x-cloak><i class="fas fa-circle-notch fa-spin mr-2" aria-hidden="true"></i>Отправка…</span>
                        </button>
                    </x-support.upload-form>
                </div>
            @else
                <div class="bg-cream rounded-xl border border-gold/10 p-6 text-center">
                    <p class="text-warm-gray text-sm">
                        Заявка закрыта{{ $ticket->closed_reason ? ' ' . $ticket->closed_reason->forUser() : '' }}.
                        Если вопрос появился снова,
                        <a href="{{ route('tickets.create') }}" class="text-primary hover:underline">создайте новую заявку</a>.
                    </p>
                </div>
            @endif
        </div>
    </div>

    <x-support.lightbox />
</x-app-layout>
