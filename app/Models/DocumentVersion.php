<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_name',
        'version_name',
        'content'
    ];

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_name', 'name');
    }
}
