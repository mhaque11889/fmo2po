@extends('layouts.app')

@section('title', 'Edit Request')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Edit Requirement Request #{{ $request->id }}</h1>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('requests.update', $request) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Category -->
            <div class="mb-6">
                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Category <span class="text-red-500">*</span>
                </label>
                <select name="category_id" id="category_id" required
                        class="w-full border border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 px-3 py-2 text-sm @error('category_id') border-red-500 @enderror">
                    <option value="">— Select a category —</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}"
                            {{ old('category_id', $request->category_id) == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Line Items Table -->
            <div class="mb-4">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-sm font-medium text-gray-700">
                        Items <span class="text-red-500">*</span>
                        <span class="text-gray-500 font-normal text-xs ml-1">(at least 1 required)</span>
                    </label>
                    <button type="button" id="add-item-btn"
                        class="px-3 py-1.5 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700 transition">
                        + Add Item
                    </button>
                </div>

                <div class="overflow-x-auto border border-gray-200 rounded-md">
                    <table class="min-w-full divide-y divide-gray-200" id="items-table">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-8">#</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item Name *</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-24">Qty *</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-40">Specifications</th>
                                <th class="px-3 py-2 w-10"></th>
                            </tr>
                        </thead>
                        <tbody id="items-tbody">
                            @forelse($request->items as $idx => $lineItem)
                                <tr class="item-row" data-index="{{ $idx }}">
                                    <td class="px-3 py-2 text-sm text-gray-500 row-num">{{ $idx + 1 }}</td>
                                    <td class="px-3 py-2">
                                        <input type="text" name="items[{{ $idx }}][item]" required
                                            value="{{ old('items.' . $idx . '.item', $lineItem->item) }}"
                                            class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" name="items[{{ $idx }}][qty]" min="1" required
                                            value="{{ old('items.' . $idx . '.qty', $lineItem->qty) }}"
                                            class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="text" name="items[{{ $idx }}][specifications]"
                                            value="{{ old('items.' . $idx . '.specifications', $lineItem->specifications) }}"
                                            placeholder="e.g. 10x20 cm"
                                            class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    </td>
                                    <td class="px-3 py-2">
                                        <button type="button" class="remove-row text-gray-300 hover:text-red-500 transition" title="Remove row">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr class="item-row" data-index="0">
                                    <td class="px-3 py-2 text-sm text-gray-500 row-num">1</td>
                                    <td class="px-3 py-2">
                                        <input type="text" name="items[0][item]" required
                                            value="{{ old('items.0.item') }}"
                                            class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" name="items[0][qty]" min="1" value="{{ old('items.0.qty', 1) }}" required
                                            class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="text" name="items[0][specifications]"
                                            value="{{ old('items.0.specifications') }}"
                                            placeholder="e.g. 10x20 cm"
                                            class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    </td>
                                    <td class="px-3 py-2">
                                        <button type="button" class="remove-row text-gray-300 hover:text-red-500 transition" title="Remove row">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <p id="items-min-error" class="mt-1 text-sm text-red-500 hidden">At least one item is required.</p>
                @error('items')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="location" class="block text-sm font-medium text-gray-700 mb-1">
                    Location <span class="text-red-500">*</span>
                </label>
                <input type="text" name="location" id="location" value="{{ old('location', $request->location) }}"
                    placeholder="e.g., Building A, Room 101"
                    class="w-full border border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 px-3 py-2 @error('location') border-red-500 @enderror"
                    required>
                @error('location')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Priority -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                <div class="flex gap-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="priority" value="normal"
                            {{ old('priority', $request->priority) === 'normal' ? 'checked' : '' }}
                            class="text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-gray-700">Normal</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="priority" value="urgent"
                            {{ old('priority', $request->priority) === 'urgent' ? 'checked' : '' }}
                            class="text-red-600 focus:ring-red-500">
                        <span class="text-sm font-medium text-red-600">Urgent</span>
                    </label>
                </div>
                @error('priority')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="remarks" class="block text-sm font-medium text-gray-700 mb-1">
                    Remarks
                </label>
                <textarea name="remarks" id="remarks" rows="3"
                    placeholder="Any additional information..."
                    class="w-full border border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 px-3 py-2 @error('remarks') border-red-500 @enderror">{{ old('remarks', $request->remarks) }}</textarea>
                @error('remarks')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('requests.show', $request) }}"
                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const itemsTbody = document.getElementById('items-tbody');
    const addItemBtn = document.getElementById('add-item-btn');
    let itemRowCount = {{ $request->items->count() > 0 ? $request->items->count() : 1 }};

    function buildItemRow(idx) {
        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.dataset.index = idx;
        tr.innerHTML = `
            <td class="px-3 py-2 text-sm text-gray-500 row-num"></td>
            <td class="px-3 py-2">
                <input type="text" name="items[${idx}][item]" required
                    class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            </td>
            <td class="px-3 py-2">
                <input type="number" name="items[${idx}][qty]" min="1" value="1" required
                    class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            </td>
            <td class="px-3 py-2">
                <input type="text" name="items[${idx}][specifications]" placeholder="e.g. 10x20 cm"
                    class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            </td>
            <td class="px-3 py-2">
                <button type="button" class="remove-row text-gray-300 hover:text-red-500 transition" title="Remove row">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </td>`;
        return tr;
    }

    function renumberRows() {
        itemsTbody.querySelectorAll('.item-row').forEach((row, i) => {
            row.querySelector('.row-num').textContent = i + 1;
        });
    }

    addItemBtn.addEventListener('click', function() {
        const tr = buildItemRow(itemRowCount++);
        itemsTbody.appendChild(tr);
        renumberRows();
        tr.querySelector('input[type="text"]').focus();
    });

    itemsTbody.addEventListener('click', function(e) {
        const btn = e.target.closest('.remove-row');
        if (!btn) return;
        const rows = itemsTbody.querySelectorAll('.item-row');
        if (rows.length <= 1) {
            document.getElementById('items-min-error').classList.remove('hidden');
            return;
        }
        document.getElementById('items-min-error').classList.add('hidden');
        btn.closest('.item-row').remove();
        renumberRows();
    });

    itemsTbody.addEventListener('keydown', function(e) {
        if (e.key !== 'Tab' || e.shiftKey) return;
        const rows = itemsTbody.querySelectorAll('.item-row');
        const lastRow = rows[rows.length - 1];
        const inputs = lastRow.querySelectorAll('input');
        const lastInput = inputs[inputs.length - 1];
        if (document.activeElement === lastInput) {
            e.preventDefault();
            const tr = buildItemRow(itemRowCount++);
            itemsTbody.appendChild(tr);
            renumberRows();
            tr.querySelector('input[type="text"]').focus();
        }
    });
});
</script>
@endsection
