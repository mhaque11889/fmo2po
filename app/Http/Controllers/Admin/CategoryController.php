<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('requirements')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string|max:1000',
            'sort_order'  => 'nullable|integer|min:0|max:9999',
        ]);

        $sortOrder = $validated['sort_order'] ?? 0;

        // Shift existing categories to make room, ensuring no duplicates
        Category::where('sort_order', '>=', $sortOrder)
            ->increment('sort_order');

        Category::create([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'sort_order'  => $sortOrder,
            'is_active'   => true,
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string|max:1000',
            'sort_order'  => 'nullable|integer|min:0|max:9999',
            'is_active'   => 'boolean',
        ]);

        $sortOrder = $validated['sort_order'] ?? 0;

        // If the sort_order changed, shift others to make room
        if ($category->sort_order !== $sortOrder) {
            Category::where('id', '!=', $category->id)
                ->where('sort_order', '>=', $sortOrder)
                ->increment('sort_order');
        }

        $category->update([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'sort_order'  => $sortOrder,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        if ($category->requirements()->exists()) {
            return back()->with('error', "Cannot delete \"{$category->name}\" — it has existing requests. Deactivate it instead.");
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
