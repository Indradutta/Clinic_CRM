<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(PermissionSeeder::class);
        $this->call(SettingSeeder::class);
        $this->call(PatientSeeder::class);

        // 1. Admin / Doctor (Alexander Fleming)
        User::updateOrCreate(
            ['email' => 'doctor@mediflow.com'],
            [
                'name' => 'Dr. Alexander Fleming',
                'username' => 'admin',
                'phone' => '9876500001',
                'password' => 'password',
                'role' => User::ROLE_ADMIN_DOCTOR,
                'status' => 'active',
            ]
        );

        // 2. Clinic Manager (Sarah Jenkins)
        User::updateOrCreate(
            ['email' => 'manager@mediflow.com'],
            [
                'name' => 'Sarah Jenkins',
                'username' => 'manager',
                'phone' => '9876500002',
                'password' => 'password',
                'role' => User::ROLE_CLINIC_MANAGER,
                'status' => 'active',
            ]
        );

        // 3. Receptionist (Emily Watson)
        User::updateOrCreate(
            ['email' => 'receptionist@mediflow.com'],
            [
                'name' => 'Emily Watson',
                'username' => 'receptionist',
                'phone' => '9876500003',
                'password' => 'password',
                'role' => User::ROLE_RECEPTIONIST,
                'status' => 'active',
            ]
        );

        // 4. Nurse / Assistant (Clara Barton)
        User::updateOrCreate(
            ['email' => 'nurse@mediflow.com'],
            [
                'name' => 'Clara Barton',
                'username' => 'nurse',
                'phone' => '9876500004',
                'password' => 'password',
                'role' => User::ROLE_NURSE,
                'status' => 'active',
            ]
        );

        // 5. Accountant (David Miller)
        User::updateOrCreate(
            ['email' => 'accountant@mediflow.com'],
            [
                'name' => 'David Miller',
                'username' => 'accountant',
                'phone' => '9876500005',
                'password' => 'password',
                'role' => User::ROLE_ACCOUNTANT,
                'status' => 'active',
            ]
        );

        $this->call(AppointmentSeeder::class);
        $this->call(ConsultationSeeder::class);
        $this->call(PrescriptionSeeder::class);
        $this->call(InvoiceSeeder::class);
    }
}
