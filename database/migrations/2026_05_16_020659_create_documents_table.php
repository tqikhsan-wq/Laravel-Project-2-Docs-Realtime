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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            // Nama dokumen (sebagai kunci unik yang sama dengan 'name' di Editor.vue)
            $table->string('name')->unique();
            // Data biner Yjs yang panjang (Uint8Array) disimpan sebagai mediumBlob atau longBlob
            $table->binary('data')->nullable(); // Di MySQL, binary() menjadi BLOB. Cocok untuk data mentah Yjs.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
