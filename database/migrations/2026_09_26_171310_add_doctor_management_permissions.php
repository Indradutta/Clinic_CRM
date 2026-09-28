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
            ['name' => 'doctors.view', 'module' => 'Doctors', 'label' => 'View Doctors', 'is_sensitive' => false],
            ['name' => 'doctors.create', 'module' => 'Doctors', 'label' => 'Add Doctors', 'is_sensitive' => true],
            ['name' => 'doctors.edit', 'module' => 'Doctors', 'label' => 'Edit Doctors', 'is_sensitive' => true],
            ['name' => 'doctors.delete', 'module' => 'Doctors', 'label' => 'Remove Doctors', 'is_sensitive' => true],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(['name' => $permission['name']], $permission);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('permissions')->whereIn('name', ['doctors.view', 'doctors.create', 'doctors.edit', 'doctors.delete'])->delete();
    }
};
