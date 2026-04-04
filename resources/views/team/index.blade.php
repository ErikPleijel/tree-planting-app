<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Your Team') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6 justify-items-center">
                @foreach ($users as $user)
                    <div class="bg-white rounded-lg shadow p-4 flex flex-col items-center text-center w-full">

                        {{-- Profile picture or placeholder --}}
                        @if ($user->profile_picture_path)
                            <img src="{{ asset('storage/' . $user->profile_picture_path) }}"
                                 alt="{{ $user->name }}"
                                 class="w-20 h-24 object-cover rounded-md border border-gray-200 mb-3">
                        @else
                            <div class="w-20 h-24 rounded-md border border-gray-200 bg-gray-100 flex items-center justify-center mb-3">
                                <svg class="w-10 h-10 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
                                </svg>
                            </div>
                        @endif

                        {{-- Name --}}
                        <p class="font-semibold text-gray-900 text-sm leading-tight">{{ $user->name }}</p>

                        {{-- Role badge --}}
                        @php
                            $roleColors = [
                                'SuperAdmin' => 'bg-purple-100 text-purple-800',
                                'Admin'      => 'bg-blue-100 text-blue-800',
                                'Monitor'    => 'bg-green-100 text-green-800',
                                'Grower'     => 'bg-yellow-100 text-yellow-800',
                            ];
                            $color = $roleColors[$user->role_name] ?? 'bg-gray-100 text-gray-600';
                        @endphp
                        <span class="mt-1 px-2 py-0.5 rounded-full text-xs font-medium {{ $color }}">
                            {{ $user->role_name ?? 'No role' }}
                        </span>

                        {{-- Country --}}
                        @if ($user->country)
                            <p class="mt-2 text-xs text-gray-500">{{ $user->country }}</p>
                        @endif

                        {{-- Email --}}
                        <p class="mt-1 text-xs text-gray-600 break-all">{{ $user->email }}</p>

                        {{-- Telephone --}}
                        @if ($user->telephone)
                            <p class="mt-1 text-xs text-gray-500">{{ $user->telephone }}</p>
                        @endif

                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-8 flex justify-center">
                {{ $users->links() }}
            </div>

        </div>
    </div>
</x-app-layout>