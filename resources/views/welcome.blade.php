<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Realtime Docs Editor</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-50 text-gray-900 p-8">
        <div id="app" class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold mb-6 text-center">Document Editor</h1>
            <editor-component></editor-component>
        </div>
    </body>
</html>
