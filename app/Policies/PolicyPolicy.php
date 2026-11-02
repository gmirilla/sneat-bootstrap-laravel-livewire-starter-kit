<?php

namespace App\Policies;

use App\Models\policy;
use App\Models\User;

class PolicyPolicy
{
    private function isAdmin(User $user): bool
    {
        return in_array($user->role, ['admin', 'superadmin'], true);
    }

    /** Created it, is the insured, or is the parent agent of the sub-agent who created it. */
    private function owns(User $user, policy $policy): bool
    {
        if ((int) $policy->agent_id === (int) $user->id || (int) $policy->insured_id === (int) $user->id) {
            return true;
        }

        return $user->role === 'agent'
            && User::where('id', $policy->agent_id)->where('parentid', $user->id)->exists();
    }

    public function view(User $user, policy $policy): bool
    {
        return $this->isAdmin($user) || $this->owns($user, $policy);
    }

    /** Edit / pay: owner or admin, and only while the policy has not been issued. */
    public function update(User $user, policy $policy): bool
    {
        return ($this->isAdmin($user) || $this->owns($user, $policy))
            && in_array($policy->status, ['draft', 'failed'], true);
    }
}
