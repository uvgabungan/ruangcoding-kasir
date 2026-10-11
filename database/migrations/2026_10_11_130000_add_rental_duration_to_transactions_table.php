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
        if (Schema::hasTable('transactions') && !Schema::hasColumn('transactions', 'rental_duration')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->integer('rental_duration')->default(1)->after('notes');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('transactions') && Schema::hasColumn('transactions', 'rental_duration')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropColumn('rental_duration');
            });
        }
    }
};
