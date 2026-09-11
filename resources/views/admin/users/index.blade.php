@extends('layouts.admin')

@section('title', 'User Accounts & Roles')
@section('header_title', 'Access Control (RBAC) & User Management')

@section('content')
<div class="space-y-8">

    {{-- Explainer Header --}}
    <div class="bg-white border border-[#111111]/15 p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h3 class="font-bold text-sm text-[#111111]">Role-Based Access Control Architecture</h3>
            <p class="text-xs font-mono text-[#808080] mt-1">
                Enforce granular privileges across Super Admin, Admin, Editor, and Author tiers using Spatie RBAC.
            </p>
        </div>
        <span class="text-xs font-mono px-3 py-1.5 bg-[#F8F8F6] border border-[#111111]/15">
            {{ $users->total() }} Total Accounts
        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        {{-- Left: Create or Edit User Form (5 cols) --}}
        <div class="lg:col-span-5 bg-white border border-[#111111]/15 p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-[#111111]/10 pb-3">
                <h3 class="font-bold text-sm text-[#111111]">{{ $editingUser ? 'Edit User: ' . $editingUser->name : 'Create New User Account' }}</h3>
                @if ($editingUser)
                    <a href="{{ route('admin.users.index') }}" class="text-xs font-mono text-[#808080] hover:text-[#111111]">Cancel</a>
                @endif
            </div>

            <form action="{{ $editingUser ? route('admin.users.update', $editingUser->id) : route('admin.users.store') }}" method="POST" class="space-y-4">
                @csrf
                @if ($editingUser)
                    @method('PUT')
                @endif

                <div>
                    <label for="name" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">Full Name *</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $editingUser->name ?? '') }}" required
                        class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                        placeholder="e.g. John Doe">
                </div>

                <div>
                    <label for="email" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">Email Address *</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $editingUser->email ?? '') }}" required
                        class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                        placeholder="user@securofi.tech">
                </div>

                <div>
                    <label for="password" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">
                        Password {{ $editingUser ? '(Leave blank to retain current)' : '*' }}
                    </label>
                    <input type="password" id="password" name="password" {{ $editingUser ? '' : 'required' }} minlength="8"
                        class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                        placeholder="••••••••••••">
                </div>

                <div>
                    <label for="role" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">Assigned System Role *</label>
                    <select id="role" name="role" required class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                        @php
                            $currentRole = old('role', $editingUser ? $editingUser->roles->pluck('name')->first() : 'Author');
                        @endphp
                        @foreach ($roles as $r)
                            <option value="{{ $r->name }}" {{ $currentRole === $r->name ? 'selected' : '' }}>
                                {{ $r->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[10px] font-mono text-[#808080] mt-1">
                        Super Admin: Full platform control • Admin: Articles & Monetization • Editor: Content & Publishing • Author: Draft submissions.
                    </p>
                </div>

                <div class="pt-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $editingUser->is_active ?? true) ? 'checked' : '' }}
                            class="w-4 h-4 rounded-none border-[#111111] text-[#111111]">
                        <span class="text-xs font-mono font-bold text-[#111111]">Active Account (Permit Authentication)</span>
                    </label>
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn-primary w-full text-xs font-mono uppercase tracking-wider py-3">
                        {{ $editingUser ? 'Update User Account →' : 'Create User Account →' }}
                    </button>
                </div>
            </form>
        </div>

        {{-- Right: Users Table (7 cols) --}}
        <div class="lg:col-span-7 bg-white border border-[#111111]/15 p-6 space-y-4">
            <h3 class="font-bold text-sm text-[#111111] border-b border-[#111111]/10 pb-3">Authorized User Directory</h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-mono">
                    <thead>
                        <tr class="border-b border-[#111111]/15 text-[#808080] uppercase tracking-wider text-[10px]">
                            <th class="py-2.5">User</th>
                            <th class="py-2.5">Role</th>
                            <th class="py-2.5">2FA</th>
                            <th class="py-2.5">Status</th>
                            <th class="py-2.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#111111]/10">
                        @foreach ($users as $user)
                            <tr class="hover:bg-[#F5F1E8]/40 {{ $editingUser && $editingUser->id === $user->id ? 'bg-[#F5F1E8]' : '' }}">
                                <td class="py-3 font-medium text-[#111111]">
                                    <div class="font-bold flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-none bg-primary text-secondary flex items-center justify-center text-[10px] font-bold">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                        <span>{{ $user->name }}</span>
                                    </div>
                                    <div class="text-[10px] text-[#808080] ml-8">{{ $user->email }}</div>
                                </td>
                                <td class="py-3">
                                    @php
                                        $roleName = $user->roles->pluck('name')->first() ?? 'No Role';
                                        $badgeColor = match($roleName) {
                                            'Super Admin' => 'bg-purple-100 text-purple-800 border-purple-200',
                                            'Admin' => 'bg-blue-100 text-blue-800 border-blue-200',
                                            'Editor' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                            default => 'bg-slate-100 text-slate-800 border-slate-200',
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 text-[9px] uppercase font-bold border {{ $badgeColor }}">
                                        {{ $roleName }}
                                    </span>
                                </td>
                                <td class="py-3 text-[10px] text-[#808080]">
                                    @if ($user->hasTwoFactorEnabled())
                                        <span class="text-emerald-700 font-bold">● Active</span>
                                    @else
                                        <span class="text-neutral">Not set</span>
                                    @endif
                                </td>
                                <td class="py-3">
                                    <span class="px-2 py-0.5 text-[9px] uppercase font-bold {{ $user->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ $user->is_active ? 'Active' : 'Disabled' }}
                                    </span>
                                </td>
                                <td class="py-3 text-right space-x-2">
                                    <a href="{{ route('admin.users.index', ['edit' => $user->id]) }}" class="text-[#111111] font-bold hover:underline">Edit</a>
                                    @if ($user->id !== auth()->id())
                                        <span>•</span>
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Permanently delete account for {{ $user->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-600 hover:underline">Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="pt-4 border-t border-[#111111]/10">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

    </div>

</div>
@endsection
