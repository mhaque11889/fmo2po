@extends('layouts.app')

@section('title', 'Reports')

@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')
<div class="mb-4">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">Reports</h1>
        <a href="{{ route('dashboard') }}" class="text-indigo-600 hover:text-indigo-900 text-sm">&larr; Back to Dashboard</a>
    </div>
</div>

{{-- Status Summary Cards --}}
<div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3 mb-5">
    @php
        $cards = [
            ['label' => 'All',          'key' => 'all',         'bg' => 'bg-white',       'text' => 'text-gray-900'],
            ['label' => 'Pending',      'key' => 'pending',     'bg' => 'bg-yellow-50',   'text' => 'text-yellow-700'],
            ['label' => 'Approved',     'key' => 'approved',    'bg' => 'bg-blue-50',     'text' => 'text-blue-700'],
            ['label' => 'Assigned',     'key' => 'assigned',    'bg' => 'bg-purple-50',   'text' => 'text-purple-700'],
            ['label' => 'In Progress',  'key' => 'in_progress', 'bg' => 'bg-orange-50',   'text' => 'text-orange-700'],
            ['label' => 'Completed',    'key' => 'completed',   'bg' => 'bg-green-50',    'text' => 'text-green-700'],
            ['label' => 'Rejected',     'key' => 'rejected',    'bg' => 'bg-red-50',      'text' => 'text-red-700'],
        ];
    @endphp
    @foreach($cards as $card)
        <div class="{{ $card['bg'] }} rounded-lg shadow p-3 text-center">
            <p class="text-xs font-medium {{ $card['text'] }} opacity-70">{{ $card['label'] }}</p>
            <p class="text-2xl font-bold {{ $card['text'] }}">{{ $statusCounts[$card['key']] }}</p>
        </div>
    @endforeach
</div>

