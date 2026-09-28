<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN_DOCTOR = 'admin_doctor';

    public const ROLE_DOCTOR = 'doctor';

    public const ROLE_CLINIC_MANAGER = 'clinic_manager';

    public const ROLE_RECEPTIONIST = 'receptionist';

    public const ROLE_NURSE = 'nurse';

    public const ROLE_ASSISTANT = 'assistant';

    public const ROLE_ACCOUNTANT = 'accountant';

    public const ROLE_OTHER_STAFF = 'other_staff';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'phone',
        'password',
        'role',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * @return BelongsToMany<Permission, $this>
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permissions');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isAdminDoctor(): bool
    {
        return $this->role === self::ROLE_ADMIN_DOCTOR;
    }

    public function isClinicManager(): bool
    {
        return $this->role === self::ROLE_CLINIC_MANAGER;
    }

    public function isReceptionist(): bool
    {
        return $this->role === self::ROLE_RECEPTIONIST;
    }

    public function isNurse(): bool
    {
        return in_array($this->role, [self::ROLE_NURSE, self::ROLE_ASSISTANT], true);
    }

    public function isAccountant(): bool
    {
        return $this->role === self::ROLE_ACCOUNTANT;
    }

    /**
     * Check if user has permission either explicitly or through role baseline.
     */
    public function hasPermission(string $permissionName): bool
    {
        // Admin / Doctor has full access to the CRM (Page 13)
        if ($this->isAdminDoctor()) {
            return true;
        }

        // Direct user permission override
        $hasDirectPermission = $this->relationLoaded('permissions')
            ? $this->permissions->contains('name', $permissionName)
            : $this->permissions()->where('name', $permissionName)->exists();

        if ($hasDirectPermission) {
            return true;
        }

        // Default role permissions per Section 10 (Page 13)
        return match ($this->role) {
            self::ROLE_DOCTOR => in_array($permissionName, ['dashboard.view', 'doctors.view', 'appointments.view', 'appointments.create', 'appointments.edit', 'patients.view', 'patients.create', 'patients.edit', 'prescriptions.view', 'prescriptions.create', 'prescriptions.edit', 'consultations.view', 'consultations.create', 'consultations.edit'], true),
            self::ROLE_CLINIC_MANAGER => in_array($permissionName, [
                'dashboard.view',
                'appointments.view', 'appointments.create', 'appointments.edit', 'appointments.cancel',
                'patients.view', 'patients.create', 'patients.edit',
                'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.payment_management',
                'consultations.view', 'consultations.create', 'consultations.edit',
                'reports.view', 'reports.export', 'reports.print',
            ], true),
            self::ROLE_RECEPTIONIST => in_array($permissionName, [
                'dashboard.view',
                'appointments.view', 'appointments.create', 'appointments.edit', 'appointments.cancel',
                'patients.view', 'patients.create', 'patients.edit',
            ], true),
            self::ROLE_NURSE, self::ROLE_ASSISTANT => in_array($permissionName, [
                'patients.view',
                'appointments.view',
                'prescriptions.view',
                'consultations.view', 'consultations.create',
            ], true),
            self::ROLE_ACCOUNTANT => in_array($permissionName, [
                'dashboard.view',
                'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.payment_management',
                'reports.view', 'reports.export', 'reports.print',
            ], true),
            default => false,
        };
    }

    /**
     * @return HasMany<Appointment, $this>
     */
    public function doctorAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'doctor_id');
    }

    /**
     * @return HasMany<Consultation, $this>
     */
    public function doctorConsultations(): HasMany
    {
        return $this->hasMany(Consultation::class, 'doctor_id');
    }

    /**
     * @return HasMany<Prescription, $this>
     */
    public function doctorPrescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class, 'doctor_id');
    }

    /** @return HasOne<DoctorProfile, $this> */
    public function doctorProfile(): HasOne
    {
        return $this->hasOne(DoctorProfile::class);
    }
}
