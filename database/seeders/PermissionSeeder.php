<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Dashboard
            ['name' => 'dashboard.view', 'module' => 'Dashboard', 'label' => 'View Dashboard', 'is_sensitive' => false],

            // Doctors
            ['name' => 'doctors.view', 'module' => 'Doctors', 'label' => 'View Doctors', 'is_sensitive' => false],
            ['name' => 'doctors.create', 'module' => 'Doctors', 'label' => 'Add Doctors', 'is_sensitive' => true],
            ['name' => 'doctors.edit', 'module' => 'Doctors', 'label' => 'Edit Doctors', 'is_sensitive' => true],
            ['name' => 'doctors.delete', 'module' => 'Doctors', 'label' => 'Remove Doctors', 'is_sensitive' => true],

            // Appointments
            ['name' => 'appointments.view', 'module' => 'Appointments', 'label' => 'View Appointments', 'is_sensitive' => false],
            ['name' => 'appointments.create', 'module' => 'Appointments', 'label' => 'Create Appointments', 'is_sensitive' => false],
            ['name' => 'appointments.edit', 'module' => 'Appointments', 'label' => 'Edit Appointments', 'is_sensitive' => false],
            ['name' => 'appointments.cancel', 'module' => 'Appointments', 'label' => 'Cancel Appointments', 'is_sensitive' => false],
            ['name' => 'appointments.delete', 'module' => 'Appointments', 'label' => 'Delete Appointments', 'is_sensitive' => true],

            // Clinical Consultations
            ['name' => 'consultations.view', 'module' => 'Consultations', 'label' => 'View Consultations', 'is_sensitive' => true],
            ['name' => 'consultations.create', 'module' => 'Consultations', 'label' => 'Create Consultations', 'is_sensitive' => true],
            ['name' => 'consultations.edit', 'module' => 'Consultations', 'label' => 'Edit Consultations', 'is_sensitive' => true],
            ['name' => 'consultations.delete', 'module' => 'Consultations', 'label' => 'Delete Consultations', 'is_sensitive' => true],

            // Reports
            ['name' => 'reports.view', 'module' => 'Reports', 'label' => 'View Reports', 'is_sensitive' => true],
            ['name' => 'reports.export', 'module' => 'Reports', 'label' => 'Export Reports', 'is_sensitive' => true],
            ['name' => 'reports.print', 'module' => 'Reports', 'label' => 'Print Reports', 'is_sensitive' => true],

            // Patients
            ['name' => 'patients.view', 'module' => 'Patients', 'label' => 'View Patients', 'is_sensitive' => false],
            ['name' => 'patients.create', 'module' => 'Patients', 'label' => 'Create Patients', 'is_sensitive' => false],
            ['name' => 'patients.edit', 'module' => 'Patients', 'label' => 'Edit Patients', 'is_sensitive' => false],
            ['name' => 'patients.delete', 'module' => 'Patients', 'label' => 'Delete Patients', 'is_sensitive' => true],

            // Prescriptions
            ['name' => 'prescriptions.view', 'module' => 'Prescriptions', 'label' => 'View Prescriptions', 'is_sensitive' => false],
            ['name' => 'prescriptions.create', 'module' => 'Prescriptions', 'label' => 'Create Prescriptions', 'is_sensitive' => false],
            ['name' => 'prescriptions.edit', 'module' => 'Prescriptions', 'label' => 'Edit Prescriptions', 'is_sensitive' => false],
            ['name' => 'prescriptions.delete', 'module' => 'Prescriptions', 'label' => 'Delete Prescriptions', 'is_sensitive' => true],
            ['name' => 'prescriptions.print', 'module' => 'Prescriptions', 'label' => 'Print Prescriptions', 'is_sensitive' => false],

            // Invoices
            ['name' => 'invoices.view', 'module' => 'Invoices', 'label' => 'View Invoices', 'is_sensitive' => false],
            ['name' => 'invoices.create', 'module' => 'Invoices', 'label' => 'Create Invoices', 'is_sensitive' => false],
            ['name' => 'invoices.edit', 'module' => 'Invoices', 'label' => 'Edit Invoices', 'is_sensitive' => false],
            ['name' => 'invoices.payment_management', 'module' => 'Invoices', 'label' => 'Payment Management', 'is_sensitive' => false],
            ['name' => 'invoices.delete', 'module' => 'Invoices', 'label' => 'Delete Invoices', 'is_sensitive' => true],

            // Settings
            ['name' => 'settings.view', 'module' => 'Settings', 'label' => 'View Settings', 'is_sensitive' => true],
            ['name' => 'settings.edit', 'module' => 'Settings', 'label' => 'Edit Settings', 'is_sensitive' => true],

            // Subaccounts
            ['name' => 'subaccounts.view', 'module' => 'Subaccounts', 'label' => 'View Subaccounts', 'is_sensitive' => true],
            ['name' => 'subaccounts.create', 'module' => 'Subaccounts', 'label' => 'Create Subaccounts', 'is_sensitive' => true],
            ['name' => 'subaccounts.edit', 'module' => 'Subaccounts', 'label' => 'Edit Subaccounts', 'is_sensitive' => true],
            ['name' => 'subaccounts.delete', 'module' => 'Subaccounts', 'label' => 'Delete Subaccounts', 'is_sensitive' => true],
        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(['name' => $perm['name']], $perm);
        }
    }
}