{{-- Filters --}}
<div class="bg-white rounded-lg shadow p-4 mb-5">
    <form action="{{ route('reports.index') }}" method="GET" class="flex flex-wrap items-end gap-3">
        <div class="w-full sm:w-auto">
            <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
            <select name="status" class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">All Statuses</option>
                @foreach(['pending','approved','rejected','assigned','in_progress','completed','cancelled','clarification_needed'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_', ' ', $s)) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="w-full sm:w-auto">
            <label class="block text-xs font-medium text-gray-600 mb-1">Category</label>
            <select name="category_id" class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="w-full sm:w-auto">
            <label class="block text-xs font-medium text-gray-600 mb-1">Created From</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                   class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div class="w-full sm:w-auto">
            <label class="block text-xs font-medium text-gray-600 mb-1">Created To</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                   class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition text-sm">
                Apply
            </button>
            <a href="{{ route('reports.index') }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-200 transition text-sm">
                Clear
            </a>
        </div>
    </form>
    @if(request()->hasAny(['status', 'category_id', 'date_from', 'date_to']))
        <div class="mt-3 flex flex-wrap gap-2 text-xs">
            <span class="text-gray-500">Filters:</span>
            @if(request('status'))
                <span class="px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700">{{ ucfirst(str_replace('_',' ',request('status'))) }}</span>
            @endif
            @if(request('category_id'))
                @php $activeCategory = $categories->firstWhere('id', request('category_id')); @endphp
                @if($activeCategory)
                    <span class="px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700">{{ $activeCategory->name }}</span>
                @endif
            @endif
            @if(request('date_from'))
                <span class="px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700">From {{ request('date_from') }}</span>
            @endif
            @if(request('date_to'))
                <span class="px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700">To {{ request('date_to') }}</span>
            @endif
        </div>
    @endif
</div>

{{-- Tab Navigation --}}
<div x-data="{ tab: '{{ request()->hasAny(['status','date_from','date_to']) ? 'all' : 'overview' }}' }" class="space-y-5">

    <div class="border-b border-gray-200">
        <nav class="flex gap-1 overflow-x-auto -mb-px">
            @php
                $tabs = [
                    ['id' => 'overview',  'label' => 'Overview'],
                    ['id' => 'category',  'label' => 'By Category'],
                    ['id' => 'trend',     'label' => 'Trend'],
                    ['id' => 'users',     'label' => 'By User'],
                    ['id' => 'all',       'label' => 'All Requests'],
                ];
            @endphp
            @foreach($tabs as $t)
                <button type="button"
                    @click="tab = '{{ $t['id'] }}'"
                    :class="tab === '{{ $t['id'] }}' ? 'border-indigo-600 text-indigo-600 border-b-2 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap px-4 py-3 text-sm border-b-2 transition focus:outline-none">
                    {{ $t['label'] }}
                </button>
            @endforeach
        </nav>
    </div>

    {{-- ================================================================
         TAB 1: OVERVIEW
    ================================================================ --}}
    <div x-show="tab === 'overview'" x-cloak>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
            {{-- Donut — by status --}}
            <div class="bg-white rounded-lg shadow p-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Requests by Status</h2>
                <div class="relative h-64">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
            {{-- Bar — by priority --}}
            <div class="bg-white rounded-lg shadow p-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Requests by Priority</h2>
                <div class="relative h-64">
                    <canvas id="priorityChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Top categories callout --}}
        @if($categoryStats->isNotEmpty())
        <div class="bg-white rounded-lg shadow p-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Top Categories by Volume</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($categoryStats->sortByDesc('total')->take(6) as $cat)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $cat['name'] }}</p>
                        <p class="text-xs text-gray-500">{{ $cat['completion_rate'] }}% completed</p>
                    </div>
                    <span class="text-2xl font-bold text-indigo-600">{{ $cat['total'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- ================================================================
         TAB 2: BY CATEGORY
    ================================================================ --}}
    <div x-show="tab === 'category'" x-cloak>
        <div class="bg-white rounded-lg shadow overflow-hidden">
            @if($categoryStats->isEmpty())
                <div class="p-10 text-center text-sm text-gray-400">No category data available for the selected filters.</div>
            @else
                {{-- Desktop table --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-5 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-5 py-3 text-center text-xs font-medium text-yellow-600 uppercase tracking-wider">Pending</th>
                                <th class="px-5 py-3 text-center text-xs font-medium text-blue-600 uppercase tracking-wider">Approved</th>
                                <th class="px-5 py-3 text-center text-xs font-medium text-purple-600 uppercase tracking-wider">Assigned/WIP</th>
                                <th class="px-5 py-3 text-center text-xs font-medium text-green-600 uppercase tracking-wider">Completed</th>
                                <th class="px-5 py-3 text-center text-xs font-medium text-red-600 uppercase tracking-wider">Rejected</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Completion Rate</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($categoryStats->sortByDesc('total') as $cat)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-5 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $cat['name'] }}</td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm text-center font-semibold text-gray-900">{{ $cat['total'] }}</td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm text-center text-yellow-700">{{ $cat['pending'] ?: '—' }}</td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm text-center text-blue-700">{{ $cat['approved'] ?: '—' }}</td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm text-center text-purple-700">{{ $cat['assigned'] ?: '—' }}</td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm text-center text-green-700 font-medium">{{ $cat['completed'] ?: '—' }}</td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm text-center text-red-700">{{ $cat['rejected'] ?: '—' }}</td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden" style="min-width:60px">
                                                <div class="bg-green-500 h-2 rounded-full" style="width:{{ $cat['completion_rate'] }}%"></div>
                                            </div>
                                            <span class="text-xs text-gray-600 whitespace-nowrap">{{ $cat['completion_rate'] }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{-- Mobile cards --}}
                <div class="md:hidden divide-y divide-gray-100">
                    @foreach($categoryStats->sortByDesc('total') as $cat)
                        <div class="p-4">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-semibold text-gray-900">{{ $cat['name'] }}</span>
                                <span class="text-lg font-bold text-indigo-600">{{ $cat['total'] }}</span>
                            </div>
                            <div class="flex flex-wrap gap-2 text-xs mb-2">
                                @if($cat['pending'])   <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded">{{ $cat['pending'] }} pending</span> @endif
                                @if($cat['completed']) <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded">{{ $cat['completed'] }} done</span> @endif
                                @if($cat['rejected'])  <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded">{{ $cat['rejected'] }} rejected</span> @endif
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-gray-100 rounded-full h-1.5">
                                    <div class="bg-green-500 h-1.5 rounded-full" style="width:{{ $cat['completion_rate'] }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500">{{ $cat['completion_rate'] }}%</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ================================================================
         TAB 3: TREND
    ================================================================ --}}
    <div x-show="tab === 'trend'" x-cloak>
        <div class="bg-white rounded-lg shadow p-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Requests Created — Last 12 Months</h2>
            <div class="relative" style="height:320px">
                <canvas id="trendChart"></canvas>
            </div>
        </div>

        {{-- Trend table beneath chart --}}
        <div class="bg-white rounded-lg shadow overflow-hidden mt-5">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requests Created</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bar</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @php $maxTrend = max(array_merge($trendValues, [1])); @endphp
                    @foreach(array_reverse($trendLabels) as $i => $label)
                        @php $val = array_reverse($trendValues)[$i]; @endphp
                        <tr>
                            <td class="px-6 py-3 text-sm text-gray-700">{{ $label }}</td>
                            <td class="px-6 py-3 text-sm font-semibold text-gray-900">{{ $val }}</td>
                            <td class="px-6 py-3">
                                <div class="bg-indigo-100 rounded-full h-2" style="width:{{ $val > 0 ? max(4, round(($val/$maxTrend)*100)) : 0 }}%; min-width:{{ $val > 0 ? '4px' : '0' }}">
                                    <div class="bg-indigo-500 h-2 rounded-full" style="width:100%"></div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ================================================================
         TAB 4: BY USER
    ================================================================ --}}
    <div x-show="tab === 'users'" x-cloak>
        <div class="space-y-5">
            {{-- FMO side --}}
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-indigo-50">
                    <h2 class="text-sm font-semibold text-indigo-800">FMO — Requesters</h2>
                </div>
                @if($fmoUsers->isEmpty())
                    <div class="p-6 text-center text-sm text-gray-400">No FMO users found.</div>
                @else
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                    <th class="px-5 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                                    <th class="px-5 py-3 text-center text-xs font-medium text-yellow-600 uppercase tracking-wider">Pending</th>
                                    <th class="px-5 py-3 text-center text-xs font-medium text-green-600 uppercase tracking-wider">Completed</th>
                                    <th class="px-5 py-3 text-center text-xs font-medium text-red-600 uppercase tracking-wider">Rejected</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($fmoUsers as $u)
                                    @if($u->submitted > 0)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                @if($u->avatar)
                                                    <img src="{{ $u->avatar }}" class="w-7 h-7 rounded-full">
                                                @else
                                                    <div class="w-7 h-7 rounded-full bg-indigo-100 flex items-center justify-center text-xs font-semibold text-indigo-600">{{ substr($u->name,0,1) }}</div>
                                                @endif
                                                <span class="text-sm font-medium text-gray-900">{{ $u->name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap text-xs text-gray-500">{{ ucfirst(str_replace('_',' ',$u->role)) }}</td>
                                        <td class="px-5 py-4 whitespace-nowrap text-sm text-center font-semibold text-gray-900">{{ $u->submitted }}</td>
                                        <td class="px-5 py-4 whitespace-nowrap text-sm text-center text-yellow-700">{{ $u->pending_count ?: '—' }}</td>
                                        <td class="px-5 py-4 whitespace-nowrap text-sm text-center text-green-700">{{ $u->completed_count ?: '—' }}</td>
                                        <td class="px-5 py-4 whitespace-nowrap text-sm text-center text-red-700">{{ $u->rejected_count ?: '—' }}</td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="md:hidden divide-y divide-gray-100">
                        @foreach($fmoUsers as $u)
                            @if($u->submitted > 0)
                            <div class="p-4">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm font-medium text-gray-900">{{ $u->name }}</span>
                                    <span class="text-lg font-bold text-indigo-600">{{ $u->submitted }}</span>
                                </div>
                                <p class="text-xs text-gray-400 mb-2">{{ ucfirst(str_replace('_',' ',$u->role)) }}</p>
                                <div class="flex flex-wrap gap-2 text-xs">
                                    @if($u->pending_count)   <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded">{{ $u->pending_count }} pending</span> @endif
                                    @if($u->completed_count) <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded">{{ $u->completed_count }} completed</span> @endif
                                    @if($u->rejected_count)  <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded">{{ $u->rejected_count }} rejected</span> @endif
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- PO side --}}
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-purple-50">
                    <h2 class="text-sm font-semibold text-purple-800">PO — Processors</h2>
                </div>
                @if($poUsers->isEmpty())
                    <div class="p-6 text-center text-sm text-gray-400">No PO users found.</div>
                @else
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                    <th class="px-5 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Total Assigned</th>
                                    <th class="px-5 py-3 text-center text-xs font-medium text-orange-600 uppercase tracking-wider">In Progress</th>
                                    <th class="px-5 py-3 text-center text-xs font-medium text-green-600 uppercase tracking-wider">Completed</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($poUsers as $u)
                                    @if($u->assigned_count > 0)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                @if($u->avatar)
                                                    <img src="{{ $u->avatar }}" class="w-7 h-7 rounded-full">
                                                @else
                                                    <div class="w-7 h-7 rounded-full bg-purple-100 flex items-center justify-center text-xs font-semibold text-purple-600">{{ substr($u->name,0,1) }}</div>
                                                @endif
                                                <span class="text-sm font-medium text-gray-900">{{ $u->name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap text-xs text-gray-500">{{ ucfirst(str_replace('_',' ',$u->role)) }}</td>
                                        <td class="px-5 py-4 whitespace-nowrap text-sm text-center font-semibold text-gray-900">{{ $u->assigned_count }}</td>
                                        <td class="px-5 py-4 whitespace-nowrap text-sm text-center text-orange-700">{{ $u->in_progress_count ?: '—' }}</td>
                                        <td class="px-5 py-4 whitespace-nowrap text-sm text-center text-green-700">{{ $u->completed_count ?: '—' }}</td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="md:hidden divide-y divide-gray-100">
                        @foreach($poUsers as $u)
                            @if($u->assigned_count > 0)
                            <div class="p-4">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm font-medium text-gray-900">{{ $u->name }}</span>
                                    <span class="text-lg font-bold text-purple-600">{{ $u->assigned_count }}</span>
                                </div>
                                <p class="text-xs text-gray-400 mb-2">{{ ucfirst(str_replace('_',' ',$u->role)) }}</p>
                                <div class="flex flex-wrap gap-2 text-xs">
                                    @if($u->in_progress_count) <span class="px-2 py-0.5 bg-orange-100 text-orange-700 rounded">{{ $u->in_progress_count }} in progress</span> @endif
                                    @if($u->completed_count)   <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded">{{ $u->completed_count }} completed</span> @endif
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ================================================================
         TAB 5: ALL REQUESTS
    ================================================================ --}}
    <div x-show="tab === 'all'" x-cloak>
        {{-- Export buttons --}}
        <div class="flex flex-wrap gap-2 mb-4">
            <a href="{{ route('reports.export', array_merge(request()->query(), ['format' => 'csv'])) }}"
               class="inline-flex items-center bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition text-sm">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export CSV
            </a>
            <a href="{{ route('reports.export', array_merge(request()->query(), ['format' => 'excel'])) }}"
               class="inline-flex items-center bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition text-sm">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export Excel
            </a>
            <span class="text-sm text-gray-400 self-center ml-1">{{ $requests->total() }} result(s)</span>
        </div>

        {{-- Desktop table --}}
        <div class="hidden md:block bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created By</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creation</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-blue-600 uppercase tracking-wider">Approved (FMO)</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-purple-600 uppercase tracking-wider">Assigned (PO)</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-green-600 uppercase tracking-wider">Completed (PO)</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($requests as $req)
                            @php
                                $statusColors = [
                                    'group_pending'        => 'bg-indigo-100 text-indigo-800',
                                    'pending'              => 'bg-yellow-100 text-yellow-800',
                                    'approved'             => 'bg-blue-100 text-blue-800',
                                    'rejected'             => 'bg-red-100 text-red-800',
                                    'assigned'             => 'bg-purple-100 text-purple-800',
                                    'in_progress'          => 'bg-orange-100 text-orange-800',
                                    'completed'            => 'bg-green-100 text-green-800',
                                    'cancelled'            => 'bg-gray-100 text-gray-700',
                                    'clarification_needed' => 'bg-amber-100 text-amber-800',
                                ];
                                $color = $statusColors[$req->status] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-900">
                                    #{{ $req->id }}
                                    @if($req->priority === 'urgent')
                                        <span class="ml-1 px-1 py-0.5 text-xs font-semibold bg-red-100 text-red-700 rounded">Urgent</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-xs text-gray-500">{{ $req->category->name ?? '—' }}</td>
                                <td class="px-5 py-4 text-sm text-gray-900 max-w-xs">{{ $req->display_item }}</td>
                                <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-500">{{ $req->location }}</td>
                                <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-900">{{ $req->creator->name ?? '—' }}</td>
                                <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-500">{{ $req->assignee->name ?? '—' }}</td>
                                <td class="px-5 py-4 whitespace-nowrap text-xs text-gray-500">{{ $req->created_at?->format('d M Y') }}</td>
                                <td class="px-5 py-4 whitespace-nowrap text-xs text-blue-700">{{ $req->approved_at?->format('d M Y') ?? '—' }}</td>
                                <td class="px-5 py-4 whitespace-nowrap text-xs text-purple-700">{{ $req->assigned_at?->format('d M Y') ?? '—' }}</td>
                                <td class="px-5 py-4 whitespace-nowrap text-xs text-green-700">{{ $req->completed_at?->format('d M Y') ?? '—' }}</td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="px-2 py-0.5 inline-flex text-xs font-semibold rounded-full {{ $color }}">
                                        {{ ucfirst(str_replace('_', ' ', $req->status)) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-sm">
                                    <a href="{{ route('requests.show', $req) }}"
                                       class="px-3 py-1 border border-indigo-600 text-indigo-600 rounded-md hover:bg-indigo-600 hover:text-white transition text-xs">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="px-6 py-10 text-center text-sm text-gray-400">No requests match the current filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile cards --}}
        <div class="md:hidden space-y-3">
            @forelse($requests as $req)
                @php $color = $statusColors[$req->status] ?? 'bg-gray-100 text-gray-800'; @endphp
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-xs font-semibold text-gray-400">#{{ $req->id }}
                            @if($req->priority === 'urgent')
                                <span class="ml-1 px-1 py-0.5 text-xs font-semibold bg-red-100 text-red-700 rounded">Urgent</span>
                            @endif
                        </span>
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $statusColors[$req->status] ?? 'bg-gray-100 text-gray-700' }}">
                            {{ ucfirst(str_replace('_', ' ', $req->status)) }}
                        </span>
                    </div>
                    <p class="text-xs text-indigo-600 mb-1">{{ $req->category->name ?? '' }}</p>
                    <p class="text-sm font-semibold text-gray-900 mb-2">{{ $req->display_item }}</p>
                    <div class="flex flex-wrap gap-x-3 gap-y-1 text-xs text-gray-600 mb-3">
                        <span><b>Location:</b> {{ $req->location }}</span>
                        <span><b>By:</b> {{ $req->creator->name ?? '—' }}</span>
                        <span>{{ $req->created_at?->format('M d, Y') }}</span>
                    </div>
                    <a href="{{ route('requests.show', $req) }}"
                       class="inline-flex items-center px-3 py-1.5 border border-indigo-600 text-indigo-600 rounded-md hover:bg-indigo-600 hover:text-white transition text-sm">
                        View
                    </a>
                </div>
            @empty
                <div class="bg-white rounded-lg shadow p-4 text-center text-sm text-gray-400">No requests found.</div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $requests->links() }}
        </div>
    </div>

