<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware('auth')->group(function () {

    // Halaman utama (Dashboard) untuk memilih/membuat dokumen
    Route::get('/', function () {
        $documents = DB::table('documents')->orderBy('updated_at', 'desc')->get(['id', 'name', 'updated_at']);
        return view('dashboard', compact('documents'));
    });

    // Proses pembuatan dokumen baru
    Route::post('/documents', function (Request $request) {
        $request->validate([
            'name' => 'required|string|regex:/^[a-zA-Z0-9-]+$/'
        ]);
        // Redirect ke halaman editor dengan nama dokumen tersebut
        return redirect('/doc/' . strtolower($request->name));
    });

    // Halaman Editor
    Route::get('/doc/{name}', function ($name) {
        // Mengirimkan data user login beserta warna kursor yang bisa di-generate dari ID user
        $user = auth()->user();
        $colors = ['#958DF1', '#F98181', '#FBCE76', '#8AE234', '#729FCF', '#AD7FA8'];
        $userColor = $colors[$user->id % count($colors)]; // Warna konsisten berdasarkan ID
        
        return view('editor', compact('name', 'user', 'userColor'));
    });

});

// API Routes untuk Versi Dokumen
Route::get('/api/versions/{documentName}', function ($documentName) {
    $versions = DB::table('document_versions')
        ->where('document_name', $documentName)
        ->orderBy('created_at', 'desc')
        ->get(['id', 'version_name', 'author_name', 'created_at']);
        
    return response()->json($versions);
});

// Menyimpan versi baru dari Vue (HTML string)
Route::post('/api/versions', function (Request $request) {
    $request->validate([
        'document_name' => 'required|string',
        'version_name' => 'required|string',
        'data' => 'required|string',
    ]);

    $authorName = auth()->check() ? auth()->user()->name : 'Anonim';

    DB::table('document_versions')->insert([
        'document_name' => $request->document_name,
        'version_name' => $request->version_name,
        'author_name' => $authorName,
        'data' => $request->data,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return response()->json(['success' => true]);
});

// Mengambil data spesifik (HTML string) untuk proses Restore
Route::get('/api/versions/data/{id}', function ($id) {
    $version = DB::table('document_versions')->where('id', $id)->first();
    if (!$version) return response()->json(['error' => 'Not found'], 404);
    
    return response()->json([
        'data' => $version->data // Akan otomatis dikonversi dari BLOB ke string HTML
    ]);
});
