<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $doctor = User::where('role', User::ROLE_ADMIN_DOCTOR)->first() ?? User::first();
        if (! $doctor) {
            return;
        }

        $patients = Patient::take(5)->get();
        if ($patients->isEmpty()) {
            return;
        }

        $p1 = $patients[0] ?? null;
        $p2 = $patients[1] ?? $p1;
        $p3 = $patients[2] ?? $p1;
        $p4 = $patients[3] ?? $p1;
        $p5 = $patients[4] ?? $p1;

        $appointments = [
            // Today's appointments
            [
                'appointment_id' => 'APT-00101',
                'patient_id' => $p1->id,
                'doctor_id' => $doctor->id,
                'appointment_date' => now()->toDateString(),
                'appointment_time' => '09:30',
                'appointment_type' => 'Consultation',
                'reason_for_visit' => 'Quarterly HbA1c and Blood Pressure checkup',
                'notes' => 'Patient has fasting blood sugar report ready. Check feet for peripheral neuropathy.',
                'status' => Appointment::STATUS_CHECKED_IN,
                'payment_status' => Appointment::PAYMENT_UNPAID,
            ],
            [
                'appointment_id' => 'APT-00102',
                'patient_id' => $p2->id,
                'doctor_id' => $doctor->id,
                'appointment_date' => now()->toDateString(),
                'appointment_time' => '10:30',
                'appointment_type' => 'Follow-up',
                'reason_for_visit' => 'Asthma inhaler technique review and seasonal wheezing',
                'notes' => 'Review peak flow readings from past 2 weeks.',
                'status' => Appointment::STATUS_CONFIRMED,
                'payment_status' => Appointment::PAYMENT_UNPAID,
            ],
            [
                'appointment_id' => 'APT-00103',
                'patient_id' => $p3->id,
                'doctor_id' => $doctor->id,
                'appointment_date' => now()->toDateString(),
                'appointment_time' => '11:15',
                'appointment_type' => 'Consultation',
                'reason_for_visit' => 'Severe recurrent tension headaches and neck stiffness',
                'notes' => 'Complaining of screen fatigue and cervical stiffness during work.',
                'status' => Appointment::STATUS_IN_CONSULTATION,
                'payment_status' => Appointment::PAYMENT_UNPAID,
            ],
            [
                'appointment_id' => 'APT-00104',
                'patient_id' => $p4->id,
                'doctor_id' => $doctor->id,
                'appointment_date' => now()->toDateString(),
                'appointment_time' => '14:30',
                'appointment_type' => 'Routine Checkup',
                'reason_for_visit' => 'Annual master health checkup and thyroid profile review',
                'notes' => 'Family history of thyroid disorders. Send for complete lipid panel.',
                'status' => Appointment::STATUS_PENDING,
                'payment_status' => Appointment::PAYMENT_UNPAID,
            ],
            [
                'appointment_id' => 'APT-00105',
                'patient_id' => $p5->id,
                'doctor_id' => $doctor->id,
                'appointment_date' => now()->toDateString(),
                'appointment_time' => '16:00',
                'appointment_type' => 'Emergency',
                'reason_for_visit' => 'Acute lower back spasm after lifting luggage',
                'notes' => 'Assess straight leg raise test and reflexes.',
                'status' => Appointment::STATUS_CONFIRMED,
                'payment_status' => Appointment::PAYMENT_UNPAID,
            ],

            // Past Completed
            [
                'appointment_id' => 'APT-00106',
                'patient_id' => $p1->id,
                'doctor_id' => $doctor->id,
                'appointment_date' => now()->subDays(2)->toDateString(),
                'appointment_time' => '11:00',
                'appointment_type' => 'Consultation',
                'reason_for_visit' => 'Initial consultation for persistent lethargy',
                'notes' => 'Prescribed preliminary vitamins and ordered lab tests.',
                'status' => Appointment::STATUS_COMPLETED,
                'payment_status' => Appointment::PAYMENT_PAID,
            ],
            [
                'appointment_id' => 'APT-00107',
                'patient_id' => $p3->id,
                'doctor_id' => $doctor->id,
                'appointment_date' => now()->subDays(5)->toDateString(),
                'appointment_time' => '15:00',
                'appointment_type' => 'Consultation',
                'reason_for_visit' => 'Post-viral dry cough evaluation',
                'notes' => 'Lungs clear on auscultation.',
                'status' => Appointment::STATUS_COMPLETED,
                'payment_status' => Appointment::PAYMENT_PAID,
            ],

            // Upcoming
            [
                'appointment_id' => 'APT-00108',
                'patient_id' => $p2->id,
                'doctor_id' => $doctor->id,
                'appointment_date' => now()->addDay()->toDateString(),
                'appointment_time' => '10:00',
                'appointment_type' => 'Follow-up',
                'reason_for_visit' => 'Spirometry test results discussion',
                'notes' => '',
                'status' => Appointment::STATUS_CONFIRMED,
                'payment_status' => Appointment::PAYMENT_UNPAID,
            ],
            [
                'appointment_id' => 'APT-00109',
                'patient_id' => $p4->id,
                'doctor_id' => $doctor->id,
                'appointment_date' => now()->addDays(3)->toDateString(),
                'appointment_time' => '12:00',
                'appointment_type' => 'Routine Checkup',
                'reason_for_visit' => 'Follow-up on iron levels and dietary plan',
                'notes' => '',
                'status' => Appointment::STATUS_PENDING,
                'payment_status' => Appointment::PAYMENT_UNPAID,
            ],

            // Cancelled
            [
                'appointment_id' => 'APT-00110',
                'patient_id' => $p5->id,
                'doctor_id' => $doctor->id,
                'appointment_date' => now()->subDays(1)->toDateString(),
                'appointment_time' => '14:00',
                'appointment_type' => 'Consultation',
                'reason_for_visit' => 'Orthopedic knee consultation',
                'notes' => 'Patient called front desk to cancel due to personal emergency.',
                'status' => Appointment::STATUS_CANCELLED,
                'payment_status' => Appointment::PAYMENT_UNPAID,
                'cancelled_reason' => 'Patient requested cancellation due to out of station travel.',
            ],
        ];

        foreach ($appointments as $data) {
            Appointment::updateOrCreate(
                ['appointment_id' => $data['appointment_id']],
                $data
            );
        }
    }
}
