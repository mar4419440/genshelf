<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Barcode: {{ $product->name }}</title>
    <style>
        body {
            font-family: sans-serif;
            width: 50mm;
            height: 30mm;
            margin: 0;
            padding: 2mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .name {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 2mm;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 100%;
        }

        .barcode-img {
            height: 12mm;
            width: auto;
            margin-bottom: 1mm;
        }

        .code {
            font-size: 9px;
            font-family: monospace;
            letter-spacing: 2px;
        }

        .price {
            font-size: 12px;
            font-weight: 800;
            margin-top: 1mm;
        }

        @media print {
            body {
                width: 50mm;
                height: 30mm;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body onload="window.print()">
    <div class="name">{{ $product->name }}</div>
    @if($product->barcode)
        <!-- Using a CDN for barcode generation -->
        <img src="https://bwipjs-api.metafloor.com/?bcid=code128&text={{ urlencode($product->barcode) }}&scale=2&rotate=N&includetext=false"
            alt="Barcode" class="barcode-img">
        <div class="code">{{ $product->barcode }}</div>
    @else
        <div style="font-size: 10px; color: red;">No Barcode Assigned</div>
    @endif
    <div class="price">{{ number_format($product->default_price, 2) }}
        {{ DB::table('settings')->where('key', 'currency')->value('value') ?: 'EGP' }}</div>

    <div class="no-print" style="position: absolute; bottom: 5px;">
        <button onclick="window.print()" style="font-size: 10px;">Print</button>
    </div>
</body>

</html>