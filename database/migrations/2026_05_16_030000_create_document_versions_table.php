<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();
            // Nama dokumen (relasi ke tabel documents)
            $table->string('document_name');
            // Nama versi (misal: "Draft 1", "Revisi Final")
            $table->string('version_name');
            // Data biner Yjs (snapshot pada saat versi ini dibuat)
            $table->binary('data');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_versions');
    }
};
