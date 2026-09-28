<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permissions = [
            ['name' => 'consultations.view', 'module' => 'Consultations', 'label' => 'View Consultations'],
            ['name' => 'consultations.create', 'module' => 'Consultations', 'label' => 'Create Consultations'],
            ['name' => 'consultations.edit', 'module' => 'Consultations', 'label' => 'Edit Consultations'],
            ['name' => 'consultations.delete', 'module' => 'Consultations', 'label' => 'Delete Consultations'],
            ['name' => 'reports.view', 'module' => 'Reports', 'label' => 'View Reports'],
            ['name' => 'reports.export', 'module' => 'Reports', 'label' => 'Export Reports'],
            ['name' => 'reports.print', 'module' => 'Reports', 'label' => 'Print Reports'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission['name']],
                [...$permission, 'is_sensitive' => true, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('permissions')
            ->whereIn('name', [
                'consultations.view',
                'consultations.create',
                'consultations.edit',
                'consultations.delete',
                'reports.view',
                'reports.export',
                'reports.print',
            ])
            ->delete();
    }
};
