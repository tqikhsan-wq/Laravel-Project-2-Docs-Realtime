<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'content'
    ];

    public function versions()
    {
        return $this->hasMany(DocumentVersion::class, 'document_name', 'name');
    }
}
