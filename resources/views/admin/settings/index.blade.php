<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark leading-tight">Общие настройки</h2>
            <p class="text-warm-gray text-sm mt-0.5">Управление контактами, брендингом и документами платформы</p>
        </div>
    </x-slot>

    {{-- Confirm modals --}}
    <x-confirm-modal
        name="delete-privacy"
        title="Удалить политику конфиденциальности"
        message="Вы уверены, что хотите удалить документ? Это действие нельзя отменить."
        icon="fa-trash-alt"
        iconColor="text-red-600"
        iconBg="bg-red-100"
        confirmText="Удалить"
        confirmClass="bg-red-600 text-white"
        method="DELETE"
    />
    <x-confirm-modal
        name="delete-offer"
        title="Удалить договор оферты"
        message="Вы уверены, что хотите удалить документ оферты? Ссылка в форме организации перестанет работать."
        icon="fa-trash-alt"
        iconColor="text-red-600"
        iconBg="bg-red-100"
        confirmText="Удалить"
        confirmClass="bg-red-600 text-white"
        method="DELETE"
    />
    <x-confirm-modal
        name="delete-logo"
        title="Удалить логотип"
        message="Вы уверены, что хотите удалить логотип сайта?"
        icon="fa-trash-alt"
        iconColor="text-red-600"
        iconBg="bg-red-100"
        confirmText="Удалить"
        confirmClass="bg-red-600 text-white"
        method="DELETE"
    />
    <x-confirm-modal
        name="delete-favicon"
        title="Удалить фавикон"
        message="Вы уверены, что хотите удалить фавикон сайта?"
        icon="fa-trash-alt"
        iconColor="text-red-600"
        iconBg="bg-red-100"
        confirmText="Удалить"
        confirmClass="bg-red-600 text-white"
        method="DELETE"
    />

    <div class="py-8" x-data="{ tab: '{{ request('tab', 'contacts') }}' }">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <x-notify />

            {{-- Tab bar --}}
            <div class="bg-white rounded-xl shadow-sm border border-gold/10 overflow-hidden">
                <div class="flex overflow-x-auto border-b border-gold/10">
                    <button @click="tab = 'contacts'"
                        :class="tab === 'contacts' ? 'border-b-2 border-gold text-primary font-semibold' : 'text-warm-gray hover:text-dark'"
                        class="flex items-center gap-2 px-5 py-3.5 text-sm whitespace-nowrap transition-colors shrink-0">
                        <i class="fas fa-address-book text-xs"></i>
                        Контактные данные
                    </button>
                    <button @click="tab = 'privacy'"
                        :class="tab === 'privacy' ? 'border-b-2 border-gold text-primary font-semibold' : 'text-warm-gray hover:text-dark'"
                        class="flex items-center gap-2 px-5 py-3.5 text-sm whitespace-nowrap transition-colors shrink-0">
                        <i class="fas fa-file-lines text-xs"></i>
                        Документы
                    </button>
                    <button @click="tab = 'branding'"
                        :class="tab === 'branding' ? 'border-b-2 border-gold text-primary font-semibold' : 'text-warm-gray hover:text-dark'"
                        class="flex items-center gap-2 px-5 py-3.5 text-sm whitespace-nowrap transition-colors shrink-0">
                        <i class="fas fa-palette text-xs"></i>
                        Брендинг
                    </button>
                    <button @click="tab = 'app-settings'"
                        :class="tab === 'app-settings' ? 'border-b-2 border-gold text-primary font-semibold' : 'text-warm-gray hover:text-dark'"
                        class="flex items-center gap-2 px-5 py-3.5 text-sm whitespace-nowrap transition-colors shrink-0">
                        <i class="fas fa-sliders text-xs"></i>
                        Настройки
                    </button>
                </div>
            </div>

            {{-- ── TAB: Contacts ── --}}
            <div x-show="tab === 'contacts'" x-cloak>
                <div class="bg-white rounded-xl shadow-sm border border-gold/10 overflow-hidden">
                    {{-- Block header --}}
                    <div class="flex items-center gap-3 px-6 py-4 border-b border-gold/10 bg-cream/30">
                        <div class="w-9 h-9 rounded-lg bg-green-100 flex items-center justify-center shrink-0">
                            <i class="fas fa-address-book text-green-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-dark text-sm">Контактные данные</p>
                            <p class="text-xs text-warm-gray">Контакты для отображения в футере сайта</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.settings.contacts') }}" class="p-6">
                        @csrf

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-6">
                            {{-- Phone --}}
                            <div>
                                <label for="contact_phone" class="block text-xs font-semibold text-warm-gray uppercase tracking-wider mb-2">
                                    Номер телефона
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                                        <i class="fas fa-phone text-warm-gray text-sm"></i>
                                    </div>
                                    <input type="text" id="contact_phone" name="contact_phone"
                                        value="{{ old('contact_phone', $settings[\App\Models\SiteSettings::CONTACT_PHONE] ?? '') }}"
                                        placeholder="+7 (800) 000-00-00"
                                        class="w-full pl-10 pr-4 py-2.5 border border-primary/20 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 bg-cream/30 text-dark placeholder-warm-gray/60">
                                </div>
                                @error('contact_phone')
                                    <p class="mt-1.5 text-xs text-red-600"><i class="fas fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Email --}}
                            <div>
                                <label for="contact_email" class="block text-xs font-semibold text-warm-gray uppercase tracking-wider mb-2">
                                    Email
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                                        <i class="fas fa-envelope text-warm-gray text-sm"></i>
                                    </div>
                                    <input type="email" id="contact_email" name="contact_email"
                                        value="{{ old('contact_email', $settings[\App\Models\SiteSettings::CONTACT_EMAIL] ?? '') }}"
                                        placeholder="info@talant-centr.ru"
                                        class="w-full pl-10 pr-4 py-2.5 border border-primary/20 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 bg-cream/30 text-dark placeholder-warm-gray/60">
                                </div>
                                @error('contact_email')
                                    <p class="mt-1.5 text-xs text-red-600"><i class="fas fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Address --}}
                            <div class="sm:col-span-2">
                                <label for="contact_address" class="block text-xs font-semibold text-warm-gray uppercase tracking-wider mb-2">
                                    Адрес
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                                        <i class="fas fa-map-marker-alt text-warm-gray text-sm"></i>
                                    </div>
                                    <input type="text" id="contact_address" name="contact_address"
                                        value="{{ old('contact_address', $settings[\App\Models\SiteSettings::CONTACT_ADDRESS] ?? '') }}"
                                        placeholder="г. Москва"
                                        class="w-full pl-10 pr-4 py-2.5 border border-primary/20 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 bg-cream/30 text-dark placeholder-warm-gray/60">
                                </div>
                                @error('contact_address')
                                    <p class="mt-1.5 text-xs text-red-600"><i class="fas fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gold/10">
                            <button type="reset"
                                class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-warm-gray hover:text-dark border border-primary/15 rounded-lg transition-colors">
                                <i class="fas fa-rotate-left text-xs"></i> Сбросить
                            </button>
                            <button type="submit"
                                class="flex items-center gap-2 px-5 py-2 gradient-gold text-dark font-semibold text-sm rounded-lg hover:opacity-90 transition-opacity">
                                <i class="fas fa-floppy-disk text-xs"></i> Сохранить
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ── TAB: Privacy Policy ── --}}
            <div x-show="tab === 'privacy'" x-cloak>
                <div class="bg-white rounded-xl shadow-sm border border-gold/10 overflow-hidden">
                    {{-- Block header --}}
                    <div class="flex items-center gap-3 px-6 py-4 border-b border-gold/10 bg-cream/30">
                        <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
                            <i class="fas fa-file-lines text-blue-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-dark text-sm">Политика конфиденциальности</p>
                            <p class="text-xs text-warm-gray">Документ будет отображаться по ссылке в футере сайта</p>
                        </div>
                    </div>

                    <div class="p-6">
                        {{-- Current file --}}
                        @if(!empty($settings[\App\Models\SiteSettings::PRIVACY_POLICY]))
                            <div class="flex items-center justify-between gap-4 p-4 mb-5 bg-green-50 border border-green-200 rounded-xl">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-9 h-9 bg-green-100 rounded-lg flex items-center justify-center shrink-0">
                                        <i class="fas fa-file-pdf text-green-600"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-dark">Документ загружен</p>
                                        <a href="{{ route('privacy-policy') }}" target="_blank"
                                            class="text-xs text-primary hover:underline truncate block">
                                            Открыть документ <i class="fas fa-external-link-alt ml-1"></i>
                                        </a>
                                    </div>
                                </div>
                                <button type="button"
                                    @click="$dispatch('confirm-delete-privacy', { action: '{{ route('admin.settings.privacy-policy.delete') }}' })"
                                    class="shrink-0 flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 border border-red-200 rounded-lg transition-colors">
                                    <i class="fas fa-trash-alt"></i> Удалить
                                </button>
                            </div>
                        @endif

                        {{-- Upload form --}}
                        <form method="POST" action="{{ route('admin.settings.privacy-policy') }}" enctype="multipart/form-data">
                            @csrf

                            <div class="mb-5">
                                <label class="block text-xs font-semibold text-warm-gray uppercase tracking-wider mb-2">
                                    PDF-документ политики конфиденциальности
                                </label>

                                <label for="privacy_policy"
                                    class="flex flex-col items-center justify-center w-full border-2 border-dashed rounded-xl cursor-pointer transition-colors
                                           {{ $errors->has('privacy_policy') ? 'border-red-300 bg-red-50/30' : 'border-primary/20 hover:border-gold/50 bg-cream/30 hover:bg-cream/60' }}"
                                    style="min-height: 130px;"
                                    x-data="{ name: '' }"
                                    @change="name = $event.target.files[0]?.name || ''">
                                    <input type="file" id="privacy_policy" name="privacy_policy"
                                        accept=".pdf,application/pdf" class="hidden"
                                        @change="name = $event.target.files[0]?.name || ''">

                                    <div class="flex flex-col items-center py-6 px-4 text-center pointer-events-none">
                                        <i class="fas fa-cloud-arrow-up text-3xl text-warm-gray mb-2"></i>
                                        <p class="text-sm font-medium text-dark" x-text="name || 'Нажмите или перетащите PDF-файл'"></p>
                                        <p class="text-xs text-warm-gray mt-1">Максимальный размер: 10 МБ</p>
                                    </div>
                                </label>

                                @error('privacy_policy')
                                    <p class="mt-1.5 text-xs text-red-600"><i class="fas fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                                @enderror
                                <p class="mt-2 text-xs text-warm-gray">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Загрузите PDF-документ политики конфиденциальности. Документ будет доступен по ссылке в футере сайта.
                                </p>
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gold/10">
                                <button type="reset"
                                    class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-warm-gray hover:text-dark border border-primary/15 rounded-lg transition-colors">
                                    <i class="fas fa-rotate-left text-xs"></i> Сбросить
                                </button>
                                <button type="submit"
                                    class="flex items-center gap-2 px-5 py-2 gradient-gold text-dark font-semibold text-sm rounded-lg hover:opacity-90 transition-opacity">
                                    <i class="fas fa-floppy-disk text-xs"></i> Сохранить
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ── Offer document block (still in "Документы" tab) ── --}}
            <div x-show="tab === 'privacy'" x-cloak>
                <div class="bg-white rounded-xl shadow-sm border border-gold/10 overflow-hidden">
                    <div class="flex items-center gap-3 px-6 py-4 border-b border-gold/10 bg-cream/30">
                        <div class="w-9 h-9 rounded-lg bg-amber-100 flex items-center justify-center shrink-0">
                            <i class="fas fa-handshake text-amber-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-dark text-sm">Договор оферты</p>
                            <p class="text-xs text-warm-gray">PDF-документ, на который ссылается чекбокс на странице редактирования организации</p>
                        </div>
                    </div>

                    <div class="p-6">
                        @php $offerDoc = $settings[\App\Models\SiteSettings::OFFER_DOCUMENT] ?? ''; @endphp
                        @if(!empty($offerDoc))
                            <div class="flex items-center justify-between gap-4 p-4 mb-5 bg-green-50 border border-green-200 rounded-xl">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-9 h-9 bg-green-100 rounded-lg flex items-center justify-center shrink-0">
                                        <i class="fas fa-file-pdf text-green-600"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-dark">Оферта загружена</p>
                                        <a href="{{ asset('storage/' . $offerDoc) }}" target="_blank"
                                            class="text-xs text-primary hover:underline truncate block">
                                            Открыть документ <i class="fas fa-external-link-alt ml-1"></i>
                                        </a>
                                    </div>
                                </div>
                                <button type="button"
                                    @click="$dispatch('confirm-delete-offer', { action: '{{ route('admin.settings.offer-document.delete') }}' })"
                                    class="shrink-0 flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 border border-red-200 rounded-lg transition-colors">
                                    <i class="fas fa-trash-alt"></i> Удалить
                                </button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('admin.settings.offer-document') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-5">
                                <label class="block text-xs font-semibold text-warm-gray uppercase tracking-wider mb-2">
                                    PDF-документ договора оферты
                                </label>
                                <label for="offer_document"
                                    class="flex flex-col items-center justify-center w-full border-2 border-dashed rounded-xl cursor-pointer transition-colors
                                           {{ $errors->has('offer_document') ? 'border-red-300 bg-red-50/30' : 'border-primary/20 hover:border-gold/50 bg-cream/30 hover:bg-cream/60' }}"
                                    style="min-height: 130px;"
                                    x-data="{ name: '' }">
                                    <input type="file" id="offer_document" name="offer_document"
                                        accept=".pdf,application/pdf" class="hidden"
                                        @change="name = $event.target.files[0]?.name || ''">
                                    <div class="flex flex-col items-center py-6 px-4 text-center pointer-events-none">
                                        <i class="fas fa-cloud-arrow-up text-3xl text-warm-gray mb-2"></i>
                                        <p class="text-sm font-medium text-dark" x-text="name || 'Нажмите или перетащите PDF-файл'"></p>
                                        <p class="text-xs text-warm-gray mt-1">Максимальный размер: 10 МБ</p>
                                    </div>
                                </label>
                                @error('offer_document')
                                    <p class="mt-1.5 text-xs text-red-600"><i class="fas fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gold/10">
                                <button type="submit"
                                    class="flex items-center gap-2 px-5 py-2 gradient-gold text-dark font-semibold text-sm rounded-lg hover:opacity-90 transition-opacity">
                                    <i class="fas fa-floppy-disk text-xs"></i> Сохранить
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ── TAB: Branding ── --}}
            <div x-show="tab === 'branding'" x-cloak>

                {{-- Brand text block --}}
                <div class="bg-white rounded-xl shadow-sm border border-gold/10 overflow-hidden mb-5">
                    <div class="flex items-center gap-3 px-6 py-4 border-b border-gold/10 bg-cream/30">
                        <div class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                            <i class="fas fa-font text-primary text-sm"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-dark text-sm">Название и подпись</p>
                            <p class="text-xs text-warm-gray">Текст и цвет названия сайта в шапке на всех страницах</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.settings.brand-text') }}" class="p-6">
                        @csrf

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-6">
                            {{-- Site name --}}
                            <div>
                                <label for="site_name" class="block text-xs font-semibold text-warm-gray uppercase tracking-wider mb-2">
                                    Название сайта
                                </label>
                                <input type="text" id="site_name" name="site_name"
                                    value="{{ old('site_name', $settings[\App\Models\SiteSettings::SITE_NAME] ?? '') }}"
                                    placeholder="Талант-центр"
                                    class="w-full px-4 py-2.5 border border-primary/20 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 bg-cream/30 text-dark placeholder-warm-gray/60">
                                @error('site_name')
                                    <p class="mt-1.5 text-xs text-red-600"><i class="fas fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-warm-gray">По умолчанию: Талант-центр</p>
                            </div>

                            {{-- Site name color --}}
                            <div>
                                <label for="site_name_color" class="block text-xs font-semibold text-warm-gray uppercase tracking-wider mb-2">
                                    Цвет названия
                                </label>
                                <div class="flex items-center gap-3">
                                    <input type="color" id="site_name_color_picker"
                                        value="{{ $settings[\App\Models\SiteSettings::SITE_NAME_COLOR] ?? '#8B4513' }}"
                                        class="w-10 h-10 rounded-lg border border-primary/20 cursor-pointer p-0.5 bg-cream/30"
                                        x-data
                                        @input="$refs.site_name_color_input.value = $event.target.value">
                                    <input type="text" id="site_name_color" name="site_name_color"
                                        x-ref="site_name_color_input"
                                        value="{{ old('site_name_color', $settings[\App\Models\SiteSettings::SITE_NAME_COLOR] ?? '#8B4513') }}"
                                        placeholder="#8B4513"
                                        class="flex-1 px-4 py-2.5 border border-primary/20 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 bg-cream/30 text-dark placeholder-warm-gray/60 font-mono"
                                        @input="$el.previousElementSibling.value = $event.target.value">
                                </div>
                                @error('site_name_color')
                                    <p class="mt-1.5 text-xs text-red-600"><i class="fas fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-warm-gray">По умолчанию: #8B4513</p>
                            </div>

                            {{-- Subtitle --}}
                            <div>
                                <label for="site_subtitle" class="block text-xs font-semibold text-warm-gray uppercase tracking-wider mb-2">
                                    Подпись (subtitle)
                                </label>
                                <input type="text" id="site_subtitle" name="site_subtitle"
                                    value="{{ old('site_subtitle', $settings[\App\Models\SiteSettings::SITE_SUBTITLE] ?? '') }}"
                                    placeholder="Всероссийский центр талантов"
                                    class="w-full px-4 py-2.5 border border-primary/20 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 bg-cream/30 text-dark placeholder-warm-gray/60">
                                @error('site_subtitle')
                                    <p class="mt-1.5 text-xs text-red-600"><i class="fas fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-warm-gray">По умолчанию: Всероссийский центр талантов</p>
                            </div>

                            {{-- Subtitle color --}}
                            <div>
                                <label for="site_subtitle_color" class="block text-xs font-semibold text-warm-gray uppercase tracking-wider mb-2">
                                    Цвет подписи
                                </label>
                                <div class="flex items-center gap-3">
                                    <input type="color" id="site_subtitle_color_picker"
                                        value="{{ $settings[\App\Models\SiteSettings::SITE_SUBTITLE_COLOR] ?? '#9A8B7A' }}"
                                        class="w-10 h-10 rounded-lg border border-primary/20 cursor-pointer p-0.5 bg-cream/30"
                                        x-data
                                        @input="$refs.site_subtitle_color_input.value = $event.target.value">
                                    <input type="text" id="site_subtitle_color" name="site_subtitle_color"
                                        x-ref="site_subtitle_color_input"
                                        value="{{ old('site_subtitle_color', $settings[\App\Models\SiteSettings::SITE_SUBTITLE_COLOR] ?? '#9A8B7A') }}"
                                        placeholder="#9A8B7A"
                                        class="flex-1 px-4 py-2.5 border border-primary/20 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 bg-cream/30 text-dark placeholder-warm-gray/60 font-mono"
                                        @input="$el.previousElementSibling.value = $event.target.value">
                                </div>
                                @error('site_subtitle_color')
                                    <p class="mt-1.5 text-xs text-red-600"><i class="fas fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-warm-gray">По умолчанию: #9A8B7A</p>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gold/10">
                            <button type="reset"
                                class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-warm-gray hover:text-dark border border-primary/15 rounded-lg transition-colors">
                                <i class="fas fa-rotate-left text-xs"></i> Сбросить
                            </button>
                            <button type="submit"
                                class="flex items-center gap-2 px-5 py-2 gradient-gold text-dark font-semibold text-sm rounded-lg hover:opacity-90 transition-opacity">
                                <i class="fas fa-floppy-disk text-xs"></i> Сохранить
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Logo block --}}
                <div class="bg-white rounded-xl shadow-sm border border-gold/10 overflow-hidden mb-5">
                    <div class="flex items-center gap-3 px-6 py-4 border-b border-gold/10 bg-cream/30">
                        <div class="w-9 h-9 rounded-lg bg-gold/15 flex items-center justify-center shrink-0">
                            <i class="fas fa-image text-primary text-sm"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-dark text-sm">Логотип сайта</p>
                            <p class="text-xs text-warm-gray">Отображается в шапке на всех страницах. Если не загружен — используется иконка по умолчанию</p>
                        </div>
                    </div>

                    <div class="p-6">
                        {{-- Current logo --}}
                        @if(!empty($settings[\App\Models\SiteSettings::SITE_LOGO]))
                            <div class="flex items-center justify-between gap-4 p-4 mb-5 bg-cream/50 border border-gold/20 rounded-xl">
                                <div class="flex items-center gap-4 min-w-0">
                                    <img src="{{ asset('storage/' . $settings[\App\Models\SiteSettings::SITE_LOGO]) }}"
                                        alt="Текущий логотип" class="h-12 w-auto max-w-[120px] object-contain rounded">
                                    <div>
                                        <p class="text-sm font-semibold text-dark">Текущий логотип</p>
                                        <p class="text-xs text-warm-gray">Загружен и отображается в шапке</p>
                                    </div>
                                </div>
                                <button type="button"
                                    @click="$dispatch('confirm-delete-logo', { action: '{{ route('admin.settings.logo.delete') }}' })"
                                    class="shrink-0 flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 border border-red-200 rounded-lg transition-colors">
                                    <i class="fas fa-trash-alt"></i> Удалить
                                </button>
                            </div>
                        @else
                            <div class="flex items-center gap-4 p-4 mb-5 bg-cream/50 border border-gold/20 rounded-xl">
                                <div class="w-11 h-11 gradient-gold rounded-full flex items-center justify-center shrink-0">
                                    <i class="fas fa-award text-white text-lg"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-dark">Используется логотип по умолчанию</p>
                                    <p class="text-xs text-warm-gray">Загрузите изображение, чтобы заменить его</p>
                                </div>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('admin.settings.logo') }}" enctype="multipart/form-data">
                            @csrf

                            <div class="mb-5">
                                <label class="block text-xs font-semibold text-warm-gray uppercase tracking-wider mb-2">
                                    Новый логотип
                                </label>

                                <label for="site_logo"
                                    class="flex flex-col items-center justify-center w-full border-2 border-dashed rounded-xl cursor-pointer transition-colors
                                           {{ $errors->has('site_logo') ? 'border-red-300 bg-red-50/30' : 'border-primary/20 hover:border-gold/50 bg-cream/30 hover:bg-cream/60' }}"
                                    style="min-height: 120px;"
                                    x-data="{ name: '', preview: null }">
                                    <input type="file" id="site_logo" name="site_logo"
                                        accept=".jpg,.jpeg,.png,.webp,.svg,image/*" class="hidden"
                                        @change="name = $event.target.files[0]?.name || ''; preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">

                                    <div class="flex flex-col items-center py-5 px-4 text-center pointer-events-none">
                                        <template x-if="preview">
                                            <img :src="preview" class="h-10 mb-2 object-contain rounded" alt="">
                                        </template>
                                        <template x-if="!preview">
                                            <i class="fas fa-cloud-arrow-up text-3xl text-warm-gray mb-2"></i>
                                        </template>
                                        <p class="text-sm font-medium text-dark" x-text="name || 'Нажмите или перетащите изображение'"></p>
                                        <p class="text-xs text-warm-gray mt-1">JPG, PNG, WebP, SVG · до 2 МБ</p>
                                    </div>
                                </label>

                                @error('site_logo')
                                    <p class="mt-1.5 text-xs text-red-600"><i class="fas fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gold/10">
                                <button type="reset"
                                    class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-warm-gray hover:text-dark border border-primary/15 rounded-lg transition-colors">
                                    <i class="fas fa-rotate-left text-xs"></i> Сбросить
                                </button>
                                <button type="submit"
                                    class="flex items-center gap-2 px-5 py-2 gradient-gold text-dark font-semibold text-sm rounded-lg hover:opacity-90 transition-opacity">
                                    <i class="fas fa-floppy-disk text-xs"></i> Сохранить
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Favicon block --}}
                <div class="bg-white rounded-xl shadow-sm border border-gold/10 overflow-hidden">
                    <div class="flex items-center gap-3 px-6 py-4 border-b border-gold/10 bg-cream/30">
                        <div class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                            <i class="fas fa-star text-primary text-sm"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-dark text-sm">Фавикон</p>
                            <p class="text-xs text-warm-gray">Иконка вкладки браузера (ICO, PNG, SVG)</p>
                        </div>
                    </div>

                    <div class="p-6">
                        {{-- Current favicon --}}
                        @if(!empty($settings[\App\Models\SiteSettings::SITE_FAVICON]))
                            <div class="flex items-center justify-between gap-4 p-4 mb-5 bg-cream/50 border border-gold/20 rounded-xl">
                                <div class="flex items-center gap-4 min-w-0">
                                    <img src="{{ asset('storage/' . $settings[\App\Models\SiteSettings::SITE_FAVICON]) }}"
                                        alt="Текущий фавикон" class="w-8 h-8 object-contain rounded">
                                    <div>
                                        <p class="text-sm font-semibold text-dark">Текущий фавикон</p>
                                        <p class="text-xs text-warm-gray">Загружен и отображается во вкладке браузера</p>
                                    </div>
                                </div>
                                <button type="button"
                                    @click="$dispatch('confirm-delete-favicon', { action: '{{ route('admin.settings.favicon.delete') }}' })"
                                    class="shrink-0 flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 border border-red-200 rounded-lg transition-colors">
                                    <i class="fas fa-trash-alt"></i> Удалить
                                </button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('admin.settings.favicon') }}" enctype="multipart/form-data">
                            @csrf

                            <div class="mb-5">
                                <label class="block text-xs font-semibold text-warm-gray uppercase tracking-wider mb-2">
                                    Новый фавикон
                                </label>

                                <label for="site_favicon"
                                    class="flex flex-col items-center justify-center w-full border-2 border-dashed rounded-xl cursor-pointer transition-colors
                                           {{ $errors->has('site_favicon') ? 'border-red-300 bg-red-50/30' : 'border-primary/20 hover:border-gold/50 bg-cream/30 hover:bg-cream/60' }}"
                                    style="min-height: 120px;"
                                    x-data="{ name: '', preview: null }">
                                    <input type="file" id="site_favicon" name="site_favicon"
                                        accept=".ico,.png,.jpg,.jpeg,.svg" class="hidden"
                                        @change="name = $event.target.files[0]?.name || ''; preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">

                                    <div class="flex flex-col items-center py-5 px-4 text-center pointer-events-none">
                                        <template x-if="preview">
                                            <img :src="preview" class="w-8 h-8 mb-2 object-contain" alt="">
                                        </template>
                                        <template x-if="!preview">
                                            <i class="fas fa-cloud-arrow-up text-3xl text-warm-gray mb-2"></i>
                                        </template>
                                        <p class="text-sm font-medium text-dark" x-text="name || 'Нажмите или перетащите файл'"></p>
                                        <p class="text-xs text-warm-gray mt-1">ICO, PNG, JPG, SVG · до 512 КБ</p>
                                    </div>
                                </label>

                                @error('site_favicon')
                                    <p class="mt-1.5 text-xs text-red-600"><i class="fas fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gold/10">
                                <button type="reset"
                                    class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-warm-gray hover:text-dark border border-primary/15 rounded-lg transition-colors">
                                    <i class="fas fa-rotate-left text-xs"></i> Сбросить
                                </button>
                                <button type="submit"
                                    class="flex items-center gap-2 px-5 py-2 gradient-gold text-dark font-semibold text-sm rounded-lg hover:opacity-90 transition-opacity">
                                    <i class="fas fa-floppy-disk text-xs"></i> Сохранить
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>{{-- /tab branding --}}

            {{-- ── TAB: App Settings ── --}}
            <div x-show="tab === 'app-settings'" x-cloak>
                <div class="bg-white rounded-xl shadow-sm border border-gold/10 overflow-hidden">
                    <div class="flex items-center gap-3 px-6 py-4 border-b border-gold/10 bg-cream/30">
                        <div class="w-9 h-9 rounded-lg bg-purple-100 flex items-center justify-center shrink-0">
                            <i class="fas fa-trophy text-purple-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-dark text-sm">Конкурсы</p>
                            <p class="text-xs text-warm-gray">Параметры по умолчанию для новых конкурсов</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.settings.contest-settings') }}" class="p-6">
                        @csrf
                        <div class="max-w-sm">
                            <label for="default_application_limit" class="block text-sm font-medium text-dark mb-2">
                                Лимит заявок для новых бесплатных конкурсов
                            </label>
                            <input type="number" id="default_application_limit" name="default_application_limit"
                                value="{{ old('default_application_limit', $settings[\App\Models\SiteSettings::DEFAULT_APPLICATION_LIMIT] ?? '50') }}"
                                min="1" max="50"
                                class="no-spinner w-full px-4 py-2.5 border border-primary/20 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 bg-cream/30 text-dark">
                            @error('default_application_limit')
                                <p class="mt-1.5 text-xs text-red-600"><i class="fas fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                            @enderror
                            <p class="mt-1.5 text-xs text-warm-gray">Значение от 1 до 50. Это значение будет подставляться по умолчанию при создании нового бесплатного конкурса.</p>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-6 border-t border-gold/10 mt-6">
                            <button type="reset"
                                class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-warm-gray hover:text-dark border border-primary/15 rounded-lg transition-colors">
                                <i class="fas fa-rotate-left text-xs"></i> Сбросить
                            </button>
                            <button type="submit"
                                class="flex items-center gap-2 px-5 py-2 gradient-gold text-dark font-semibold text-sm rounded-lg hover:opacity-90 transition-opacity">
                                <i class="fas fa-floppy-disk text-xs"></i> Сохранить
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    {{-- Keep tab active after redirect --}}
    @if(request('tab'))
    <script>
        document.addEventListener('alpine:init', () => {
            // tab already set via x-data initial value
        });
    </script>
    @endif

</x-app-layout>
