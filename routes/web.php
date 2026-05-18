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
        $user = auth()->user();
        $colors = ['#958DF1', '#F98181', '#FBCE76', '#8AE234', '#729FCF', '#AD7FA8'];
        $userColor = $colors[$user->id % count($colors)];
        return view('editor', compact('name', 'user', 'userColor'));
    });

    // Download Dokumen sebagai file .doc (Word)
    Route::get('/doc/{name}/download', function ($name) {
        $doc = DB::table('documents')->where('name', $name)->first();

        if (!$doc) abort(404, 'Dokumen tidak ditemukan.');

        $rawContent = $doc->content ?? '';

        // Konversi Quill Delta JSON → teks HTML
        $bodyContent = '';
        try {
            $delta = json_decode($rawContent, true);
            if (isset($delta['ops']) && is_array($delta['ops'])) {
                foreach ($delta['ops'] as $op) {
                    if (isset($op['insert']) && is_string($op['insert'])) {
                        $bodyContent .= nl2br(htmlspecialchars($op['insert']));
                    }
                }
            } else {
                $bodyContent = nl2br(htmlspecialchars($rawContent));
            }
        } catch (\Exception $e) {
            $bodyContent = nl2br(htmlspecialchars($rawContent));
        }

        $docTitle = htmlspecialchars($name);
        $tanggal  = now()->format('d M Y, H:i');

        // Format HTML yang bisa dibaca Microsoft Word langsung
        $wordDoc = <<<HTML
<html xmlns:o='urn:schemas-microsoft-com:office:office'
      xmlns:w='urn:schemas-microsoft-com:office:word'
      xmlns='http://www.w3.org/TR/REC-html40'>
<head>
  <meta charset='UTF-8'>
  <title>{$docTitle}</title>
  <!--[if gte mso 9]>
  <xml><w:WordDocument><w:View>Print</w:View><w:Zoom>100</w:Zoom></w:WordDocument></xml>
  <![endif]-->
  <style>
    body  { font-family: Arial, sans-serif; font-size: 12pt; line-height: 1.6; margin: 2cm; }
    h1    { font-size: 20pt; border-bottom: 1pt solid #ccc; padding-bottom: 6pt; }
    h2    { font-size: 16pt; }
    p     { margin: 0.4em 0; }
    footer{ margin-top: 48pt; font-size: 10pt; color: #aaa; border-top: 1pt solid #eee; padding-top: 12pt; }
  </style>
</head>
<body>
  <h1>{$docTitle}</h1>
  <div>{$bodyContent}</div>
  <footer>Diunduh dari Realtime Docs &mdash; {$tanggal}</footer>
</body>
</html>
HTML;

        return response($wordDoc, 200, [
            'Content-Type'        => 'application/msword; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $name . '.doc"',
        ]);
    })->name('doc.download');

});

