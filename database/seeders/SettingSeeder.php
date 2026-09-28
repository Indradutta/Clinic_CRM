<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // 1. Doctor Profile (Sec 8, p. 9)
            'doctor_name' => ['Dr. Alexander Fleming, M.D.', 'doctor'],
            'doctor_qualification' => ['MBBS, MD - General Medicine', 'doctor'],
            'doctor_specialization' => ['Internal Medicine & Primary Care', 'doctor'],
            'doctor_registration_number' => ['MCI-482910', 'doctor'],
            'doctor_phone' => ['9876500001', 'doctor'],
            'doctor_email' => ['doctor@mediflow.com', 'doctor'],
            'doctor_clinic_name' => ['MediFlow Central Polyclinic', 'doctor'],
            'doctor_clinic_address' => ['42 Healthcare Avenue, Medical Enclave, New Delhi - 110001', 'doctor'],
            'doctor_profile_photo' => ['', 'doctor'],

            // 2. Clinic Settings (Sec 8, p. 9)
            'clinic_name' => ['MediFlow Central Polyclinic', 'clinic'],
            'clinic_address' => ['42 Healthcare Avenue, Medical Enclave, New Delhi - 110001', 'clinic'],
            'clinic_phone' => ['9876500006', 'clinic'],
            'clinic_email' => ['contact@mediflowclinic.com', 'clinic'],
            'clinic_website' => ['https://mediflowclinic.com', 'clinic'],
            'clinic_logo' => ['', 'clinic'],
            'clinic_working_hours' => ['09:00 AM - 07:00 PM', 'clinic'],
            'clinic_consultation_fee' => ['500', 'clinic'],
            'clinic_currency' => ['₹', 'clinic'],
            'clinic_time_zone' => ['Asia/Kolkata', 'clinic'],

            // 3. Appointment Settings (Sec 8, p. 10)
            'appointment_duration' => ['15', 'appointment'],
            'appointment_working_days' => [json_encode(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']), 'appointment'],
            'appointment_working_hours_start' => ['09:00', 'appointment'],
            'appointment_working_hours_end' => ['19:00', 'appointment'],
            'appointment_break_time_start' => ['13:00', 'appointment'],
            'appointment_break_time_end' => ['14:00', 'appointment'],
            'appointment_max_per_day' => ['30', 'appointment'],
            'appointment_cancellation_rules' => ['Cancellations must be requested at least 2 hours prior to scheduled appointment slot.', 'appointment'],
            'appointment_reminders' => ['1', 'appointment'],

            // 4. Prescription Settings (Sec 8, p. 10)
            'prescription_header' => ['MediFlow Digital Outpatient Care Center', 'prescription'],
            'prescription_doctor_info' => ['Dr. Alexander Fleming | MBBS, MD (General Medicine) | Reg: MCI-482910', 'prescription'],
            'prescription_clinic_logo' => ['', 'prescription'],
            'prescription_footer' => ['Please take medicines strictly according to prescribed dosage. Report immediately in case of any adverse symptoms.', 'prescription'],
            'prescription_signature' => ['', 'prescription'],
            'prescription_default_instructions' => ['Take with lukewarm water after food unless explicitly directed otherwise.', 'prescription'],

            // 5. Invoice Settings (Sec 8, p. 10)
            'invoice_prefix' => ['INV-', 'invoice'],
            'invoice_numbering' => ['1001', 'invoice'],
            'invoice_tax_settings' => ['18', 'invoice'],
            'invoice_payment_terms' => ['Payment is due upon receipt of medical consultation and services.', 'invoice'],
            'invoice_footer' => ['Thank you for visiting MediFlow Clinic. Wishing you a swift recovery!', 'invoice'],
            'invoice_business_information' => ['GSTIN: 07AAAAA0000A1Z5 | Reg: DL-MED-9921', 'invoice'],

            // 6. Notification Settings (Sec 8, p. 10)
            'notify_new_appointment' => ['1', 'notification'],
            'notify_appointment_confirmation' => ['1', 'notification'],
            'notify_appointment_cancellation' => ['1', 'notification'],
            'notify_appointment_reminders' => ['1', 'notification'],
            'notify_payment_received' => ['1', 'notification'],
            'notify_invoice_due' => ['1', 'notification'],
        ];

        foreach ($settings as $key => [$value, $group]) {
            Setting::set($key, $value, $group);
        }
    }
}
