<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash-message />

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">{{ __('Users') }}</div>
                    <div class="mt-1 text-3xl font-semibold text-gray-900">{{ $usersCount }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">{{ __('Organizations') }}</div>
                    <div class="mt-1 text-3xl font-semibold text-gray-900">{{ $organizationsCount }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">{{ __('Contests') }}</div>
                    <div class="mt-1 text-3xl font-semibold text-gray-900">{{ $contestsCount }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">{{ __('Applications') }}</div>
                    <div class="mt-1 text-3xl font-semibold text-gray-900">{{ $applicationsCount }}</div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-2">{{ __('System Administration') }}</h3>
                    <p class="text-gray-600">
                        {{ __('Full admin panel features (user management, org verification, contest oversight) coming in Stage 2 & 3.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
