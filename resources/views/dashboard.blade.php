<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Dashboard - Realtime Docs</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-50 text-gray-900 min-h-screen">
        <div class="max-w-5xl mx-auto p-8">
            <header class="flex justify-between items-center mb-10 border-b pb-4">
                <h1 class="text-3xl font-bold text-gray-800">Dokumen Saya</h1>
                <div class="flex items-center gap-4">
                    <div class="text-sm text-gray-600">
                        Halo, <span class="font-semibold text-gray-800">{{ auth()->user()->name }}</span>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-sm bg-red-100 text-red-600 hover:bg-red-200 px-3 py-1 rounded-md transition-colors">
                            Logout
                        </button>
                    </form>
                </div>
            </header>

            <!-- Form Buat Dokumen Baru -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-8">
                <h2 class="text-lg font-semibold mb-4 text-gray-700">Buat Dokumen Baru</h2>
                <form action="/documents" method="POST" class="flex gap-4">
                    @csrf
                    <input type="text" name="name" placeholder="Nama Dokumen (contoh: laporan-bulanan)" 
                           class="flex-1 border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" required pattern="[a-zA-Z0-9-]+">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md font-medium transition-colors">
                        Buat Dokumen
                    </button>
                </form>
                @error('name')
                    <p class="text-red-500 text-sm mt-2">Nama dokumen hanya boleh mengandung huruf, angka, dan strip (-).</p>
                @enderror
            </div>

            <!-- Daftar Dokumen -->
            <div>
                <h2 class="text-lg font-semibold mb-4 text-gray-700">Dokumen Terakhir Dibuka</h2>
                @if($documents->isEmpty())
                    <div class="text-center py-12 bg-white rounded-lg border border-gray-200 shadow-sm">
                        <p class="text-gray-500 mb-2">Belum ada dokumen.</p>
                        <p class="text-sm text-gray-400">Buat dokumen pertama Anda melalui form di atas.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($documents as $doc)
                        <div class="relative bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md hover:border-blue-300 transition-all group">
                            {{-- Tombol Download (pojok kanan atas) --}}
                            <a href="/doc/{{ $doc->name }}/download"
                               title="Download dokumen ini"
                               onclick="event.stopPropagation()"
                               class="absolute top-3 right-3 z-10 p-1.5 rounded-md text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                            </a>
                            {{-- Link Buka Dokumen --}}
                            <a href="/doc/{{ $doc->name }}" class="block p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="bg-blue-100 text-blue-600 p-3 rounded-lg group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-1 truncate pr-6">{{ $doc->name }}</h3>
                                <p class="text-xs text-gray-500">Terakhir diubah: {{ \Carbon\Carbon::parse($doc->updated_at)->diffForHumans() }}</p>
                            </a>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </body>
</html>
