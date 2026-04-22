<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $transaction->id }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            width: 80mm;
            margin: 0 auto;
            padding: 5mm;
            color: #000;
            font-size: 12px;
            line-height: 1.2;
        }

        .header {
            text-align: center;
            margin-bottom: 5mm;
            border-bottom: 1px dashed #000;
            padding-bottom: 3mm;
        }

        .header h1 {
            font-size: 18px;
            margin: 0;
            text-transform: uppercase;
        }

        .info {
            margin-bottom: 4mm;
        }

        .info div {
            display: flex;
            justify-content: space-between;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4mm;
        }

        th {
            text-align: left;
            border-bottom: 1px solid #000;
            font-size: 11px;
            padding: 1mm 0;
        }

        td {
            padding: 1.5mm 0;
            vertical-align: top;
        }

        .total-section {
            border-top: 1px dashed #000;
            padding-top: 2mm;
            margin-top: 2mm;
        }

        .total-section div {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 1mm;
        }

        .qr-section {
            text-align: center;
            margin-top: 6mm;
            border-top: 1px solid #eee;
            padding-top: 4mm;
        }

        .qr-section p {
            font-size: 10px;
            margin-bottom: 5px;
            color: #666;
        }

        .footer {
            text-align: center;
            margin-top: 2mm;
            font-size: 10px;
            border-top: 1px dashed #000;
            padding-top: 3mm;
        }

        .return-section {
            margin-top: 6mm;
            border-top: 2px solid #d00;
            padding-top: 4mm;
        }

        .return-section h3 {
            font-size: 13px;
            color: #d00;
            margin: 0 0 3mm 0;
            text-align: center;
        }

        .return-btn {
            background: #d00;
            color: #fff;
            border: none;
            padding: 3px 8px;
            font-size: 10px;
            border-radius: 3px;
            cursor: pointer;
        }

        .return-btn:hover {
            background: #b00;
        }

        .return-all-btn {
            width: 100%;
            padding: 8px;
            background: #d00;
            color: #fff;
            border: none;
            font-size: 12px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 4mm;
        }

        .return-all-btn:hover {
            background: #b00;
        }

        @media print {
            @page {
                margin: 0;
            }

            .no-print {
                display: none !important;
            }

            body {
                width: 72mm; /* Standard printable area for 80mm thermal paper */
                margin: 0;
                padding: 2mm;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .header h1 {
                font-size: 20px;
            }

            .total-section div {
                font-size: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>{{ DB::table('settings')->where('key', 'store_name')->value('value') ?: 'GenShelf Store' }}</h1>
        <p>{{ DB::table('settings')->where('key', 'store_address')->value('value') }}</p>
        <p>{{ DB::table('settings')->where('key', 'store_phone')->value('value') }}</p>
    </div>

    <div class="info">
        <div><span>Date:</span> <span>{{ $transaction->created_at->format('Y-m-d H:i') }}</span></div>
        <div><span>Invoice #:</span> <span>{{ $transaction->id }}</span></div>
        <div><span>Customer:</span> <span>{{ $transaction->customer ? $transaction->customer->name : 'Walk-in' }}</span>
        </div>
        <div><span>Cashier:</span> <span>{{ $transaction->user->name ?? 'N/A' }}</span></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th style="text-align:center">Qty</th>
                <th style="text-align:right">Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaction->items as $item)
                <tr>
                    <td>{{ $item->name }}</td>
                    <td style="text-align:center">{{ $item->qty }}</td>
                    <td style="text-align:right">{{ number_format($item->unit_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        <div><span>Subtotal:</span> <span>{{ number_format($transaction->subtotal, 2) }}</span></div>
        @if($transaction->tax > 0)
            <div><span>Tax:</span> <span>{{ number_format($transaction->tax, 2) }}</span></div>
        @endif
        <div style="font-size: 18px; margin-top: 2mm;"><span>TOTAL:</span>
            <span>{{ number_format($transaction->total, 2) }}</span></div>

        <div style="font-size: 12px; color: #444; margin-top: 2mm;">
            <span>Paid:</span> <span>{{ number_format($transaction->paid_amount ?? $transaction->total, 2) }}</span>
        </div>
        @if(($transaction->due_amount ?? 0) > 0)
            <div style="font-size: 12px; color: #d00;">
                <span>Balance:</span> <span>{{ number_format($transaction->due_amount, 2) }}</span>
            </div>
            <div><small>Due Date: {{ $transaction->due_date ?: 'N/A' }}</small></div>
        @endif
    </div>

    <div class="qr-section">
        <p>Scan to view digital receipt</p>
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ urlencode(route('pos.invoice', $transaction->id)) }}"
            alt="QR Code">
    </div>

    <div class="footer">
        <p>Thank you for your business!</p>
        <p>Powered by GenShelf</p>
    </div>

    {{-- Return Section (visible on screen for staff, hidden on print and for public) --}}
    @auth
    <div class="return-section no-print">
        <h3>🔄 Return Items</h3>
        <table style="width:100%;">
            @foreach($transaction->items as $item)
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 4px 0; font-size: 11px;">{{ $item->name }} (x{{ $item->qty }})</td>
                    <td style="text-align:right; padding: 4px 0;">
                        <form action="{{ route('pos.invoice.return') }}" method="POST" style="display:inline;">
                            @csrf
                            <input type="hidden" name="transaction_id" value="{{ $transaction->id }}">
                            <input type="hidden" name="product_id" value="{{ $item->product_id }}">
                            <input type="hidden" name="item_name" value="{{ $item->name }}">
                            <input type="hidden" name="qty" value="{{ $item->qty }}">
                            <input type="hidden" name="refund_amount" value="{{ $item->line_total }}">
                            <input type="hidden" name="type" value="single">
                            <button type="submit" class="return-btn"
                                onclick="return confirm('Return {{ $item->name }}?')">↩ Return</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </table>

        <form action="{{ route('pos.invoice.return') }}" method="POST">
            @csrf
            <input type="hidden" name="transaction_id" value="{{ $transaction->id }}">
            <input type="hidden" name="refund_amount" value="{{ $transaction->total }}">
            <input type="hidden" name="type" value="all">
            <button type="submit" class="return-all-btn"
                onclick="return confirm('Return ALL items from this invoice?')">↩ Return All Items</button>
        </form>
    </div>
    @endauth

    @if(session('success'))
        <div class="no-print" style="margin-top:10px; padding:8px; background:#e0ffe0; border:1px solid #0a0; border-radius:4px; font-size:11px; text-align:center;">
            ✅ {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="no-print" style="margin-top:10px; padding:8px; background:#ffe0e0; border:1px solid #d00; border-radius:4px; font-size:11px; text-align:center;">
            ❌ {{ session('error') }}
        </div>
    @endif

    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.print()"
            style="padding: 10px 20px; cursor: pointer; background: #4f46e5; color: #fff; border: none; border-radius: 5px;">Print
            Receipt</button>
    </div>
</body>

</html>