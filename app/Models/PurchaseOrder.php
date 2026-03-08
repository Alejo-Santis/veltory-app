<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasUuid, SoftDeletes;

    protected $fillable = [
        'reference', 'supplier_id', 'warehouse_id', 'status',
        'expected_at', 'sent_at', 'received_at', 'cancelled_at',
        'created_by', 'notes',
    ];

    protected $casts = [
        'expected_at'  => 'date',
        'sent_at'      => 'datetime',
        'received_at'  => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // ── Relaciones ────────────────────────────────────────────
    public function supplier()  { return $this->belongsTo(Supplier::class); }
    public function warehouse() { return $this->belongsTo(Warehouse::class); }
    public function items()     { return $this->hasMany(PurchaseOrderItem::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }

    // ── Accesors ──────────────────────────────────────────────
    public function getTotalAttribute(): float
    {
        return $this->items->sum(fn ($item) => $item->quantity_ordered * ($item->unit_cost ?? 0));
    }

    public function getPendingItemsAttribute(): bool
    {
        return $this->items->some(
            fn ($item) => $item->quantity_received < $item->quantity_ordered
        );
    }

    // ── Helpers de estado ─────────────────────────────────────
    public function isDraft():     bool { return $this->status === 'draft'; }
    public function isSent():      bool { return $this->status === 'sent'; }
    public function isPartial():   bool { return $this->status === 'partial'; }
    public function isReceived():  bool { return $this->status === 'received'; }
    public function isCancelled(): bool { return $this->status === 'cancelled'; }
    public function canEdit():     bool { return $this->isDraft(); }
    public function canReceive():  bool { return in_array($this->status, ['sent', 'partial']); }
    public function canCancel():   bool { return in_array($this->status, ['draft', 'sent', 'partial']); }
}
