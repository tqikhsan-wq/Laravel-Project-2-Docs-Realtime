<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Editor: {{ $name }} - Realtime Docs</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-50 text-gray-900 min-h-screen">
        <div id="app" class="max-w-6xl mx-auto p-4 sm:p-8">
            <div class="mb-6 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="/" class="text-gray-500 hover:text-gray-800 transition-colors" title="Kembali ke Dashboard">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    </a>
                    <h1 class="text-2xl font-bold text-gray-800">Editor: <span class="text-blue-600">{{ $name }}</span></h1>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-sm text-gray-600 hidden sm:block">
                        Login sebagai: <span class="font-semibold text-gray-800">{{ auth()->user()->name }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Mengirim nama dokumen, nama user, dan warna kursor sebagai prop ke Vue Component -->
            <editor-component 
                doc-name="{{ $name }}" 
                user-name="{{ $user->name }}" 
                user-color="{{ $userColor }}">
            </editor-component>
        </div>
    </body>
</html>
