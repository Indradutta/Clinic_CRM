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
        Schema::table('patients', function (Blueprint $table) {
            $table->string('preferred_language', 80)->nullable()->after('marital_status');
            $table->decimal('height_cm', 5, 1)->nullable()->after('preferred_language');
            $table->decimal('weight_kg', 5, 1)->nullable()->after('height_cm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['preferred_language', 'height_cm', 'weight_kg']);
        });
    }
};
