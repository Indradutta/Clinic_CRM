<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\PatientNote;
use App\Models\User;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $doctor = User::where('role', User::ROLE_ADMIN_DOCTOR)->first();

        $patients = [
            [
                'patient_id' => 'PAT-00101',
                'name' => 'Robert Miller',
                'dob' => '1974-05-12',
                'age' => 52,
                'gender' => 'Male',
                'phone' => '9876543210',
                'email' => 'robert.miller@example.com',
                'address' => '12 Park Street, Central District, New Delhi',
                'emergency_contact_name' => 'Helen Miller (Wife)',
                'emergency_contact_phone' => '9876543219',
                'blood_group' => 'O+',
                'marital_status' => 'Married',
                'allergies' => 'Penicillin, Ciprofloxacin (Mild rash)',
                'chronic_conditions' => 'Type 2 Diabetes Mellitus, Essential Hypertension',
                'current_medications' => 'Metformin 500mg BD, Telmisartan 40mg OD',
                'past_surgeries' => 'Laparoscopic Cholecystectomy (2021)',
                'family_history' => 'Father had Myocardial Infarction at age 60. Mother has Osteoarthritis.',
                'smoking_habits' => 'Former smoker (Quit 2018)',
                'alcohol_consumption' => 'Occasional (1-2 drinks/month)',
            ],
            [
                'patient_id' => 'PAT-00102',
                'name' => 'Sarah Jenkins',
                'dob' => '1992-08-24',
                'age' => 34,
                'gender' => 'Female',
                'phone' => '9845011223',
                'email' => 'sarah.j@example.com',
                'address' => '45 Green Valley Enclave, Block B, Gurgaon',
                'emergency_contact_name' => 'Mark Jenkins (Brother)',
                'emergency_contact_phone' => '9845099887',
                'blood_group' => 'A+',
                'marital_status' => 'Single',
                'allergies' => 'Sulfa drugs, Dust mites (Allergic rhinitis)',
                'chronic_conditions' => 'Mild Persistent Bronchial Asthma',
                'current_medications' => 'Budesonide/Formoterol 200/6 Inhaler as needed',
                'past_surgeries' => 'None reported',
                'family_history' => 'Maternal history of Type 2 Diabetes.',
                'smoking_habits' => 'Non-smoker',
                'alcohol_consumption' => 'Non-drinker',
            ],
            [
                'patient_id' => 'PAT-00103',
                'name' => 'Priya Sharma',
                'dob' => '1998-11-03',
                'age' => 27,
                'gender' => 'Female',
                'phone' => '9988766554',
                'email' => 'priya.sharma@example.com',
                'address' => '78 Lakeview Apartments, Sector 14, Noida',
                'emergency_contact_name' => 'Rajesh Sharma (Father)',
                'emergency_contact_phone' => '9988711223',
                'blood_group' => 'B+',
                'marital_status' => 'Single',
                'allergies' => 'No known drug allergies (NKDA)',
                'chronic_conditions' => 'None',
                'current_medications' => 'Multivitamin supplement',
                'past_surgeries' => 'None',
                'family_history' => 'No significant hereditary diseases',
                'smoking_habits' => 'Non-smoker',
                'alcohol_consumption' => 'Social',
            ],
            [
                'patient_id' => 'PAT-00104',
                'name' => 'Amit Kumar',
                'dob' => '1981-03-19',
                'age' => 45,
                'gender' => 'Male',
                'phone' => '9123456789',
                'email' => 'amit.kumar@example.com',
                'address' => '89 Vasant Kunj, Pocket 4, New Delhi',
                'emergency_contact_name' => 'Sunita Kumar (Wife)',
                'emergency_contact_phone' => '9123498765',
                'blood_group' => 'AB+',
                'marital_status' => 'Married',
                'allergies' => 'Peanuts, Shellfish',
                'chronic_conditions' => 'Gastroesophageal Reflux Disease (GERD)',
                'current_medications' => 'Pantoprazole 40mg OD before breakfast',
                'past_surgeries' => 'Appendectomy (2012)',
                'family_history' => 'Father has Hypertension.',
                'smoking_habits' => 'Occasional smoker',
                'alcohol_consumption' => 'Moderate',
            ],
            [
                'patient_id' => 'PAT-00105',
                'name' => 'David Chen',
                'dob' => '1966-07-30',
                'age' => 60,
                'gender' => 'Male',
                'phone' => '9776655443',
                'email' => 'david.chen@example.com',
                'address' => '23 Defense Colony, Ring Road, New Delhi',
                'emergency_contact_name' => 'Lisa Chen (Daughter)',
                'emergency_contact_phone' => '9776611223',
                'blood_group' => 'O-',
                'marital_status' => 'Married',
                'allergies' => 'Aspirin, NSAIDs (Gastric upset)',
                'chronic_conditions' => 'Mild Osteoarthritis (Bilateral knees)',
                'current_medications' => 'Calcium + Vitamin D3, Glucosamine',
                'past_surgeries' => 'Knee Arthroscopy (2019)',
                'family_history' => 'Mother had Osteoporosis.',
                'smoking_habits' => 'Non-smoker',
                'alcohol_consumption' => 'Non-drinker',
            ],
        ];

        foreach ($patients as $data) {
            $patient = Patient::updateOrCreate(['patient_id' => $data['patient_id']], $data);

            // Add sample clinical note
            PatientNote::firstOrCreate(
                [
                    'patient_id' => $patient->id,
                    'title' => 'Initial Clinical Intake',
                ],
                [
                    'user_id' => $doctor?->id,
                    'note' => 'Patient registered for primary clinic care. Vitals and baseline medical history recorded. All allergies flagged in clinical chart.',
                ]
            );
        }
    }
}
