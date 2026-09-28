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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('appointment_id')->unique();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
            $table->date('appointment_date')->index();
            $table->string('appointment_time'); // e.g. 09:30 or 14:15
            $table->string('appointment_type')->default('Consultation'); // Consultation, Follow-up, Routine Checkup, Emergency
            $table->text('reason_for_visit')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('pending')->index(); // pending, confirmed, checked_in, in_consultation, completed, cancelled, no_show
            $table->string('payment_status')->default('unpaid')->index(); // unpaid, paid, partially_paid
            $table->text('cancelled_reason')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
