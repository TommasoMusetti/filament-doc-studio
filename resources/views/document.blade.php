<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        /* dompdf-first: no flexbox, no grid, no custom fonts. */
        @page { margin: 20mm; }

        body { font-family: DejaVu Sans, sans-serif; font-size: 11pt; color: #111; }

        h1 { font-size: 20pt; margin: 0 0 8pt; }
        h2 { font-size: 15pt; margin: 12pt 0 6pt; }
        h3 { font-size: 12pt; margin: 10pt 0 4pt; }
    </style>
</head>
<body>
{{-- Blocks escape their own user data; what arrives here is finished markup. --}}
{!! $body !!}
</body>
</html>
