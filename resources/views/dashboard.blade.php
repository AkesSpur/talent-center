<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash-message />

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-2">
                        {{ __('Welcome, :name!', ['name' => auth()->user()->first_name]) }}
                    </h3>
                    <p class="text-gray-600">
                        {{ __('This is your participant dashboard. Contest browsing and application features coming in Stage 2 & 3.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
