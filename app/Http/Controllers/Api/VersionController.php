<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocumentVersion;
use Illuminate\Http\Request;

class VersionController extends Controller
{
    public function index($document_name)
    {
        $versions = DocumentVersion::where('document_name', $document_name)->get();
        return response()->json($versions);
    }

    public function store(Request $request, $document_name)
    {
        $request->validate([
            'version_name' => 'required|string',
            'content' => 'required|string' // Delta or HTML representation
        ]);

        $version = DocumentVersion::create([
            'document_name' => $document_name,
            'version_name' => $request->version_name,
            'content' => $request->content
        ]);

        return response()->json($version, 201);
    }
}
