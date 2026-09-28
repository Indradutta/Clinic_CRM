<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prescription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'prescription_number',
        'patient_id',
        'doctor_id',
        'appointment_id',
        'consultation_id',
        'prescription_date',
        'diagnosis',
        'symptoms',
        'clinical_notes',
        'tests',
        'advice',
        'follow_up_date',
    ];

    protected $casts = [
        'prescription_date' => 'date',
        'follow_up_date' => 'date',
        'tests' => 'array',
    ];

    /**
     * Auto-generate sequential Prescription Number (RX-00001 format).
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Prescription $prescription): void {
            if (empty($prescription->prescription_number)) {
                $maxId = static::withTrashed()->max('id') ?? 0;
                $prescription->prescription_number = 'RX-'.str_pad((string) ($maxId + 1), 5, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Patient relation.
     *
     * @return BelongsTo<Patient, $this>
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Doctor (User) relation.
     *
     * @return BelongsTo<User, $this>
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * Linked Appointment relation.
     *
     * @return BelongsTo<Appointment, $this>
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Linked Clinical Consultation relation.
     *
     * @return BelongsTo<Consultation, $this>
     */
    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    /**
     * Itemized medicine prescriptions.
     *
     * @return HasMany<PrescriptionItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class);
    }
}
