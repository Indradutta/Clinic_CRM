<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'name',
        'dob',
        'age',
        'gender',
        'phone',
        'email',
        'address',
        'primary_concern',
        'emergency_contact_name',
        'emergency_contact_phone',
        'blood_group',
        'marital_status',
        'preferred_language',
        'height_cm',
        'weight_kg',
        'allergies',
        'chronic_conditions',
        'current_medications',
        'past_surgeries',
        'family_history',
        'smoking_habits',
        'alcohol_consumption',
    ];

    protected $casts = [
        'dob' => 'date',
        'age' => 'integer',
        'height_cm' => 'decimal:1',
        'weight_kg' => 'decimal:1',
    ];

    /**
     * Auto-generate unique Patient ID on creation (PAT-00001 format).
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($patient): void {
            if (empty($patient->patient_id)) {
                $maxId = static::withTrashed()->max('id') ?? 0;
                $patient->patient_id = 'PAT-'.str_pad((string) ($maxId + 1), 5, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * @return HasMany<PatientNote, $this>
     */
    public function notes(): HasMany
    {
        return $this->hasMany(PatientNote::class)->latest();
    }

    /**
     * @return HasMany<PatientDocument, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(PatientDocument::class)->latest();
    }

    /**
     * @return HasMany<Appointment, $this>
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class)->orderBy('appointment_date', 'desc')->orderBy('appointment_time', 'desc');
    }

    /**
     * @return HasMany<Consultation, $this>
     */
    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class)->latest('consultation_date');
    }

    /**
     * @return HasMany<Prescription, $this>
     */
    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class)->latest('prescription_date');
    }

    /**
     * @return HasMany<Invoice, $this>
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class)->latest('invoice_date');
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest('payment_date');
    }
}
