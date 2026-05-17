<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_versions', function (Blueprint $table) {
            // Tambah author_name jika belum ada
            if (!Schema::hasColumn('document_versions', 'author_name')) {
                $table->string('author_name')->nullable()->after('version_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('document_versions', function (Blueprint $table) {
            $table->dropColumn('author_name');
        });
    }
};
