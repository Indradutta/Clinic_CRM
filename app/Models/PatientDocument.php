<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'user_id',
        'title',
        'file_path',
        'file_type',
        'file_size',
    ];

    /**
     * @return BelongsTo<Patient, $this>
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Human-readable file size.
     */
    public function getFormattedSizeAttribute(): string
    {
        if (! $this->file_size) {
            return '—';
        }

        if ($this->file_size < 1024) {
            return $this->file_size.' B';
        }

        if ($this->file_size < 1048576) {
            return round($this->file_size / 1024, 1).' KB';
        }

        return round($this->file_size / 1048576, 2).' MB';
    }
}