</div>{{-- end Alpine tabs --}}
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Status Donut Chart
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Approved', 'Assigned', 'In Progress', 'Completed', 'Rejected', 'Cancelled'],
                datasets: [{
                    data: [
                        {{ $statusCounts['pending'] }},
                        {{ $statusCounts['approved'] }},
                        {{ $statusCounts['assigned'] }},
                        {{ $statusCounts['in_progress'] }},
                        {{ $statusCounts['completed'] }},
                        {{ $statusCounts['rejected'] }},
                        {{ $statusCounts['cancelled'] }},
                    ],
                    backgroundColor: ['#fef08a','#bfdbfe','#ddd6fe','#fed7aa','#bbf7d0','#fecaca','#e5e7eb'],
                    borderColor:     ['#ca8a04','#2563eb','#7c3aed','#ea580c','#16a34a','#dc2626','#9ca3af'],
                    borderWidth: 1.5,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right', labels: { boxWidth: 12, font: { size: 12 } } },
                    tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed}` } }
                }
            }
        });
    }

    // Priority Bar Chart
    const priorityCtx = document.getElementById('priorityChart');
    if (priorityCtx) {
        new Chart(priorityCtx, {
            type: 'bar',
            data: {
                labels: ['Normal', 'Urgent'],
                datasets: [{
                    label: 'Requests',
                    data: [{{ $priorityData['normal'] }}, {{ $priorityData['urgent'] }}],
                    backgroundColor: ['#e0e7ff', '#fecaca'],
                    borderColor: ['#4f46e5', '#dc2626'],
                    borderWidth: 1.5,
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // Trend Line Chart
    const trendCtx = document.getElementById('trendChart');
    if (trendCtx) {
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: @json($trendLabels),
                datasets: [{
                    label: 'Requests Created',
                    data: @json($trendValues),
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79,70,229,0.08)',
                    borderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.3,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } },
                    x: { grid: { color: '#f3f4f6' } }
                }
            }
        });
    }
});
</script>
@endpush
