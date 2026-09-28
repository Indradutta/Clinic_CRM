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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('patient_id')->unique();
            $table->string('name')->index();
            $table->date('dob')->nullable();
            $table->unsignedSmallInteger('age')->nullable()->index();
            $table->string('gender')->nullable()->index();
            $table->string('phone')->index();
            $table->string('email')->nullable()->index();
            $table->text('address')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('blood_group', 10)->nullable();
            $table->string('marital_status', 30)->nullable();

            // Medical Information (Sec 5, p. 5)
            $table->text('allergies')->nullable();
            $table->text('chronic_conditions')->nullable();
            $table->text('current_medications')->nullable();
            $table->text('past_surgeries')->nullable();
            $table->text('family_history')->nullable();
            $table->string('smoking_habits')->nullable();
            $table->string('alcohol_consumption')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
