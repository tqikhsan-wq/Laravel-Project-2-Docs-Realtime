<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $name }} - Google Docs Clone</title>
        <script>
            window.onerror = function(msg, url, line) {
                alert("CRITICAL ERROR: " + msg + " di baris " + line);
            };
        </script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
        <style>
            body { font-family: 'Roboto', sans-serif; }
            .font-google { font-family: 'Google Sans', sans-serif; }
        </style>
    </head>
    <body class="bg-[#F8F9FA] text-gray-900 m-0 p-0" style="overflow: hidden;">
        <div id="app" class="h-screen w-full">
            <h1 id="loading-text" class="text-2xl text-gray-500 absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">Memuat Editor... (Jika tulisan ini tidak hilang, ada error)</h1>
            <editor-component 
                doc-name="{{ $name }}" 
                user-name="{{ $user->name }}" 
                user-color="{{ $userColor }}">
            </editor-component>
        </div>
    </body>
</html>
