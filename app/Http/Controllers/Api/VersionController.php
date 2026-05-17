<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocumentVersion;
use Illuminate\Http\Request;

class VersionController extends Controller
{
    // GET /api/versions/{documentName}
    public function index($documentName)
    {
        $versions = DocumentVersion::where('document_name', $documentName)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'version_name', 'author_name', 'created_at']);
        return response()->json($versions);
    }

    // POST /api/versions/{documentName}
    public function store(Request $request, $documentName)
    {
        $request->validate([
            'version_name' => 'required|string',
            'data'         => 'required|string',
        ]);

        $version = DocumentVersion::create([
            'document_name' => $documentName,
            'version_name'  => $request->version_name,
            'author_name'   => auth()->check() ? auth()->user()->name : 'Anonim',
            'content'       => $request->data,  // field di DB = content
        ]);

        return response()->json($version, 201);
    }

    // GET /api/versions/data/{id}
    public function showData($id)
    {
        $version = DocumentVersion::findOrFail($id);
        return response()->json(['data' => $version->content]);
    }
}
