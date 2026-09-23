<x-app-layout>
    <x-slot name="header">
        <x-breadcrumbs :items="[
            ['label' => 'Поддержка', 'url' => route('tickets.index')],
            ['label' => 'Новая заявка'],
        ]" />
        <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark">Новая заявка</h2>
        <p class="text-warm-gray mt-1">Опишите вопрос — ответим в течение {{ app(\App\Services\SlaService::class)->hours() }} часов</p>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <x-notify />

            <x-support.upload-form :action="route('tickets.store')"
                class="bg-white rounded-xl shadow-sm border border-gold/10 p-6 sm:p-8 space-y-6">

                {{-- Category --}}
                <div>
                    <label for="category_id" class="block text-sm font-medium text-dark mb-2">
                        Категория обращения <span class="text-red-500">*</span>
                    </label>
                    <select id="category_id" name="category_id" required
                        class="w-full px-4 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm">
                        <option value="">Выберите категорию</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}"
                                @selected(old('category_id', $selected) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <x-support.field-error field="category_id" class="mt-2" />
                </div>

                {{-- Subject --}}
                <div x-data="{ subject: @js(old('subject', '')) }">
                    <label for="subject" class="block text-sm font-medium text-dark mb-2">
                        Тема <span class="text-red-500">*</span>
                    </label>
                    <input id="subject" type="text" name="subject" required maxlength="150"
                        value="{{ old('subject') }}" x-model="subject"
                        placeholder="Коротко о вашем вопросе"
                        class="w-full px-4 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm">
                    <div class="flex justify-between gap-3 mt-1.5">
                        <x-support.field-error field="subject" />
                        <span class="text-xs text-warm-gray ml-auto shrink-0" x-text="subject.length + ' / 150'">0 / 150</span>
                    </div>
                </div>

                {{-- Description --}}
                <div>
                    <label for="description" class="block text-sm font-medium text-dark mb-2">
                        Описание <span class="text-red-500">*</span>
                    </label>
                    <textarea id="description" name="description" rows="7" required maxlength="20000"
                        placeholder="Опишите подробно: что произошло, на каком шаге, номер заявки на конкурс, если вопрос по ней"
                        @paste="pasted($event)"
                        class="w-full px-4 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm resize-y">{{ old('description') }}</textarea>
                    <x-support.field-error field="description" class="mt-2" />
                </div>

                {{-- Files --}}
                <div>
                    <p class="block text-sm font-medium text-dark mb-2">Вложения</p>
                    <x-support.file-picker id="files" />
                </div>

                <x-support.upload-status />

                <div class="flex flex-wrap gap-3 pt-2 border-t border-gold/10">
                    <button type="submit" :disabled="sending"
                        class="px-6 py-2.5 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity active:scale-[0.98] text-sm disabled:opacity-60 disabled:cursor-wait">
                        <span x-show="!sending"><i class="fas fa-paper-plane mr-2" aria-hidden="true"></i>Отправить заявку</span>
                        <span x-show="sending" x-cloak><i class="fas fa-circle-notch fa-spin mr-2" aria-hidden="true"></i>Отправка…</span>
                    </button>
                    <a href="{{ route('tickets.index') }}"
                        class="px-6 py-2.5 border border-primary/20 text-warm-gray rounded-lg hover:border-primary/40 transition-colors text-sm">
                        Отмена
                    </a>
                </div>
            </x-support.upload-form>
        </div>
    </div>
</x-app-layout>
