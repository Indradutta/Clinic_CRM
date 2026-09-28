<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;

class ConsultationSeeder extends Seeder
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

        $patients = Patient::take(4)->get();
        if ($patients->isEmpty()) {
            return;
        }

        $p1 = $patients[0] ?? null;
        $p2 = $patients[1] ?? $p1;
        $p3 = $patients[2] ?? $p1;
        $p4 = $patients[3] ?? $p1;

        $apt1 = Appointment::where('patient_id', $p1->id)->first();
        $apt2 = Appointment::where('patient_id', $p2->id)->first();

        // 1. Consultation for Patient 1 (Type 2 Diabetes & Hypertension)
        Consultation::create([
            'patient_id' => $p1->id,
            'doctor_id' => $doctor->id,
            'appointment_id' => $apt1?->id,
            'consultation_date' => now()->subDays(3)->toDateString(),
            'symptoms' => 'Mild morning dizziness, excessive thirst, increased urination frequency over past 2 weeks.',
            'diagnosis' => 'Type 2 Diabetes Mellitus with Essential Hypertension (Mild Uncontrolled)',
            'vitals' => [
                'blood_pressure' => '138/88',
                'heart_rate' => '78',
                'temperature' => '98.4',
                'respiratory_rate' => '16',
                'oxygen_saturation' => '98',
                'weight' => '76.5',
                'height' => '172',
                'bmi' => '25.9',
            ],
            'medical_notes' => 'Cardiovascular exam shows normal S1/S2, no murmurs. Pedal pulses intact bilaterally. Fasting Blood Glucose was 152 mg/dL. HbA1c pending repeat next month. Advised reduction in salt intake and 30 minutes brisk walking daily.',
            'treatment' => 'Titrate Metformin, continue Telmisartan 40mg. Diet counseling provided. Recommended lifestyle modifications.',
        ]);

        // 2. Consultation for Patient 2 (Bronchial Asthma)
        Consultation::create([
            'patient_id' => $p2->id,
            'doctor_id' => $doctor->id,
            'appointment_id' => $apt2?->id,
            'consultation_date' => now()->subDays(1)->toDateString(),
            'symptoms' => 'Dry nocturnal cough, episodic wheezing triggered by dust, slight shortness of breath on climbing stairs.',
            'diagnosis' => 'Mild Persistent Bronchial Asthma with Seasonal Allergic Rhinitis',
            'vitals' => [
                'blood_pressure' => '118/76',
                'heart_rate' => '82',
                'temperature' => '98.6',
                'respiratory_rate' => '20',
                'oxygen_saturation' => '96',
                'weight' => '62.0',
                'height' => '165',
                'bmi' => '22.8',
            ],
            'medical_notes' => 'Bilateral expiratory rhonchi present in lower lung zones. No cyanosis or clubbing. ENT examination shows pale, swollen turbinates with clear nasal discharge.',
            'treatment' => 'Inhaled corticosteroid + LABA combination. Add oral Montelukast nightly. Inhaler spacer technique demonstrated and verified.',
        ]);

        // 3. Consultation for Patient 3 (Acute Pharyngitis)
        if ($p3) {
            Consultation::create([
                'patient_id' => $p3->id,
                'doctor_id' => $doctor->id,
                'appointment_id' => null,
                'consultation_date' => now()->toDateString(),
                'symptoms' => 'Sore throat, pain while swallowing, low-grade fever for 3 days, mild headache.',
                'diagnosis' => 'Acute Exudative Tonsillopharyngitis',
                'vitals' => [
                    'blood_pressure' => '120/80',
                    'heart_rate' => '88',
                    'temperature' => '100.2',
                    'respiratory_rate' => '18',
                    'oxygen_saturation' => '99',
                    'weight' => '70.0',
                    'height' => '175',
                    'bmi' => '22.9',
                ],
                'medical_notes' => 'Pharyngeal mucosa congested with erythematous tonsils showing follicular exudates. Tender anterior cervical lymphadenopathy. Systemic review otherwise unremarkable.',
                'treatment' => 'Oral antibiotic course for 5 days, warm saline gargles, antipyretic/analgesic as needed.',
            ]);
        }
    }
}
