<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Consultation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'appointment_id',
        'consultation_date',
        'symptoms',
        'diagnosis',
        'vitals',
        'medical_notes',
        'treatment',
    ];

    protected $casts = [
        'consultation_date' => 'datetime',
        'vitals' => 'array',
    ];

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
     * Appointment relation.
     *
     * @return BelongsTo<Appointment, $this>
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Linked Digital Prescription relation.
     *
     * @return HasOne<Prescription, $this>
     */
    public function prescription(): HasOne
    {
        return $this->hasOne(Prescription::class);
    }

    /**
     * Formatted string summary of recorded vitals.
     */
    public function getVitalsSummaryAttribute(): string
    {
        if (empty($this->vitals) || ! is_array($this->vitals)) {
            return 'Vitals not recorded';
        }

        $parts = [];
        if (! empty($this->vitals['bp'])) {
            $parts[] = 'BP: '.$this->vitals['bp'].' mmHg';
        }
        if (! empty($this->vitals['pulse'])) {
            $parts[] = 'Pulse: '.$this->vitals['pulse'].' bpm';
        }
        if (! empty($this->vitals['temperature'])) {
            $parts[] = 'Temp: '.$this->vitals['temperature'].'°F';
        }
        if (! empty($this->vitals['spo2'])) {
            $parts[] = 'SpO2: '.$this->vitals['spo2'].'%';
        }
        if (! empty($this->vitals['weight_kg'])) {
            $parts[] = 'Wt: '.$this->vitals['weight_kg'].' kg';
        }

        return empty($parts) ? 'Vitals not recorded' : implode(' • ', $parts);
    }
}
