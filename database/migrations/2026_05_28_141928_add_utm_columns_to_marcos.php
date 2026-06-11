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
        Schema::table('marcos', function (Blueprint $table) {
            if (! Schema::hasColumn('marcos', 'easting')) {
                $table->decimal('easting', 12, 4)->nullable();
            }

            if (! Schema::hasColumn('marcos', 'northing')) {
                $table->decimal('northing', 12, 4)->nullable();
            }

            if (! Schema::hasColumn('marcos', 'meridiano_central')) {
                $table->integer('meridiano_central')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marcos', function (Blueprint $table) {
            $table->dropColumn(['easting', 'northing', 'meridiano_central']);
        });
    }
};
