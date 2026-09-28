<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    // 7 Lifecycle Statuses (Sec 4, p. 3)
    public const STATUS_PENDING = 'pending';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_CHECKED_IN = 'checked_in';

    public const STATUS_IN_CONSULTATION = 'in_consultation';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_NO_SHOW = 'no_show';

    // Payment Statuses (Sec 4, p. 3)
    public const PAYMENT_UNPAID = 'unpaid';

    public const PAYMENT_PAID = 'paid';

    public const PAYMENT_PARTIALLY_PAID = 'partially_paid';

    protected $fillable = [
        'appointment_id',
        'patient_id',
        'doctor_id',
        'appointment_date',
        'appointment_time',
        'appointment_type',
        'reason_for_visit',
        'notes',
        'status',
        'payment_status',
        'cancelled_reason',
    ];

    protected $casts = [
        'appointment_date' => 'date',
    ];

    /**
     * Auto-generate sequential Appointment ID (APT-00001 format).
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Appointment $appointment): void {
            if (empty($appointment->appointment_id)) {
                $maxId = static::withTrashed()->max('id') ?? 0;
                $appointment->appointment_id = 'APT-'.str_pad((string) ($maxId + 1), 5, '0', STR_PAD_LEFT);
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
     * Clinical Consultation encounter.
     *
     * @return HasOne<Consultation, $this>
     */
    public function consultation(): HasOne
    {
        return $this->hasOne(Consultation::class);
    }

    /**
     * Digital Prescription issued for this appointment.
     *
     * @return HasOne<Prescription, $this>
     */
    public function prescription(): HasOne
    {
        return $this->hasOne(Prescription::class);
    }

    // --- Scopes ---

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('appointment_date', now()->toDateString());
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereDate('appointment_date', '>=', now()->toDateString())
            ->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeCheckedIn(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CHECKED_IN);
    }

    public function scopeInConsultation(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_IN_CONSULTATION);
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    // --- UI Helpers ---

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_CONFIRMED => 'Confirmed',
            self::STATUS_CHECKED_IN => 'Checked In',
            self::STATUS_IN_CONSULTATION => 'In Consultation',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_NO_SHOW => 'No Show',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_CONFIRMED => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::STATUS_CHECKED_IN => 'bg-blue-50 text-blue-700 border-blue-200',
            self::STATUS_IN_CONSULTATION => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            self::STATUS_COMPLETED => 'bg-slate-100 text-slate-700 border-slate-300',
            self::STATUS_CANCELLED => 'bg-rose-50 text-rose-700 border-rose-200',
            self::STATUS_NO_SHOW => 'bg-amber-50 text-amber-700 border-amber-200',
            default => 'bg-yellow-50 text-yellow-800 border-yellow-200', // pending
        };
    }

    public function getPaymentBadgeClassAttribute(): string
    {
        return match ($this->payment_status) {
            self::PAYMENT_PAID => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::PAYMENT_PARTIALLY_PAID => 'bg-amber-50 text-amber-700 border-amber-200',
            default => 'bg-rose-50 text-rose-700 border-rose-200', // unpaid
        };
    }
}
