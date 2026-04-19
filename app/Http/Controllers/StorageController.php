<?php

namespace App\Http\Controllers;

use App\Models\Storage;
use Illuminate\Http\Request;

class StorageController extends Controller
{
    public function index()
    {
        $storages = Storage::all();
        return view('pages.storages.index', compact('storages'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'conditions' => 'nullable|string',
        ]);

        Storage::create($validated);
        return redirect()->back()->with('success', __('Storage location added successfully.'));
    }

    public function update(Request $request, Storage $storage)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'conditions' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $validated['is_active'] = $request->has('is_active');

        $storage->update($validated);
        return redirect()->back()->with('success', __('Storage location updated successfully.'));
    }

    public function destroy(Storage $storage)
    {
        // Check if there are active batches in this storage
        // Assuming ProductBatch has a relation 'batches'
        // For now, let's just delete or prevent if items exist
        $storage->delete();
        return redirect()->back()->with('success', __('Storage location deleted successfully.'));
    }
}
