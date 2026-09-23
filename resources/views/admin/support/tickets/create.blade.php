<x-app-layout>
    <x-slot name="header">
        <x-breadcrumbs :items="[
            ['label' => 'Поддержка', 'url' => route('admin.support.tickets.index')],
            ['label' => 'Новая заявка'],
        ]" />
        <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark">Заявка от имени пользователя</h2>
        <p class="text-warm-gray mt-1">Если пользователь обратился по телефону или почте</p>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-notify />

            {{-- The user search keeps its own state; the form below owns the upload state --}}
            <div x-data="userPicker()">
                <x-support.upload-form :action="route('admin.support.tickets.store')"
                    class="bg-white rounded-xl shadow-sm border border-gold/10 p-6 sm:p-8 space-y-6">

                    {{-- User search --}}
                    <div>
                        <label for="user-search" class="block text-sm font-medium text-dark mb-2">
                            Пользователь <span class="text-red-500">*</span>
                        </label>
                        <input type="hidden" name="user_id" :value="selected?.id">

                        <div class="relative" x-show="!selected">
                            <input id="user-search" type="text" x-model="query" @input.debounce.300ms="search()"
                                placeholder="Поиск по email или ФИО (минимум 2 символа)"
                                autocomplete="off"
                                class="w-full px-4 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm">

                            <ul x-show="results.length" x-cloak
                                class="absolute z-20 mt-1 w-full bg-white border border-gold/20 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                <template x-for="user in results" :key="user.id">
                                    <li>
                                        <button type="button" @click="choose(user)"
                                            class="w-full text-left px-4 py-2.5 hover:bg-cream transition-colors">
                                            <span class="block text-sm text-dark" x-text="user.name"></span>
                                            <span class="block text-xs text-warm-gray" x-text="user.email"></span>
                                        </button>
                                    </li>
                                </template>
                            </ul>
                            <p class="mt-2 text-xs text-warm-gray" x-show="searched && !results.length && query.length >= 2" x-cloak>
                                Ничего не найдено.
                            </p>
                        </div>

                        <div x-show="selected" x-cloak
                            class="flex items-center justify-between gap-3 px-4 py-3 bg-cream rounded-lg">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-dark truncate" x-text="selected?.name"></p>
                                <p class="text-xs text-warm-gray truncate" x-text="selected?.email"></p>
                            </div>
                            <button type="button" @click="selected = null; query = ''; results = []"
                                class="text-warm-gray hover:text-dark transition-colors shrink-0" aria-label="Сбросить выбор">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <x-support.field-error field="user_id" class="mt-2" />
                    </div>

                    <div>
                        <label for="category_id" class="block text-sm font-medium text-dark mb-2">
                            Категория <span class="text-red-500">*</span>
                        </label>
                        <select id="category_id" name="category_id" required
                            class="w-full px-4 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm">
                            <option value="">Выберите категорию</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <x-support.field-error field="category_id" class="mt-2" />
                    </div>

                    <div>
                        <label for="subject" class="block text-sm font-medium text-dark mb-2">
                            Тема <span class="text-red-500">*</span>
                        </label>
                        <input id="subject" type="text" name="subject" required maxlength="150" value="{{ old('subject') }}"
                            class="w-full px-4 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm">
                        <x-support.field-error field="subject" class="mt-2" />
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-dark mb-2">
                            Описание <span class="text-red-500">*</span>
                        </label>
                        <textarea id="description" name="description" rows="6" required maxlength="20000"
                            placeholder="Суть обращения со слов пользователя" @paste="pasted($event)"
                            class="w-full px-4 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm resize-y">{{ old('description') }}</textarea>
                        <x-support.field-error field="description" class="mt-2" />
                    </div>

                    <div>
                        <p class="block text-sm font-medium text-dark mb-2">Вложения</p>
                        <x-support.file-picker id="files" compact />
                    </div>

                    <x-support.upload-status />

                    <div class="flex flex-wrap gap-3 pt-2 border-t border-gold/10">
                        <button type="submit" :disabled="!selected || sending"
                            class="px-6 py-2.5 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity active:scale-[0.98] text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!sending">Создать заявку</span>
                            <span x-show="sending" x-cloak><i class="fas fa-circle-notch fa-spin mr-2" aria-hidden="true"></i>Отправка…</span>
                        </button>
                        <a href="{{ route('admin.support.tickets.index') }}"
                            class="px-6 py-2.5 border border-primary/20 text-warm-gray rounded-lg hover:border-primary/40 transition-colors text-sm">
                            Отмена
                        </a>
                    </div>
                </x-support.upload-form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function userPicker() {
            return {
                query: '', results: [], selected: null, searched: false,
                async search() {
                    if (this.query.length < 2) { this.results = []; this.searched = false; return; }
                    const res = await fetch('{{ route('admin.support.tickets.search-users') }}?q=' + encodeURIComponent(this.query), {
                        headers: { 'Accept': 'application/json' },
                    });
                    this.results = res.ok ? await res.json() : [];
                    this.searched = true;
                },
                choose(user) { this.selected = user; this.results = []; },
            };
        }
    </script>
    @endpush
</x-app-layout>
