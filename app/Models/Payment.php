<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    public const METHOD_CASH = 'Cash';

    public const METHOD_CARD = 'Card';

    public const METHOD_UPI = 'UPI';

    public const METHOD_BANK_TRANSFER = 'Bank Transfer';

    public const METHOD_INSURANCE = 'Insurance';

    public const METHOD_CHEQUE = 'Cheque';

    protected $fillable = [
        'receipt_number',
        'invoice_id',
        'patient_id',
        'received_by',
        'payment_date',
        'amount',
        'payment_method',
        'transaction_reference',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'amount' => 'decimal:2',
    ];

    /**
     * Auto-generate sequential Receipt Number (RCT-XXXXX) and recalculate invoice totals.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Payment $payment): void {
            if (empty($payment->receipt_number)) {
                $maxId = static::withTrashed()->max('id') ?? 0;
                $payment->receipt_number = 'RCT-'.str_pad((string) ($maxId + 1), 5, '0', STR_PAD_LEFT);
            }
        });

        static::created(function (Payment $payment): void {
            $payment->invoice?->recalculateTotals();
        });

        static::deleted(function (Payment $payment): void {
            $payment->invoice?->recalculateTotals();
        });
    }

    /**
     * Parent invoice relation.
     *
     * @return BelongsTo<Invoice, $this>
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
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
     * Staff receiver relation.
     *
     * @return BelongsTo<User, $this>
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Method badge style helper.
     */
    public function getMethodBadgeClassAttribute(): string
    {
        return match ($this->payment_method) {
            self::METHOD_CASH => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::METHOD_UPI => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            self::METHOD_CARD => 'bg-blue-50 text-blue-700 border-blue-200',
            self::METHOD_INSURANCE => 'bg-purple-50 text-purple-700 border-purple-200',
            self::METHOD_BANK_TRANSFER => 'bg-cyan-50 text-cyan-700 border-cyan-200',
            default => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    }
}
