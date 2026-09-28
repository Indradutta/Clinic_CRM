<?php

namespace Database\Seeders;

use App\Models\Consultation;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class PrescriptionSeeder extends Seeder
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

        if (Prescription::where('prescription_number', 'RX-0001')->exists()) {
            return;
        }

        $patients = Patient::take(4)->get();
        if ($patients->isEmpty()) {
            return;
        }

        $p1 = $patients[0] ?? null;
        $p2 = $patients[1] ?? $p1;
        $p3 = $patients[2] ?? $p1;

        $c1 = Consultation::where('patient_id', $p1->id)->first();
        $c2 = Consultation::where('patient_id', $p2->id)->first();
        $c3 = Consultation::where('patient_id', $p3->id)->first();

        // 1. Prescription for Patient 1 (Diabetes & HTN)
        $rx1 = Prescription::create([
            'prescription_number' => 'RX-0001',
            'patient_id' => $p1->id,
            'doctor_id' => $doctor->id,
            'appointment_id' => $c1?->appointment_id,
            'consultation_id' => $c1?->id,
            'prescription_date' => now()->subDays(3)->toDateString(),
            'diagnosis' => 'Type 2 Diabetes Mellitus & Stage 1 Essential Hypertension',
            'symptoms' => 'Excessive thirst, fatigue, occasional morning lightheadedness.',
            'clinical_notes' => 'Fasting blood sugar 152 mg/dL. BP 138/88 mmHg. Keep maintaining log of daily fasting and postprandial glucose levels.',
            'tests' => ['HbA1c Glycated Hemoglobin', 'Lipid Profile', 'Serum Creatinine & eGFR', 'Urine Microalbuminuria'],
            'advice' => 'Low carbohydrate, low salt (DASH) diet. Avoid processed sugars. 30 minutes brisk walking at least 5 days a week. Hydrate with at least 2.5 liters of water daily.',
            'follow_up_date' => now()->addDays(27)->toDateString(),
        ]);

        PrescriptionItem::create([
            'prescription_id' => $rx1->id,
            'medicine_name' => 'Metformin Hydrochloride (Glucophage)',
            'dosage' => '500 mg',
            'frequency' => 'Twice daily (BD)',
            'duration' => '30 Days',
            'route' => 'Oral',
            'timing' => 'With Meals',
            'instructions' => 'Take immediately with breakfast and dinner to avoid gastrointestinal irritation.',
        ]);

        PrescriptionItem::create([
            'prescription_id' => $rx1->id,
            'medicine_name' => 'Telmisartan (Telma)',
            'dosage' => '40 mg',
            'frequency' => 'Once daily (OD)',
            'duration' => '30 Days',
            'route' => 'Oral',
            'timing' => 'Morning Before Breakfast',
            'instructions' => 'Take regularly every morning at the same time. Check BP weekly.',
        ]);

        PrescriptionItem::create([
            'prescription_id' => $rx1->id,
            'medicine_name' => 'Rosuvastatin (Rozavel)',
            'dosage' => '10 mg',
            'frequency' => 'Once daily at bedtime (HS)',
            'duration' => '30 Days',
            'route' => 'Oral',
            'timing' => 'Bedtime',
            'instructions' => 'Take at night before sleep.',
        ]);

        // 2. Prescription for Patient 2 (Asthma & Allergic Rhinitis)
        $rx2 = Prescription::create([
            'prescription_number' => 'RX-0002',
            'patient_id' => $p2->id,
            'doctor_id' => $doctor->id,
            'appointment_id' => $c2?->appointment_id,
            'consultation_id' => $c2?->id,
            'prescription_date' => now()->subDays(1)->toDateString(),
            'diagnosis' => 'Bronchial Asthma (Mild Persistent) & Seasonal Allergic Rhinitis',
            'symptoms' => 'Nighttime dry cough, wheeze upon dust exposure.',
            'clinical_notes' => 'Expiratory wheeze in basal areas. Rhinitis managed with H1 antagonist + antileukotriene combination.',
            'tests' => ['Spirometry / Pulmonary Function Test', 'Complete Blood Count (CBC) with Absolute Eosinophil Count'],
            'advice' => 'Rinse mouth thoroughly with water and spit out after using steroid inhaler to prevent candidiasis. Keep windows closed during high pollen hours.',
            'follow_up_date' => now()->addDays(14)->toDateString(),
        ]);

        PrescriptionItem::create([
            'prescription_id' => $rx2->id,
            'medicine_name' => 'Budesonide + Formoterol Inhaler (Foracort 200)',
            'dosage' => '200 mcg / 6 mcg',
            'frequency' => '2 Puffs Twice daily (BD)',
            'duration' => '1 Month',
            'route' => 'Inhalation',
            'timing' => 'After Meals',
            'instructions' => 'Inhale through spacer device. Gargle and rinse mouth after each dose.',
        ]);

        PrescriptionItem::create([
            'prescription_id' => $rx2->id,
            'medicine_name' => 'Montelukast + Levocetirizine (Montair-LC)',
            'dosage' => '10 mg / 5 mg',
            'frequency' => 'Once daily at bedtime (HS)',
            'duration' => '15 Days',
            'route' => 'Oral',
            'timing' => 'Bedtime',
            'instructions' => 'Take at night. May cause mild drowsiness.',
        ]);

        // 3. Prescription for Patient 3 (Acute Tonsillopharyngitis)
        if ($p3) {
            $rx3 = Prescription::create([
                'prescription_number' => 'RX-0003',
                'patient_id' => $p3->id,
                'doctor_id' => $doctor->id,
                'appointment_id' => null,
                'consultation_id' => $c3?->id,
                'prescription_date' => now()->toDateString(),
                'diagnosis' => 'Acute Exudative Tonsillopharyngitis',
                'symptoms' => 'Severe throat pain, painful deglutition, fever 100.2°F.',
                'clinical_notes' => 'Tonsillar erythema and exudates. Complete 5 full days of antibiotics even if symptoms subside earlier.',
                'tests' => ['Rapid Strep Test / Throat Swab Culture'],
                'advice' => 'Warm saline gargles 4 times daily. Avoid chilled drinks, oily and spicy food. Drink plenty of warm fluids and herbal tea.',
                'follow_up_date' => now()->addDays(5)->toDateString(),
            ]);

            PrescriptionItem::create([
                'prescription_id' => $rx3->id,
                'medicine_name' => 'Amoxicillin + Potassium Clavulanate (Augmentin 625)',
                'dosage' => '625 mg',
                'frequency' => 'Twice daily (BD)',
                'duration' => '5 Days',
                'route' => 'Oral',
                'timing' => 'With Meals',
                'instructions' => 'Must complete full 5-day course. Do not discontinue prematurely.',
            ]);

            PrescriptionItem::create([
                'prescription_id' => $rx3->id,
                'medicine_name' => 'Paracetamol (Dolo 650)',
                'dosage' => '650 mg',
                'frequency' => 'As needed (SOS) max TID',
                'duration' => '3 Days',
                'route' => 'Oral',
                'timing' => 'After Meals',
                'instructions' => 'Take only if fever > 99.5°F or throat pain is distressing. Maintain 6-hour gap between doses.',
            ]);

            PrescriptionItem::create([
                'prescription_id' => $rx3->id,
                'medicine_name' => 'Povidone Iodine 2% Gargle Solution (Betadine)',
                'dosage' => '10 ml diluted 1:1 with warm water',
                'frequency' => 'Thrice daily (TID)',
                'duration' => '5 Days',
                'route' => 'Topical / Gargle',
                'timing' => 'After Meals',
                'instructions' => 'Gargle for 30 seconds and spit out. Do not swallow.',
            ]);
        }
    }
}
