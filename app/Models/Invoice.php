<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_UNPAID = 'unpaid';

    public const STATUS_PARTIALLY_PAID = 'partially_paid';

    public const STATUS_PAID = 'paid';

    public const STATUS_OVERDUE = 'overdue';

    public const STATUS_CANCELLED = 'cancelled';

    public const DISCOUNT_FIXED = 'fixed';

    public const DISCOUNT_PERCENTAGE = 'percentage';

    protected $fillable = [
        'invoice_number',
        'patient_id',
        'doctor_id',
        'appointment_id',
        'prescription_id',
        'invoice_date',
        'due_date',
        'subtotal',
        'tax_percentage',
        'tax_amount',
        'discount_type',
        'discount_value',
        'discount_amount',
        'grand_total',
        'paid_amount',
        'balance_due',
        'status',
        'notes',
        'terms',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_due' => 'decimal:2',
    ];

    /**
     * Auto-generate sequential Invoice Number (INV-XXXXX).
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Invoice $invoice): void {
            if (empty($invoice->invoice_number)) {
                $prefix = Setting::get('invoice_prefix', 'INV-');
                $maxId = static::withTrashed()->max('id') ?? 0;
                $invoice->invoice_number = $prefix.str_pad((string) ($maxId + 1), 5, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Recalculate subtotal, taxes, discounts, balance due, and automated status.
     */
    public function recalculateTotals(): void
    {
        $hasItems = $this->items()->exists();
        $subtotal = $hasItems ? (float) $this->items()->sum('total') : (float) $this->subtotal;

        // Discount computation
        $discountAmount = 0.00;
        if ($this->discount_type === self::DISCOUNT_PERCENTAGE) {
            $discountAmount = round(($subtotal * (float) $this->discount_value) / 100, 2);
        } else {
            $discountAmount = min((float) $this->discount_value, $subtotal);
        }

        // Tax computation on post-discount subtotal
        $taxable = max(0.00, $subtotal - $discountAmount);
        $taxAmount = round(($taxable * (float) $this->tax_percentage) / 100, 2);

        $grandTotal = $hasItems ? round($taxable + $taxAmount, 2) : ((float) $this->grand_total > 0 ? (float) $this->grand_total : round($taxable + $taxAmount, 2));
        $paidAmount = (float) $this->payments()->sum('amount');
        $balanceDue = max(0.00, round($grandTotal - $paidAmount, 2));

        // Status transition logic
        $status = $this->status;
        if ($status !== self::STATUS_CANCELLED) {
            if ($grandTotal > 0 && $paidAmount >= $grandTotal) {
                $status = self::STATUS_PAID;
            } elseif ($paidAmount > 0) {
                $status = self::STATUS_PARTIALLY_PAID;
            } elseif ($this->due_date && $this->due_date->isPast() && $this->due_date->toDateString() < now()->toDateString()) {
                $status = self::STATUS_OVERDUE;
            } else {
                $status = self::STATUS_UNPAID;
            }
        }

        $this->updateQuietly([
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'grand_total' => $grandTotal,
            'paid_amount' => $paidAmount,
            'balance_due' => $balanceDue,
            'status' => $status,
        ]);
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
     * Associated Appointment relation.
     *
     * @return BelongsTo<Appointment, $this>
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Associated Prescription relation.
     *
     * @return BelongsTo<Prescription, $this>
     */
    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    /**
     * Invoice Line Items relation.
     *
     * @return HasMany<InvoiceItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Payments transactions relation.
     *
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest('payment_date');
    }

    /**
     * Badge CSS class for status.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PAID => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::STATUS_PARTIALLY_PAID => 'bg-amber-50 text-amber-700 border-amber-200',
            self::STATUS_OVERDUE => 'bg-rose-50 text-rose-700 border-rose-200',
            self::STATUS_CANCELLED => 'bg-slate-100 text-slate-500 border-slate-200',
            default => 'bg-blue-50 text-blue-700 border-blue-200',
        };
    }

    /**
     * Human-friendly label for status.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PAID => 'Paid in Full',
            self::STATUS_PARTIALLY_PAID => 'Partially Paid',
            self::STATUS_OVERDUE => 'Overdue',
            self::STATUS_CANCELLED => 'Cancelled',
            default => 'Unpaid',
        };
    }
}
