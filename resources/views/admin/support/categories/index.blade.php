<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <x-breadcrumbs :items="[
                    ['label' => 'Поддержка', 'url' => route('admin.support.tickets.index')],
                    ['label' => 'Настройки поддержки'],
                ]" />
                <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark">Настройки поддержки</h2>
                <p class="text-warm-gray mt-1">Категории обращений и параметры службы поддержки</p>
            </div>
            <button type="button" @click="$dispatch('open-category-modal', { parent: null })"
                class="self-start inline-flex items-center px-5 py-2.5 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity active:scale-[0.98] text-sm">
                <i class="fas fa-plus mr-2"></i>Добавить категорию
            </button>
        </div>
    </x-slot>

    <x-confirm-modal
        name="delete-support-category"
        title="Удалить категорию"
        message="Категорию можно удалить, только если в ней нет заявок и подкатегорий. Обычно достаточно архивировать."
        icon="fa-trash"
        iconColor="text-red-600"
        iconBg="bg-red-100"
        confirmText="Удалить"
        confirmClass="bg-red-600 text-white hover:bg-red-700"
        method="DELETE"
    />

    <div x-data="categoryManager()" class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <x-notify />

            {{-- Helpdesk settings --}}
            <div class="bg-white rounded-xl shadow-sm border border-gold/10 p-6">
                <h3 class="font-serif text-lg font-semibold text-dark mb-4">Параметры</h3>
                <form method="POST" action="{{ route('admin.support.settings.update') }}"
                    class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @csrf
                    <div>
                        <label for="sla" class="block text-sm font-medium text-dark mb-2">Срок SLA, часов</label>
                        <input id="sla" type="number" name="support_sla_hours" min="1" max="720" required
                            value="{{ old('support_sla_hours', $slaHours) }}"
                            class="w-full px-4 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm no-spinner">
                        <p class="text-xs text-warm-gray mt-1.5">Срок на решение заявки с момента создания.</p>
                        <x-input-error :messages="$errors->get('support_sla_hours')" class="mt-2" />
                    </div>
                    <div>
                        <label for="support-email" class="block text-sm font-medium text-dark mb-2">Email поддержки</label>
                        <input id="support-email" type="email" name="support_email" maxlength="255"
                            value="{{ old('support_email', $supportEmail) }}" placeholder="support@talant-centr.ru"
                            class="w-full px-4 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm">
                        <p class="text-xs text-warm-gray mt-1.5">Показывается на страницах входа и восстановления пароля.</p>
                        <x-input-error :messages="$errors->get('support_email')" class="mt-2" />
                    </div>
                    <div class="sm:col-span-2">
                        <button type="submit"
                            class="px-5 py-2.5 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity active:scale-[0.98] text-sm">
                            Сохранить параметры
                        </button>
                    </div>
                </form>
            </div>

            {{-- Categories --}}
            @forelse($categories as $category)
                <div class="bg-white rounded-xl shadow-sm border border-gold/10 overflow-hidden">
                    <div class="px-6 py-4 flex flex-wrap items-center gap-3 border-b border-gold/10 {{ $category->is_active ? '' : 'bg-cream/60' }}">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="font-serif text-lg font-semibold text-dark">{{ $category->name }}</h3>
                                @if($category->is_active)
                                    <span class="inline-flex items-center text-xs font-medium px-2.5 py-1 rounded-full bg-green-100 text-green-700">Активна</span>
                                @else
                                    <span class="inline-flex items-center text-xs font-medium px-2.5 py-1 rounded-full bg-orange-100 text-orange-700">Архив</span>
                                @endif
                                <span class="text-xs text-warm-gray">Порядок: {{ $category->sort_order }}</span>
                                <span class="text-xs text-warm-gray">Заявок: {{ $category->tickets_count }}</span>
                            </div>
                            @if($category->description)
                                <p class="text-sm text-warm-gray mt-1">{{ $category->description }}</p>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button"
                                @click="openEdit({{ Js::from([
                                    'id' => $category->id,
                                    'name' => $category->name,
                                    'description' => $category->description,
                                    'sort_order' => $category->sort_order,
                                    'is_active' => $category->is_active,
                                    'parent_id' => null,
                                ]) }})"
                                class="px-3 py-1.5 border border-primary/20 text-primary text-xs rounded-lg hover:bg-primary/5 transition-colors">
                                <i class="fas fa-pen mr-1"></i>Изменить
                            </button>
                            <form method="POST" action="{{ route('admin.support.categories.archive', $category) }}" class="inline">
                                @csrf
                                <button type="submit"
                                    class="px-3 py-1.5 border border-primary/20 text-warm-gray text-xs rounded-lg hover:bg-primary/5 transition-colors">
                                    <i class="fas {{ $category->is_active ? 'fa-box-archive' : 'fa-rotate-left' }} mr-1"></i>
                                    {{ $category->is_active ? 'В архив' : 'Восстановить' }}
                                </button>
                            </form>
                            <button type="button"
                                @click="$dispatch('confirm-delete-support-category', { action: '{{ route('admin.support.categories.destroy', $category) }}' })"
                                class="px-3 py-1.5 border border-red-200 text-red-500 text-xs rounded-lg hover:bg-red-50 transition-colors">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Subcategories --}}
                    <div class="divide-y divide-gold/10">
                        @foreach($category->children as $sub)
                            <div class="px-6 py-3 flex flex-wrap items-center gap-3 hover:bg-cream/30 transition-colors">
                                <span class="text-sm text-dark flex-1 min-w-0">{{ $sub->name }}</span>
                                @if(! $sub->is_active)
                                    <span class="inline-flex items-center text-xs font-medium px-2 py-0.5 rounded-full bg-orange-100 text-orange-700">архив</span>
                                @endif
                                <span class="text-xs text-warm-gray">{{ $sub->sort_order }}</span>
                                <div class="flex items-center gap-2">
                                    <button type="button"
                                        @click="openEdit({{ Js::from([
                                            'id' => $sub->id,
                                            'name' => $sub->name,
                                            'description' => $sub->description,
                                            'sort_order' => $sub->sort_order,
                                            'is_active' => $sub->is_active,
                                            'parent_id' => $category->id,
                                        ]) }})"
                                        class="px-2.5 py-1 border border-primary/20 text-primary text-xs rounded-lg hover:bg-primary/5 transition-colors">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.support.categories.archive', $sub) }}" class="inline">
                                        @csrf
                                        <button type="submit"
                                            class="px-2.5 py-1 border border-primary/20 text-warm-gray text-xs rounded-lg hover:bg-primary/5 transition-colors">
                                            <i class="fas {{ $sub->is_active ? 'fa-box-archive' : 'fa-rotate-left' }}"></i>
                                        </button>
                                    </form>
                                    <button type="button"
                                        @click="$dispatch('confirm-delete-support-category', { action: '{{ route('admin.support.categories.destroy', $sub) }}' })"
                                        class="px-2.5 py-1 border border-red-200 text-red-500 text-xs rounded-lg hover:bg-red-50 transition-colors">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach

                        <div class="px-6 py-3">
                            <button type="button" @click="openCreate({{ $category->id }})"
                                class="text-sm text-primary hover:underline">
                                <i class="fas fa-plus mr-1"></i>Добавить подкатегорию
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl shadow-sm border border-gold/10 p-12 text-center">
                    <div class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-tags text-3xl text-primary"></i>
                    </div>
                    <h3 class="font-serif text-xl font-semibold text-dark mb-2">Категорий нет</h3>
                    <p class="text-warm-gray mb-6">Загрузите предустановленные категории командой <code class="text-xs">db:seed --class=SupportCategorySeeder</code> или добавьте вручную.</p>
                </div>
            @endforelse
        </div>

        {{-- Create / edit modal --}}
        <div x-show="open" x-cloak
            @open-category-modal.window="openCreate($event.detail.parent)"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            @keydown.escape.window="open = false"
            class="fixed inset-0 z-50 flex items-center justify-center p-4">

            <div class="fixed inset-0 bg-dark/40 backdrop-blur-sm" @click="open = false"></div>

            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <div class="p-6 border-b border-gold/10 flex items-center justify-between">
                    <h3 class="font-serif text-lg font-semibold text-dark"
                        x-text="form.id ? 'Редактировать' : (form.parent_id ? 'Новая подкатегория' : 'Новая категория')"></h3>
                    <button type="button" @click="open = false" class="text-warm-gray hover:text-dark transition-colors" aria-label="Закрыть">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form :action="form.id ? '{{ url('admin/support/categories') }}/' + form.id : '{{ route('admin.support.categories.store') }}'"
                    method="POST" class="p-6 space-y-4">
                    @csrf
                    <template x-if="form.id"><input type="hidden" name="_method" value="PUT"></template>
                    <input type="hidden" name="parent_id" :value="form.parent_id ?? ''">

                    <div>
                        <label class="block text-sm font-medium text-dark mb-2">Название <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required maxlength="50" x-model="form.name"
                            class="w-full px-4 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm">
                        <p class="text-xs text-warm-gray mt-1" x-text="(form.name?.length || 0) + ' / 50'"></p>
                    </div>

                    <div x-show="!form.parent_id">
                        <label class="block text-sm font-medium text-dark mb-2">Описание (внутреннее)</label>
                        <textarea name="description" rows="2" maxlength="1000" x-model="form.description"
                            class="w-full px-4 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm resize-none"></textarea>
                        <p class="text-xs text-warm-gray mt-1">Не видно пользователю.</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-dark mb-2">Порядок</label>
                            <input type="number" name="sort_order" min="0" x-model="form.sort_order"
                                class="w-full px-4 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm no-spinner">
                        </div>
                        <div class="flex items-end pb-0.5">
                            <label class="flex items-center gap-2 cursor-pointer">
                                {{-- Unchecked checkboxes are not submitted; this carries the "false" --}}
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" x-model="form.is_active"
                                    class="rounded border-primary/30 text-primary">
                                <span class="text-sm font-medium text-dark">Активна</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="submit" class="px-5 py-2.5 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity active:scale-[0.98] text-sm">
                            Сохранить
                        </button>
                        <button type="button" @click="open = false"
                            class="px-5 py-2.5 border border-primary/20 text-warm-gray rounded-lg hover:border-primary/40 transition-colors text-sm">
                            Отмена
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function categoryManager() {
            const blank = { id: null, name: '', description: '', sort_order: 0, is_active: true, parent_id: null };
            return {
                open: false,
                form: { ...blank },
                openCreate(parentId) {
                    this.form = { ...blank, parent_id: parentId ?? null };
                    this.open = true;
                },
                openEdit(data) {
                    this.form = { ...data };
                    this.open = true;
                },
            };
        }
    </script>
    @endpush
</x-app-layout>
