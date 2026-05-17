<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function index()
    {
        return response()->json(Document::all());
    }

    public function show($name)
    {
        $document = Document::firstOrCreate(
            ['name' => $name],
            ['content' => '']
        );
        return response()->json($document);
    }

    public function update(Request $request, $name)
    {
        // Gunakan updateOrCreate agar tidak pernah 404 meski dokumen belum ada
        $document = Document::updateOrCreate(
            ['name' => $name],
            ['content' => $request->content]
        );
        return response()->json($document);
    }
}
