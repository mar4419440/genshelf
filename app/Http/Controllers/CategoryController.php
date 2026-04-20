<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('parent')->get();
        return view('pages.categories.index', compact('categories'));
    }

    public function downloadTemplate()
    {
        $reqStyle = '<style bgcolor="#FFE6E6">';
        $reqEnd = '</style>';

        $headers = [
            $reqStyle . __('Category Name (Arabic)') . $reqEnd,
            __('Category Name (English)'),
            __('Parent Category Name')
        ];

        $xlsx = \Shuchkin\SimpleXLSXGen::fromArray([$headers]);
        $xlsx->downloadAs('category_template.xlsx');
        exit;
    }

    public function importCategories(Request $request)
    {
        $request->validate(['csv_file' => 'required|file|mimes:csv,txt,xlsx,xls']);

        $path = $request->file('csv_file')->getRealPath();
        $extension = $request->file('csv_file')->getClientOriginalExtension();

        if (in_array(strtolower($extension), ['xlsx', 'xls'])) {
            if ($xlsx = \Shuchkin\SimpleXLSX::parse($path)) {
                $data = $xlsx->rows();
            } else {
                return redirect()->back()->with('error', \Shuchkin\SimpleXLSX::parseError());
            }
        } else {
            $data = array_map('str_getcsv', file($path));
        }

        $header = array_shift($data);
        $count = 0;
        $errors = [];

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            foreach ($data as $index => $row) {
                if (count($row) < 1) continue;

                $nameAr = trim($row[0] ?? '');
                $nameEn = trim($row[1] ?? '');
                $parentName = trim($row[2] ?? '');

                if (empty($nameAr)) {
                    $errors[] = "Row " . ($index + 2) . ": Missing required Category Name.";
                    continue;
                }

                $parentId = null;
                if (!empty($parentName)) {
                    $parent = Category::where('name', $parentName)->orWhere('name_en', $parentName)->first();
                    if ($parent) {
                        $parentId = $parent->id;
                    } else {
                        // Optionally create parent or just error
                        $errors[] = "Row " . ($index + 2) . ": Parent '$parentName' not found.";
                        continue;
                    }
                }

                Category::create([
                    'name' => $nameAr,
                    'name_en' => $nameEn,
                    'parent_id' => $parentId
                ]);
                $count++;
            }
            \Illuminate\Support\Facades\DB::commit();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->back()->with('error', __('Import failed: ') . $e->getMessage());
        }

        $msg = __(':count Categories imported successfully.', ['count' => $count]);
        if (!empty($errors)) {
            $msg .= " " . __('Errors in rows: ') . implode(', ', $errors);
        }

        return redirect()->back()->with('success', $msg);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        Category::create($validated);
        return redirect()->back()->with('success', __('Category added successfully.'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:categories,id|different:id',
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
