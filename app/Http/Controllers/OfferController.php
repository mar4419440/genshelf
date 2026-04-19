<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SpecialOffer;

class OfferController extends Controller
{
    public function index()
    {
        // Guard if toggled off
        if (\DB::table('settings')->where('key', 'toggle_offers')->value('value') != '1') {
            return view('pages.offers.disabled');
        }

        $offers = SpecialOffer::all();
        return view('pages.offers.index', compact('offers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'type' => 'required|in:fixed,pct,bogo',
            'value' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'active' => 'boolean'
        ]);

        $validated['active'] = $request->has('active') ? 1 : 0;

        SpecialOffer::create($validated);
        return redirect()->back()->with('success', __('Offer created successfully.'));
    }

    public function update(Request $request, SpecialOffer $offer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'type' => 'required|in:fixed,pct,bogo',
            'value' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'active' => 'boolean'
        ]);

        $validated['active'] = $request->has('active') ? 1 : 0;

        $offer->update($validated);
        return redirect()->back()->with('success', __('Offer updated successfully.'));
    }

    public function destroy(SpecialOffer $offer)
    {
        $offer->delete();
        return redirect()->back()->with('success', __('Offer deleted successfully.'));
    }
}
