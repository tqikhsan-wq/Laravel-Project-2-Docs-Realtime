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
        
        $docName = strtolower($request->name);
        
        // Simpan ke database agar langsung muncul di dashboard
        DB::table('documents')->updateOrInsert(
            ['name' => $docName],
            ['content' => '', 'created_at' => now(), 'updated_at' => now()]
        );

        // Redirect ke halaman editor dengan nama dokumen tersebut
        return redirect('/doc/' . $docName);
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

