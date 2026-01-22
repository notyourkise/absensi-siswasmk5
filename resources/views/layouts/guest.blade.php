<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Login - SMKN 5 Samarinda</title>

        {{-- 1. TAILWIND (Tetap) --}}
        <script src="https://cdn.tailwindcss.com"></script>

        {{-- 2. UPDATE: PANGGIL DOTLOTTIE PLAYER (Player Baru) --}}
        <script src="https://unpkg.com/@lottiefiles/dotlottie-wc@latest/dist/dotlottie-wc.js" type="module"></script>

        {{-- 3. FONT & ICON (Tetap) --}}
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </head>
    <body class="antialiased bg-gray-100">
        {{ $slot }}
    </body>
</html>