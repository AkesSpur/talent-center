<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark">Фоны дипломов</h2>
                <p class="text-warm-gray mt-1">Управление фоновыми изображениями для дипломов</p>
            </div>
            <button type="button" onclick="document.getElementById('modal-create').classList.remove('hidden')"
                class="inline-flex items-center px-5 py-2.5 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity text-sm">
                <i class="fas fa-plus mr-2"></i>Загрузить фон
            </button>
        </div>
    </x-slot>

    <x-confirm-modal
        name="delete-bg"
        title="Удалить фон"
        message="Вы уверены, что хотите удалить этот фон? Уже созданные конкурсы с этим фоном не пострадают."
        icon="fa-trash"
        iconColor="text-red-600"
        iconBg="bg-red-100"
        confirmText="Удалить"
        confirmClass="bg-red-600 text-white hover:bg-red-700"
        method="DELETE"
    />

    <div x-data="{ fullscreenUrl: null }" class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <x-notify />

            @if($backgrounds->count())
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($backgrounds as $bg)
                        <div class="bg-white rounded-xl shadow-sm border border-gold/10 overflow-hidden">
                            {{-- Thumbnail --}}
                            <div class="relative group cursor-pointer aspect-[297/210]"
                                 @click="fullscreenUrl = '{{ asset('storage/' . $bg->image_path) }}'">
                                <img src="{{ asset('storage/' . $bg->image_path) }}"
                                     class="w-full h-full object-cover" alt="{{ $bg->name ?? 'Фон диплома' }}" />
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors flex items-center justify-center">
                                    <i class="fas fa-search-plus text-white text-2xl opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                </div>
                            </div>

                            {{-- Info --}}
                            <div class="p-4">
                                <p class="font-medium text-dark text-sm mb-2">{{ $bg->name ?: 'Без названия' }}</p>

                                {{-- Genre tags --}}
                                <div class="flex flex-wrap gap-1 mb-3">
                                    @forelse($bg->platformCategories as $cat)
                                        <span class="text-xs bg-primary/10 text-primary px-2 py-0.5 rounded-full">{{ $cat->name }}</span>
                                    @empty
                                        <span class="text-xs text-warm-gray italic">Все жанры</span>
                                    @endforelse
                                </div>

                                {{-- Actions --}}
                                <div class="flex items-center gap-2">
                                    <button type="button"
                                        onclick="openEditBg({{ $bg->id }}, '{{ addslashes($bg->name ?? '') }}', {{ json_encode($bg->platformCategories->pluck('id')->toArray()) }})"
                                        class="flex-1 px-3 py-1.5 border border-primary/20 text-primary text-xs rounded-lg hover:bg-primary/5 transition-colors text-center">
                                        <i class="fas fa-pen mr-1"></i>Изменить
                                    </button>
                                    <button type="button"
                                        @click="$dispatch('confirm-delete-bg', { action: '{{ route('admin.diploma-backgrounds.destroy', $bg) }}' })"
                                        class="px-3 py-1.5 border border-red-200 text-red-500 text-xs rounded-lg hover:bg-red-50 transition-colors">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                    <div class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-image text-3xl text-primary"></i>
                    </div>
                    <h3 class="font-serif text-xl font-semibold text-dark mb-2">Фонов нет</h3>
                    <p class="text-warm-gray mb-6">Загрузите первый фоновый рисунок для дипломов.</p>
                </div>
            @endif
        </div>

        {{-- Fullscreen preview overlay --}}
        <div x-show="fullscreenUrl" x-cloak
             @click="fullscreenUrl = null"
             @keydown.escape.window="fullscreenUrl = null"
             class="fixed inset-0 z-50 bg-black/90 flex items-center justify-center p-4 cursor-pointer">
            <button @click="fullscreenUrl = null" class="absolute top-4 right-4 text-white text-2xl hover:text-gold z-10">
                <i class="fas fa-times"></i>
            </button>
            <img :src="fullscreenUrl" class="max-w-full max-h-full object-contain rounded-lg shadow-2xl" @click.stop />
        </div>
    </div>

    {{-- Create Modal --}}
    <div id="modal-create" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md">
            <div class="p-6 border-b border-gold/10 flex items-center justify-between">
                <h3 class="font-serif text-lg font-semibold text-dark">Новый фон диплома</h3>
                <button onclick="document.getElementById('modal-create').classList.add('hidden')" class="text-warm-gray hover:text-dark">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.diploma-backgrounds.store') }}" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-dark mb-2">Название</label>
                    <input type="text" name="name" maxlength="255"
                        class="w-full px-4 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm"
                        placeholder="Например: Золотой фон">
                </div>
                <div>
                    <label class="block text-sm font-medium text-dark mb-2">Изображение <span class="text-red-500">*</span></label>
                    <input type="file" name="image" accept="image/*" required
                        class="w-full text-sm text-warm-gray file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary/10 file:text-primary hover:file:bg-primary/20" />
                    <p class="text-xs text-warm-gray mt-1">JPG, PNG, WebP — до 4 МБ. Рекомендуется A4 горизонтальный.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-dark mb-2">Жанры</label>
                    <div class="max-h-40 overflow-y-auto border border-primary/10 rounded-lg p-3 space-y-2">
                        @foreach($platformCategories as $cat)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="platform_category_ids[]" value="{{ $cat->id }}"
                                    class="rounded border-primary/30 text-primary">
                                <span class="text-sm text-dark">{{ $cat->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p class="text-xs text-warm-gray mt-1">Если не выбрано — фон доступен для всех жанров.</p>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="px-5 py-2.5 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 text-sm">
                        Загрузить
                    </button>
                    <button type="button" onclick="document.getElementById('modal-create').classList.add('hidden')"
                        class="px-5 py-2.5 border border-primary/20 text-warm-gray rounded-lg hover:border-primary/40 text-sm">
                        Отмена
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div id="modal-edit-bg" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md">
            <div class="p-6 border-b border-gold/10 flex items-center justify-between">
                <h3 class="font-serif text-lg font-semibold text-dark">Редактировать фон</h3>
                <button onclick="document.getElementById('modal-edit-bg').classList.add('hidden')" class="text-warm-gray hover:text-dark">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="form-edit-bg" method="POST" action="" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-dark mb-2">Название</label>
                    <input id="edit-bg-name" type="text" name="name" maxlength="255"
                        class="w-full px-4 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-dark mb-2">Заменить изображение</label>
                    <input type="file" name="image" accept="image/*"
                        class="w-full text-sm text-warm-gray file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary/10 file:text-primary hover:file:bg-primary/20" />
                    <p class="text-xs text-warm-gray mt-1">Оставьте пустым, чтобы сохранить текущее изображение.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-dark mb-2">Жанры</label>
                    <div class="max-h-40 overflow-y-auto border border-primary/10 rounded-lg p-3 space-y-2">
                        @foreach($platformCategories as $cat)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="platform_category_ids[]" value="{{ $cat->id }}"
                                    class="rounded border-primary/30 text-primary edit-bg-cat" data-cat-id="{{ $cat->id }}">
                                <span class="text-sm text-dark">{{ $cat->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="px-5 py-2.5 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 text-sm">
                        Сохранить
                    </button>
                    <button type="button" onclick="document.getElementById('modal-edit-bg').classList.add('hidden')"
                        class="px-5 py-2.5 border border-primary/20 text-warm-gray rounded-lg hover:border-primary/40 text-sm">
                        Отмена
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openEditBg(id, name, catIds) {
        document.getElementById('edit-bg-name').value = name;
        document.getElementById('form-edit-bg').action = '/admin/diploma-backgrounds/' + id;
        document.querySelectorAll('.edit-bg-cat').forEach(cb => {
            cb.checked = catIds.includes(parseInt(cb.dataset.catId));
        });
        document.getElementById('modal-edit-bg').classList.remove('hidden');
    }
    </script>
</x-app-layout>
