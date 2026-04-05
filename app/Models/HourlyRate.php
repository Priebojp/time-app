<?php

namespace App\Models;

use Database\Factories\HourlyRateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HourlyRate extends Model
{
    /** @use HasFactory<HourlyRateFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_id',
        'rate',
        'currency',
        'valid_from',
        'valid_to',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'valid_from' => 'date',
            'valid_to' => 'date',
        ];
    }

    /**
     * Get the user that the hourly rate belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the company that the hourly rate belongs to.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
