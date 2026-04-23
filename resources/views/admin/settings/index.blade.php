@extends('layouts.admin')

@section('title', 'Settings')
@section('heading', 'Settings')

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Change Password --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-900">Change Your Password</h2>
            <p class="text-xs text-gray-400 mt-0.5">Update the password for your own admin account.</p>
        </div>
        <form method="POST" action="{{ route('admin.settings.password') }}" class="px-6 py-5 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                <input type="password" name="current_password" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent
                              @error('current_password') border-red-400 @enderror">
                @error('current_password')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                <input type="password" name="password" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent
                              @error('password') border-red-400 @enderror">
                @error('password')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                <input type="password" name="password_confirmation" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
            </div>

            <div class="pt-1">
                <button type="submit"
                        class="px-5 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                    Update Password
                </button>
            </div>
        </form>
    </div>

    {{-- Add New Manager --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-900">Add Admin Manager</h2>
            <p class="text-xs text-gray-400 mt-0.5">Create a new admin account with full panel access.</p>
        </div>
        <form method="POST" action="{{ route('admin.settings.managers.store') }}" class="px-6 py-5 space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent
                              @error('name') border-red-400 @enderror"
                       placeholder="John Doe">
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent
                              @error('email') border-red-400 @enderror"
                       placeholder="admin@example.com">
                @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent
                              @error('password') border-red-400 @enderror">
                @error('password')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                <input type="password" name="password_confirmation" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
            </div>

            <div class="pt-1">
                <button type="submit"
                        class="px-5 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                    Add Manager
                </button>
            </div>
        </form>
    </div>

</div>

{{-- Current Managers --}}
<div class="bg-white rounded-xl border border-gray-200 mt-6">
    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="font-semibold text-gray-900">Admin Managers</h2>
        <p class="text-xs text-gray-400 mt-0.5">All accounts with admin panel access.</p>
    </div>

    @if($managers->isEmpty())
        <div class="px-6 py-10 text-center text-gray-400 text-sm">No admin accounts found.</div>
    @else
        <div class="divide-y divide-gray-100">
            @foreach($managers as $manager)
            <div class="flex flex-wrap items-center justify-between gap-3 px-4 sm:px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                        <span class="text-sm font-bold text-green-700">{{ strtoupper($manager->name[0]) }}</span>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">
                            {{ $manager->name }}
                            @if($manager->id === auth()->id())
                                <span class="ml-1 text-[10px] bg-green-100 text-green-700 font-semibold px-1.5 py-0.5 rounded-full">You</span>
                            @endif
                        </p>
                        <p class="text-xs text-gray-400">{{ $manager->email }}</p>
                    </div>
                </div>

                @if($manager->id !== auth()->id())
                    <form method="POST" action="{{ route('admin.settings.managers.destroy', $manager) }}"
                          onsubmit="return confirm('Remove admin access for {{ addslashes($manager->name) }}? This cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="text-xs text-red-500 hover:text-red-700 font-medium transition-colors px-3 py-1.5 border border-red-200 rounded-lg hover:border-red-400">
                            Remove
                        </button>
                    </form>
                @else
                    <span class="text-xs text-gray-300 italic">current session</span>
                @endif
            </div>
            @endforeach
        </div>
    @endif
</div>

@endsection
