@extends('layouts.app')

@push('styles')
@endpush

@section('content')
    <div class="page-hdr">
        <h2>{{ __('Settings') }}</h2>
    </div>

    <div class="split">
        <div>
            <div class="card">
                <h3 style="margin-bottom:16px">{{ __('Store Information') }}</h3>
                <form action="{{ route('settings.update') }}" method="POST">
                    @csrf
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label>{{ __('Store Name') }}</label>
                        <input name="store_name" value="{{ $settings['store_name'] ?? '' }}">
                    </div>
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label>{{ __('Store Address') }}</label>
                        <input name="store_address" value="{{ $settings['store_address'] ?? '' }}">
                    </div>
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label>{{ __('Store Phone') }}</label>
                        <input name="store_phone" value="{{ $settings['store_phone'] ?? '' }}">
                    </div>

                    <div class="form-row" style="display: flex; gap: 10px; margin-bottom: 12px;">
                        <div class="form-group" style="flex: 1;">
                            <label>{{ __('Currency') }}</label>
                            <select name="currency">
                                <option value="USD" {{ ($settings['currency'] ?? '') === 'USD' ? 'selected' : '' }}>USD ($)
                                </option>
                                <option value="EGP" {{ ($settings['currency'] ?? '') === 'EGP' ? 'selected' : '' }}>EGP (ج.م)
                                </option>
                                <option value="EUR" {{ ($settings['currency'] ?? '') === 'EUR' ? 'selected' : '' }}>EUR (€)
                                </option>
                            </select>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>{{ __('Tax Rate') }} (%)</label>
                            <input name="tax_rate" type="number" step="0.01" value="{{ $settings['tax_rate'] ?? '14' }}">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 12px;">
                        <label>{{ __('Low Stock Default Threshold') }}</label>
                        <input name="low_stock_default" type="number" value="{{ $settings['low_stock_default'] ?? '5' }}">
                    </div>
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label>{{ __('Language') }}</label>
                        <select name="language">
                            <option value="en" {{ ($settings['language'] ?? '') === 'en' ? 'selected' : '' }}>English</option>
                            <option value="ar" {{ ($settings['language'] ?? '') === 'ar' ? 'selected' : '' }}>العربية</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label>{{ __('Default Cost View (POs)') }}</label>
                        <select name="cost_display_mode">
                            <option value="unit" {{ ($settings['cost_display_mode'] ?? 'unit') === 'unit' ? 'selected' : '' }}>{{ __('Unit Cost') }}</option>
                            <option value="total" {{ ($settings['cost_display_mode'] ?? 'unit') === 'total' ? 'selected' : '' }}>{{ __('Total Cost') }}</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-pr" style="margin-top:8px">💾 {{ __('Save Settings') }}</button>
                </form>
            </div>
        </div>

        <div class="card">
            <h3 style="margin-bottom:16px">{{ __('Feature Toggles') }}</h3>
            <form action="{{ route('settings.updateToggles') }}" method="POST">
                @csrf
                @php
                    $toggles = [
                        'toggle_loyalty' => __('Loyalty System'),
                        'toggle_credit' => __('Credit & Debt System'),
                        'toggle_offers' => __('Offers Engine'),
                        'toggle_warranty' => __('Warranty Tracking'),
                        'toggle_tax' => __('Tax Calculation'),
                        'toggle_transfers' => __('Stock Transfers')
                    ];
                @endphp

                <div style="display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
                    @foreach($toggles as $key => $label)
                        <label
                            style="display:flex;align-items:center;gap:8px;padding:10px 14px;border:1px solid var(--bg3);border-radius:6px;font-size:13px;cursor:pointer;">
                            <input type="hidden" name="{{ $key }}" value="0">
                            <input type="checkbox" name="{{ $key }}" value="1" {{ ($settings[$key] ?? '0') === '1' ? 'checked' : '' }}>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>

                <button type="submit" class="btn btn-pr" style="margin-top:16px; width: 100%;">💾
                    {{ __('Save Toggles') }}</button>
            </form>
        </div>
    </div>
@endsection