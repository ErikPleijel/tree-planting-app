<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Edit User') }}: {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-8 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('users.update', $user) }}" class="flex flex-col items-center">
                        @csrf
                        @method('PUT')

                        <div class="mb-6 text-center w-full">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">User Information</h3>
                            <p class="text-sm text-gray-600">
                                Name: {{ $user->name }}<br>
                                Email: {{ $user->email }}
                            </p>
                        </div>

<div class="mb-8 w-full max-w-md">
    <label class="block text-sm font-medium text-gray-700 text-center mb-4">Role</label>
    <div class="mt-2 space-y-3 flex flex-col items-center">
        <div class="flex items-center w-48">
            <input type="radio"
                   name="role"
                   value=""
                   id="role_none"
                   @checked(!$user->roles->count())
                   class="border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            <label for="role_none" class="ml-2 text-sm text-gray-600">
                No role
            </label>
        </div>
        @foreach($roles as $role)
            <div class="flex items-center w-48">
                <input type="radio"
                       name="role"
                       value="{{ $role->name }}"
                       id="role_{{ $role->id }}"
                       @checked($user->hasRole($role->name))
                       @disabled($role->name === 'Admin' && !$canEditAdmin)
                       class="border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50
                              {{ $role->name === 'Admin' && !$canEditAdmin ? 'opacity-50 cursor-not-allowed' : '' }}">
                <label for="role_{{ $role->id }}" class="ml-2 text-sm text-gray-600
                       {{ $role->name === 'Admin' && !$canEditAdmin ? 'opacity-50 cursor-not-allowed' : '' }}">
                    {{ $role->name }}
                </label>
            </div>
        @endforeach
    </div>
    @error('role')
        <p class="mt-1 text-sm text-red-600 text-center">{{ $message }}</p>
    @enderror
</div>

                        <div class="flex items-center justify-center space-x-4">
                            <a href="{{ route('users.report') }}"
                               class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-transparent rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                Cancel
                            </a>
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
