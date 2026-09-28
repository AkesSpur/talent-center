<x-app-layout>
    <x-slot name="header">
        <x-breadcrumbs :items="[
            ['label' => 'Поддержка', 'url' => route('admin.support.tickets.index')],
            ['label' => 'База знаний', 'url' => route('admin.support.articles.index')],
            ['label' => 'Новая статья'],
        ]" />
        <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark">Новая статья</h2>
        <p class="text-warm-gray mt-1">Черновик виден только сотрудникам, пока вы его не опубликуете</p>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-notify />

            @include('admin.support.articles._form', [
                'action'  => route('admin.support.articles.store'),
                'article' => null,
            ])
        </div>
    </div>
</x-app-layout>
