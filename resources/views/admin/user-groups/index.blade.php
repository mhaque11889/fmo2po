@extends('layouts.app')

@section('title', ($type === 'fmo' ? 'FMO' : 'PO') . ' User Groups')

@section('content')
<div class="flex flex-wrap gap-3 justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-900">{{ $type === 'fmo' ? 'FMO' : 'PO' }} User Groups</h1>
    <a href="{{ route('admin.' . $type . '-groups.create') }}"
       class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition whitespace-nowrap">
        + Create Group
    </a>
</div>

@if(session('success'))
    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
        {{ session('error') }}
    </div>
@endif

{{-- Desktop table --}}
<div class="hidden md:block bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Group Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Members</th>
                    @if($type === 'fmo')
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Group Approver</th>
                    @endif
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created By</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($groups as $group)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-gray-900">{{ $group->name }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-500">{{ $group->description ?: '—' }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                {{ $group->members->count() }} {{ Str::plural('member', $group->members->count()) }}
                            </span>
                        </td>
                        @if($type === 'fmo')
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($group->groupApprover)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">
                                    {{ $group->groupApprover->name }}
                                </span>
                            @else
                                <span class="text-sm text-gray-400">—</span>
                            @endif
                        </td>
                        @endif
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-500">{{ $group->creator->name ?? '—' }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('admin.' . $type . '-groups.edit', $group) }}"
                                   class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                    Edit
                                </a>
                                <form action="{{ route('admin.' . $type . '-groups.destroy', $group) }}" method="POST"
                                      onsubmit="return confirm('Delete this group? Members will not be deleted, only their group membership.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-medium">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $type === 'fmo' ? 6 : 5 }}" class="px-6 py-10 text-center text-sm text-gray-400">
                            No groups yet. <a href="{{ route('admin.' . $type . '-groups.create') }}" class="text-indigo-600 hover:underline">Create one</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Mobile cards --}}
<div class="md:hidden space-y-3">
    @forelse($groups as $group)
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex justify-between items-start mb-2">
                <span class="text-sm font-semibold text-gray-900">{{ $group->name }}</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                    {{ $group->members->count() }} {{ Str::plural('member', $group->members->count()) }}
                </span>
            </div>
            @if($group->description)
                <p class="text-xs text-gray-500 mb-2">{{ $group->description }}</p>
            @endif
            @if($type === 'fmo' && $group->groupApprover)
                <p class="text-xs text-indigo-600 mb-1">Approver: {{ $group->groupApprover->name }}</p>
            @endif
            <p class="text-xs text-gray-400 mb-3">Created by: {{ $group->creator->name ?? '—' }}</p>
            <div class="flex gap-4">
                <a href="{{ route('admin.' . $type . '-groups.edit', $group) }}"
                   class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                    Edit
                </a>
                <form action="{{ route('admin.' . $type . '-groups.destroy', $group) }}" method="POST"
                      onsubmit="return confirm('Delete this group? Members will not be deleted, only their group membership.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-medium">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-lg shadow p-6 text-center text-sm text-gray-400">
            No groups yet. <a href="{{ route('admin.' . $type . '-groups.create') }}" class="text-indigo-600 hover:underline">Create one</a>.
        </div>
    @endforelse
</div>

<div class="mt-4">
    <a href="{{ route('dashboard') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back to Dashboard</a>
</div>
@endsection
