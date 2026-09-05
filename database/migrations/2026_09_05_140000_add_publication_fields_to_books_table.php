<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('books')) {
            return;
        }

        Schema::table('books', function (Blueprint $table) {
            if (! Schema::hasColumn('books', 'content_kind')) {
                $table->string('content_kind', 32)
                    ->default('book')
                    ->after('book_type')
                    ->index();
            }
            if (! Schema::hasColumn('books', 'digital_file')) {
                $table->string('digital_file')->nullable()->after('sample_files');
            }
            if (! Schema::hasColumn('books', 'is_free')) {
                $table->boolean('is_free')->default(false)->after('price');
            }
            if (! Schema::hasColumn('books', 'admin_notes')) {
                $table->text('admin_notes')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('books')) {
            return;
        }

        Schema::table('books', function (Blueprint $table) {
            foreach (['content_kind', 'digital_file', 'is_free', 'admin_notes'] as $col) {
                if (Schema::hasColumn('books', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
