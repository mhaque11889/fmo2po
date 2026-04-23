@extends('layouts.app')

@section('title', 'Edit Group: ' . $userGroup->name)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Edit Group</h1>
        <a href="{{ route('admin.' . $userGroup->type . '-groups.index') }}"
           class="text-sm text-gray-500 hover:text-gray-700">← Back to Groups</a>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Group Details --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-base font-semibold text-gray-800 mb-4">Group Details</h2>
        <form action="{{ route('admin.' . $userGroup->type . '-groups.update', $userGroup) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                    Group Name <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name', $userGroup->name) }}"
                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-5">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                    Description <span class="text-gray-400 font-normal">(optional)</span>
                </label>
                <textarea id="description" name="description" rows="2"
                          class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description', $userGroup->description) }}</textarea>
            </div>

            @if($userGroup->type === 'fmo')
            <div class="mb-5">
                <label for="group_approver_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Group Approver <span class="text-gray-400 font-normal">(optional)</span>
                </label>
                <p class="text-xs text-gray-500 mb-2">
                    When set, requests from this group will require approval from this person before reaching the FMO Admin.
                    If the approver is also the request creator, their request skips this step.
                </p>
                <select id="group_approver_id" name="group_approver_id"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">— No group approver —</option>
                    @foreach($approverCandidates as $candidate)
                        <option value="{{ $candidate->id }}"
                            {{ old('group_approver_id', $userGroup->group_approver_id) == $candidate->id ? 'selected' : '' }}>
                            {{ $candidate->name }} ({{ $candidate->email }}) — {{ ucfirst(str_replace('_', ' ', $candidate->role)) }}
                        </option>
                    @endforeach
                </select>
                @error('group_approver_id')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            @endif

            @if($userGroup->type === 'fmo' && $categories->isNotEmpty())
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Category-Specific Approvers <span class="text-gray-400 font-normal">(optional)</span>
                </label>
                <p class="text-xs text-gray-500 mb-3">
                    Override the group approver for specific categories. If set, requests in that category will go to this person instead.
                </p>
                <div class="border border-gray-200 rounded-md overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase w-1/3">Category</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Approver</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($categories as $category)
                                @php
                                    $currentApproverId = $userGroup->categoryApprovers->firstWhere('category_id', $category->id)?->approver_id;
                                @endphp
                                <tr>
                                    <td class="px-4 py-2 font-medium text-gray-700">{{ $category->name }}</td>
                                    <td class="px-4 py-2">
                                        <select name="category_approvers[{{ $category->id }}]"
                                                class="w-full border border-gray-300 rounded-md px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                            <option value="">— Use group default —</option>
                                            @foreach($approverCandidates as $candidate)
                                                <option value="{{ $candidate->id }}"
                                                    {{ old("category_approvers.{$category->id}", $currentApproverId) == $candidate->id ? 'selected' : '' }}>
                                                    {{ $candidate->name }} ({{ $candidate->email }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <button type="submit"
                    class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition text-sm font-medium">
                Save Changes
            </button>
        </form>
    </div>

    {{-- Members --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-base font-semibold text-gray-800">
                Members
                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                    {{ $userGroup->members->count() }}
                </span>
            </h2>
        </div>

        @if($userGroup->members->isNotEmpty())
            {{-- Desktop table --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($userGroup->members as $member)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        @if($member->avatar)
                                            <img src="{{ $member->avatar }}" alt="Avatar" class="w-7 h-7 rounded-full">
                                        @else
                                            <div class="w-7 h-7 rounded-full bg-gray-200 flex items-center justify-center">
                                                <span class="text-gray-500 text-xs">{{ substr($member->name, 0, 1) }}</span>
                                            </div>
                                        @endif
                                        <span class="text-sm font-medium text-gray-900">{{ $member->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $member->email }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <form action="{{ route('admin.' . $userGroup->type . '-groups.members.remove', [$userGroup, $member]) }}"
                                          method="POST"
                                          onsubmit="return confirm('Remove {{ $member->name }} from this group?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                            Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile member list --}}
            <div class="md:hidden divide-y divide-gray-100">
                @foreach($userGroup->members as $member)
                    <div class="px-4 py-3 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2 min-w-0">
                            @if($member->avatar)
                                <img src="{{ $member->avatar }}" alt="Avatar" class="w-8 h-8 rounded-full flex-shrink-0">
                            @else
                                <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center flex-shrink-0">
                                    <span class="text-gray-500 text-xs">{{ substr($member->name, 0, 1) }}</span>
                                </div>
                            @endif
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-900">{{ $member->name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ $member->email }}</p>
                            </div>
                        </div>
                        <form action="{{ route('admin.' . $userGroup->type . '-groups.members.remove', [$userGroup, $member]) }}"
                              method="POST"
                              onsubmit="return confirm('Remove {{ $member->name }} from this group?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium whitespace-nowrap">
                                Remove
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @else
            <div class="px-6 py-8 text-center text-sm text-gray-400">
                No members yet. Add one below.
            </div>
        @endif

        {{-- Add Member --}}
        <div class="px-4 sm:px-6 py-4 bg-gray-50 border-t border-gray-200">
            <h3 class="text-sm font-medium text-gray-700 mb-3">Add Member</h3>
            @if($availableUsers->isNotEmpty())
                <form action="{{ route('admin.' . $userGroup->type . '-groups.members.add', $userGroup) }}"
                      method="POST"
                      class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    @csrf
                    <select name="user_id"
                            class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 flex-1">
                        <option value="">Select a user...</option>
                        @foreach($availableUsers as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                    <button type="submit"
                            class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition text-sm font-medium whitespace-nowrap">
                        Add to Group
                    </button>
                </form>
            @else
                <p class="text-sm text-gray-400">
                    All {{ $userGroup->type === 'fmo' ? 'FMO' : 'PO' }} users are currently assigned to a group.
                </p>
            @endif
        </div>
    </div>

</div>
@endsection
