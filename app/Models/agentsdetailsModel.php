<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class agentsdetailsModel extends Model
{
    protected $fillable = [
        'uid', 'allowcredit', 'status', 'auth_token', 'puid', 'issubagent', 'canregistersubagent',

        // Total credit counters (kept in sync as sums of typed counters below)
        'noallocated', 'noused',
        'subcreditassigned', 'subcreditused',

        // Per-type credit counters — agent / parent-agent rows
        'private_allocated', 'private_used',
        'commercial_allocated', 'commercial_used',

        // Per-type pool — parent-agent rows
        'pool_enabled',
        'pool_private_size', 'pool_private_used',
        'pool_commercial_size', 'pool_commercial_used',

        // Per-type subagent individual allocation
        'subcreditassigned_private', 'subcreditused_private',
        'subcreditassigned_commercial', 'subcreditused_commercial',

        // Per-type subagent pool caps
        'pool_cap_private', 'pool_cap_used_private',
        'pool_cap_commercial', 'pool_cap_used_commercial',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function getuserinfo()
    {
        return User::where('id', $this->uid)->first();
    }

    public function getagg()
    {
        return policy::where('agent_id', $this->uid)->get();
    }

    public function getparentdetails()
    {
        return User::where('id', $this->puid)->first();
    }

    /** Returns the parent agent's agentsdetailsModel row (subagent rows only). */
    public function parentAgentDetails(): ?self
    {
        if (!$this->puid) return null;
        return self::where('uid', $this->puid)->first();
    }

    // ── Per-type availability helpers ──────────────────────────────────────

    public function availablePrivate(): int
    {
        return max(0, $this->private_allocated - $this->private_used);
    }

    public function availableCommercial(): int
    {
        return max(0, $this->commercial_allocated - $this->commercial_used);
    }

    /** Returns available credits for the given credit type ('private' or 'commercial'). */
    public function availableByType(string $type): int
    {
        return $type === 'commercial' ? $this->availableCommercial() : $this->availablePrivate();
    }

    // ── Total sync helpers ─────────────────────────────────────────────────

    /** Recalculate noallocated/noused from typed counters and save. */
    public function syncTotals(): void
    {
        $this->noallocated = $this->private_allocated + $this->commercial_allocated;
        $this->noused      = $this->private_used      + $this->commercial_used;
    }

    /** Recalculate subcredit totals from typed counters (subagent rows). */
    public function syncSubcreditTotals(): void
    {
        $this->subcreditassigned = $this->subcreditassigned_private + $this->subcreditassigned_commercial;
        $this->subcreditused     = $this->subcreditused_private     + $this->subcreditused_commercial;
    }
}
