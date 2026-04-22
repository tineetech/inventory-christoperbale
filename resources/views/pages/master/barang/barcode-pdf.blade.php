<!DOCTYPE html>
<html>
<head>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; text-align: center; padding: 8px; }
        .sku  { font-size: 10px; font-weight: bold; margin-bottom: 2px; }
        .nama { font-size: 8px; color: #555; margin-bottom: 4px; }
        img   { width: 100%; }
    </style>
</head>
<body>
    <img src="data:image/png;base64,{{ $barcodeBase64 }}">
</body>
</html>