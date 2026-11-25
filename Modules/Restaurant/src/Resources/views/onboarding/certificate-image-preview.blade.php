<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $fileName }}</title>
    <style>
        * { box-sizing: border-box; }
        html, body { width: 100%; min-height: 100%; margin: 0; }
        body {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            background: #f1f3f5;
        }
        img {
            display: block;
            max-width: min(100%, 1200px);
            max-height: calc(100vh - 48px);
            width: auto;
            height: auto;
            object-fit: contain;
            background: #fff;
            box-shadow: 0 8px 32px rgb(15 23 42 / 16%);
        }
    </style>
</head>
<body>
    <img src="{{ $image }}" alt="{{ $fileName }}">
</body>
</html>
