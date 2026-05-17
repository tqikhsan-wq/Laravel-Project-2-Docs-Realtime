<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Login - Realtime Docs</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-50 text-gray-900 min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full bg-white rounded-xl shadow-lg p-8 border border-gray-100">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-blue-600 mb-2">Realtime Docs</h1>
                <p class="text-gray-500">Masuk untuk melanjutkan ke dokumen</p>
            </div>

            <form action="/login" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="password">Password</label>
                    <input type="password" id="password" name="password" required
                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors">
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg shadow-md transition-colors">
                    Masuk
                </button>
            </form>

            <p class="mt-6 text-center text-gray-600 text-sm">
                Belum punya akun? <a href="/register" class="text-blue-600 hover:text-blue-800 font-semibold">Daftar sekarang</a>
            </p>
        </div>
    </body>
</html>
