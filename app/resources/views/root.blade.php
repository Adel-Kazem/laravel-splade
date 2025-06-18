<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="{{ asset('css/root.css') }}">

        @spladeHead
        @vite('resources/js/app.js')
    </head>
    <body class="antialiased">
        @splade
    </body>
</html>
