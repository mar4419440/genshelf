<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return view('pages.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
        ]);

        Category::create($validated);
        return redirect()->back()->with('success', __('Category added successfully.'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
        ]);

        $category->update($validated);
        return redirect()->back()->with('success', __('Category updated successfully.'));
    }

    public function destroy(Category $category)
    {
        // For GenShelf, since we duplicate strings to products/suppliers, deleting a category
        // just removes it from the options list, it doesn't break existing products.
        $category->delete();
        return redirect()->back()->with('success', __('Category deleted successfully.'));
    }
}
