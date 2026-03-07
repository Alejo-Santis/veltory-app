<?php

namespace App\Models;

use App\Enums\TransferStatus;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transfer extends Model
{
    use HasUuid;

    protected $fillable = [
        'reference', 'from_warehouse_id', 'to_warehouse_id',
        'status', 'requested_by', 'approved_by', 'notes',
        'requested_at', 'approved_at', 'shipped_at',
        'completed_at', 'cancelled_at',
    ];

    protected $casts = [
        'status'        => TransferStatus::class,
        'requested_at'  => 'datetime',
        'approved_at'   => 'datetime',
        'shipped_at'    => 'datetime',
        'completed_at'  => 'datetime',
        'cancelled_at'  => 'datetime',
    ];

    // ── Relaciones ────────────────────────────────────────────

    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransferItem::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ── Helpers ───────────────────────────────────────────────

    public function canTransitionTo(TransferStatus $newStatus): bool
    {
        return match($this->status) {
            TransferStatus::DRAFT      => in_array($newStatus, [TransferStatus::REQUESTED, TransferStatus::CANCELLED]),
            TransferStatus::REQUESTED  => in_array($newStatus, [TransferStatus::APPROVED,  TransferStatus::CANCELLED]),
            TransferStatus::APPROVED   => in_array($newStatus, [TransferStatus::IN_TRANSIT, TransferStatus::CANCELLED]),
            TransferStatus::IN_TRANSIT => in_array($newStatus, [TransferStatus::COMPLETED,  TransferStatus::CANCELLED]),
            default                    => false,
        };
    }

    // Genera referencia automática: TRF-20260001
    public static function generateReference(): string
    {
        $year  = now()->year;
        $count = static::whereYear('created_at', $year)->count() + 1;
        return 'TRF-' . $year . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
