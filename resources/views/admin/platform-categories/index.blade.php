<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark">Категории конкурсов</h2>
                <p class="text-warm-gray mt-1">Управление категориями платформы</p>
            </div>
            <button type="button" onclick="document.getElementById('modal-create').classList.remove('hidden')"
                class="inline-flex items-center px-5 py-2.5 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity text-sm">
                <i class="fas fa-plus mr-2"></i>Добавить категорию
            </button>
        </div>
    </x-slot>

    <x-confirm-modal
        name="delete-category"
        title="Удалить категорию"
        message="Вы уверены, что хотите удалить эту категорию? Все привязанные конкурсы потеряют категорию."
        icon="fa-trash"
        iconColor="text-red-600"
        iconBg="bg-red-100"
        confirmText="Удалить"
        confirmClass="bg-red-600 text-white hover:bg-red-700"
        method="DELETE"
    />

    <div x-data class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <x-notify />

            @if($categories->count())
                <div class="bg-white rounded-xl shadow-sm border border-gold/10 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gold/10 text-warm-gray text-xs uppercase tracking-wider">
                                    <th class="text-left px-6 py-3 font-semibold w-16">Порядок</th>
                                    <th class="text-left px-6 py-3 font-semibold">Название</th>
                                    <th class="text-left px-6 py-3 font-semibold hidden md:table-cell">Описание</th>
                                    <th class="text-left px-6 py-3 font-semibold w-24">Статус</th>
                                    <th class="text-left px-6 py-3 font-semibold w-24">Конкурсов</th>
                                    <th class="text-right px-6 py-3 font-semibold">Действия</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gold/10">
                                @foreach($categories as $category)
                                    <tr class="hover:bg-cream/30 transition-colors">
                                        <td class="px-6 py-4 text-warm-gray text-center">{{ $category->sort_order }}</td>
                                        <td class="px-6 py-4 font-medium text-dark">{{ $category->name }}</td>
                                        <td class="px-6 py-4 hidden md:table-cell text-warm-gray">
                                            {{ Str::limit($category->description, 60) ?: '—' }}
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($category->is_active)
                                                <span class="inline-flex items-center text-xs font-medium px-2.5 py-1 rounded-full bg-green-100 text-green-700">
                                                    Активна
                                                </span>
                                            @else
                                                <span class="inline-flex items-center text-xs font-medium px-2.5 py-1 rounded-full bg-gray-100 text-gray-600">
                                                    Скрыта
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-warm-gray text-center">{{ $category->contests()->count() }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <button type="button"
                                                    onclick="openEdit({{ $category->id }}, '{{ addslashes($category->name) }}', '{{ addslashes($category->description ?? '') }}', {{ $category->sort_order }}, {{ $category->is_active ? 'true' : 'false' }})"
                                                    class="px-3 py-1.5 border border-primary/20 text-primary text-xs rounded-lg hover:bg-primary/5 transition-colors">
                                                    <i class="fas fa-pen mr-1"></i>Изменить
                                                </button>
                                                <button type="button"
                                                    @click="$dispatch('confirm-delete-category', { action: '{{ route('admin.platform-categories.destroy', $category) }}' })"
                                                    class="px-3 py-1.5 border border-red-200 text-red-500 text-xs rounded-lg hover:bg-red-50 transition-colors">
                                                    <i class="fas fa-trash mr-1"></i>Удалить
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                    <div class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-tags text-3xl text-primary"></i>
                    </div>
                    <h3 class="font-serif text-xl font-semibold text-dark mb-2">Категорий нет</h3>
                    <p class="text-warm-gray mb-6">Создайте первую категорию для группировки конкурсов.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Create Modal --}}
    <div id="modal-create" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md">
            <div class="p-6 border-b border-gold/10 flex items-center justify-between">
                <h3 class="font-serif text-lg font-semibold text-dark">Новая категория</h3>
                <button onclick="document.getElementById('modal-create').classList.add('hidden')" class="text-warm-gray hover:text-dark">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.platform-categories.store') }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-dark mb-2">Название <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required maxlength="255"
                        class="w-full px-4 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm"
                        placeholder="Изобразительное искусство">
                </div>
                <div>
                    <label class="block text-sm font-medium text-dark mb-2">Описание</label>
                    <textarea name="description" rows="3" maxlength="1000"
                        class="w-full px-4 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm resize-none"
                        placeholder="Краткое описание категории..."></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-dark mb-2">Порядок сортировки</label>
                        <input type="number" name="sort_order" value="0" min="0"
                            class="w-full px-4 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm">
                    </div>
                    <div class="flex items-end pb-0.5">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" checked
                                class="rounded border-primary/30 text-primary">
                            <span class="text-sm font-medium text-dark">Активна</span>
                        </label>
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="px-5 py-2.5 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 text-sm">
                        Создать
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
    <div id="modal-edit" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md">
            <div class="p-6 border-b border-gold/10 flex items-center justify-between">
                <h3 class="font-serif text-lg font-semibold text-dark">Редактировать категорию</h3>
                <button onclick="document.getElementById('modal-edit').classList.add('hidden')" class="text-warm-gray hover:text-dark">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="form-edit" method="POST" action="" class="p-6 space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-dark mb-2">Название <span class="text-red-500">*</span></label>
                    <input id="edit-name" type="text" name="name" required maxlength="255"
                        class="w-full px-4 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-dark mb-2">Описание</label>
                    <textarea id="edit-description" name="description" rows="3" maxlength="1000"
                        class="w-full px-4 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm resize-none"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-dark mb-2">Порядок сортировки</label>
                        <input id="edit-sort" type="number" name="sort_order" min="0"
                            class="w-full px-4 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm">
                    </div>
                    <div class="flex items-end pb-0.5">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input id="edit-active" type="checkbox" name="is_active" value="1"
                                class="rounded border-primary/30 text-primary">
                            <span class="text-sm font-medium text-dark">Активна</span>
                        </label>
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="px-5 py-2.5 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 text-sm">
                        Сохранить
                    </button>
                    <button type="button" onclick="document.getElementById('modal-edit').classList.add('hidden')"
                        class="px-5 py-2.5 border border-primary/20 text-warm-gray rounded-lg hover:border-primary/40 text-sm">
                        Отмена
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openEdit(id, name, description, sortOrder, isActive) {
        document.getElementById('edit-name').value = name;
        document.getElementById('edit-description').value = description;
        document.getElementById('edit-sort').value = sortOrder;
        document.getElementById('edit-active').checked = isActive;
        document.getElementById('form-edit').action = '/admin/platform-categories/' + id;
        document.getElementById('modal-edit').classList.remove('hidden');
    }
    </script>
</x-app-layout>
