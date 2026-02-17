<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Support Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash-message />

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">{{ __('Pending Organizations') }}</div>
                    <div class="mt-1 text-3xl font-semibold text-orange-600">{{ $pendingOrgsCount }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">{{ __('Total Users') }}</div>
                    <div class="mt-1 text-3xl font-semibold text-gray-900">{{ $usersCount }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">{{ __('Applications') }}</div>
                    <div class="mt-1 text-3xl font-semibold text-gray-900">{{ $applicationsCount }}</div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-2">{{ __('Moderation Panel') }}</h3>
                    <p class="text-gray-600">
                        {{ __('User/org moderation and application review features coming in Stage 2 & 3.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
