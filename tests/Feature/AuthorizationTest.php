<?php

use App\Models\agentsdetailsModel;
use App\Models\policy;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

function userWithRole(string $role, array $attrs = []): User
{
    $user = User::factory()->create($attrs);
    $user->forceFill(['role' => $role, 'account_status' => 'active'])->save();

    return $user;
}

function draftPolicyFor(User $owner): policy
{
    $p = new policy();
    $p->forceFill([
        'insured_id'   => $owner->id,
        'insured_name' => 'Test Insured',
        'agent_id'     => $owner->id,
        'status'       => 'draft',
        'start_date'   => now()->toDateString(),
        'end_date'     => now()->addYear()->toDateString(),
        'create_uid'   => $owner->id,
        'producttype'  => 'Private Motor Third Party',
        'contribution' => 15000,
    ])->save();

    return $p;
}

// ── C-1/C-2/C-3: privileged routes are role-gated ─────────────────────────────

dataset('adminOnlyRoutes', [
    'list users'          => ['get', '/list_users'],
    'update user'         => ['post', '/update_user'],
    'list agents'         => ['get', '/list_agents'],
    'agent profile'       => ['get', '/agent_profile'],
    'agent update'        => ['post', '/agent_update'],
    'generate api token'  => ['get', '/generate-api-token?auid=1'],
    'elite brokers'       => ['get', '/elite/brokers'],
    'elite create acct'   => ['post', '/elite/brokers/create-account'],
    'niip code mgmt'      => ['get', '/niip_code_mgmt'],
    'retry niip'          => ['get', '/retry_niip?policyno=X'],
    'ecmr index'          => ['get', '/ecmr_index'],
    'admin jobs'          => ['get', '/admin/jobs'],
    'niip manual import'  => ['post', '/niip-manual-import/import'],
    'import states'       => ['post', '/import_states'],
    'update states'       => ['get', '/update-states'],
]);

it('forbids non-admin roles from privileged routes', function (string $method, string $uri) {
    foreach (['user', 'agent', 'subagent', 'broker'] as $role) {
        $this->actingAs(userWithRole($role))
            ->call(strtoupper($method), $uri)
            ->assertForbidden();
    }
})->with('adminOnlyRoutes');

it('redirects guests away from privileged routes', function (string $method, string $uri) {
    $this->call(strtoupper($method), $uri)->assertRedirect('/login');
})->with('adminOnlyRoutes');

it('lets a superadmin past the role gate', function () {
    $this->actingAs(userWithRole('superadmin'))->get('/list_agents')->assertOk();
});

it('keeps the removed test endpoint gone', function () {
    $this->actingAs(userWithRole('superadmin'))->get('/test_async')->assertNotFound();
});

// ── C-1: role escalation ──────────────────────────────────────────────────────

it('does not let a normal user promote themselves', function () {
    $user = userWithRole('user');

    $this->actingAs($user)->post('/update_user', ['uid' => $user->id, 'role' => 'superadmin'])->assertForbidden();

    expect($user->fresh()->role)->toBe('user');
});

it('only lets a superadmin grant admin roles', function () {
    $admin  = userWithRole('admin');
    $target = userWithRole('user');

    $this->actingAs($admin)->post('/update_user', ['uid' => $target->id, 'role' => 'superadmin'])->assertForbidden();
    expect($target->fresh()->role)->toBe('user');

    $this->actingAs($admin)->post('/update_user', ['uid' => $target->id, 'role' => 'agent'])->assertRedirect();
    expect($target->fresh()->role)->toBe('agent');
});

it('rejects unknown roles', function () {
    $super  = userWithRole('superadmin');
    $target = userWithRole('user');

    $this->actingAs($super)->post('/update_user', ['uid' => $target->id, 'role' => 'godmode'])->assertSessionHasErrors('role');
});

// ── C-3: password reset is limited to the parent agent / admins ───────────────

it('lets only the parent agent or an admin reset a sub-agent password', function () {
    $parent = userWithRole('agent');
    $sub    = userWithRole('subagent', ['parentid' => $parent->id]);
    $other  = userWithRole('agent');
    $payload = ['password' => 'Str0ngPassw0rd', 'password_confirmation' => 'Str0ngPassw0rd'];

    $this->actingAs($other)->post("/subagent/{$sub->id}/reset-password", $payload)->assertForbidden();
    $this->actingAs(userWithRole('user'))->post("/subagent/{$sub->id}/reset-password", $payload)->assertForbidden();
    $this->actingAs($parent)->post("/subagent/{$sub->id}/reset-password", $payload)->assertSessionHasNoErrors();
});

it('never lets an agent reset an admin password', function () {
    $admin = userWithRole('admin');

    $this->actingAs(userWithRole('agent'))
        ->post("/subagent/{$admin->id}/reset-password", ['password' => 'Str0ngPassw0rd', 'password_confirmation' => 'Str0ngPassw0rd'])
        ->assertForbidden();
});

// ── C-8: sub-agent registration is bound to the caller ───────────────────────

it('refuses to register a sub-agent under a different agent', function () {
    $victim   = userWithRole('agent');
    $attacker = userWithRole('agent');
    agentsdetailsModel::create(['uid' => $victim->id, 'canregistersubagent' => true, 'status' => 'activated', 'private_allocated' => 50]);

    $this->actingAs($attacker)->post("/sub_agents/register_new/{$victim->id}", [
        'firstname' => 'A', 'lastname' => 'B', 'phone' => '0800', 'email' => 'x@example.com',
        'password' => 'Str0ngPassw0rd', 'subcredit_private' => 5, 'subcredit_commercial' => 0,
    ])->assertForbidden();

    expect(User::where('email', 'x@example.com')->exists())->toBeFalse();
});

it('only exposes a sub-agent profile to the parent or an admin', function () {
    $parent = userWithRole('agent');
    $sub    = userWithRole('subagent', ['parentid' => $parent->id]);

    $this->actingAs(userWithRole('agent'))->get("/sub_agent/profile/{$sub->id}")->assertForbidden();
    $this->actingAs(userWithRole('user'))->get("/sub_agent/profile/{$sub->id}")->assertForbidden();
});

// ── C-7: policy object-level authorization ───────────────────────────────────

it('blocks viewing another user\'s policy', function () {
    $owner  = userWithRole('agent');
    $policy = draftPolicyFor($owner);

    $this->actingAs(userWithRole('agent'))->get("/view_policy?id={$policy->id}")->assertForbidden();
    $this->actingAs(userWithRole('user'))->get("/view_policy?id={$policy->id}")->assertForbidden();
});

it('blocks initiating payment on another user\'s policy', function () {
    $policy = draftPolicyFor(userWithRole('agent'));

    $this->actingAs(userWithRole('agent'))->get("/init_paystack/{$policy->id}")->assertForbidden();
});

it('blocks the payment confirmation page for another user\'s policy', function () {
    $policy = draftPolicyFor(userWithRole('agent'));

    $this->actingAs(userWithRole('agent'))->get("/policy/{$policy->id}/confirm")->assertForbidden();
});

// ── C-5: payment endpoint ────────────────────────────────────────────────────

it('refuses to pay for another user\'s policy', function () {
    $policy = draftPolicyFor(userWithRole('agent'));

    $this->actingAs(userWithRole('agent'))
        ->post('/policy/pay_policy', ['policyid' => $policy->id, 'agencycredit' => 1])
        ->assertForbidden();

    expect($policy->fresh()->status)->toBe('draft');
});

it('does not issue a policy when no payment method is supplied', function () {
    $owner  = userWithRole('agent');
    $policy = draftPolicyFor($owner);

    $this->actingAs($owner)
        ->post('/policy/pay_policy', ['policyid' => $policy->id])
        ->assertOk()
        ->assertViewIs('user_errors');

    expect($policy->fresh()->status)->toBe('draft');
});

it('refuses to re-issue an already approved policy', function () {
    $owner  = userWithRole('agent');
    $policy = draftPolicyFor($owner);
    $policy->forceFill(['status' => 'approved', 'policyno' => 'SLM/MTR/2026/1'])->save();

    $this->actingAs($owner)
        ->post('/policy/pay_policy', ['policyid' => $policy->id, 'agencycredit' => 1])
        ->assertViewIs('user_errors');
});

it('does not charge credit when the agent has none', function () {
    $owner = userWithRole('agent');
    agentsdetailsModel::create([
        'uid' => $owner->id, 'allowcredit' => true, 'status' => 'activated',
        'private_allocated' => 1, 'private_used' => 1,
    ]);
    $policy = draftPolicyFor($owner);

    $this->actingAs($owner)
        ->post('/policy/pay_policy', ['policyid' => $policy->id, 'agencycredit' => 1])
        ->assertViewIs('user_errors');

    expect(agentsdetailsModel::where('uid', $owner->id)->first()->private_used)->toBe(1);
});

it('releases reserved credit when Elite rejects the policy', function () {
    config(['variables.API_ELITE_URL' => 'https://elite.test/api']);
    \Illuminate\Support\Facades\Http::fake(['elite.test/*' => \Illuminate\Support\Facades\Http::response(['data' => ['status' => 'error', 'message' => 'nope']])]);

    $owner = userWithRole('agent');
    agentsdetailsModel::create([
        'uid' => $owner->id, 'allowcredit' => true, 'status' => 'activated',
        'private_allocated' => 5, 'private_used' => 0, 'auth_token' => 'tok',
    ]);
    $policy = draftPolicyFor($owner);
    (new \App\Models\policyrisk())->forceFill([
        'product_id' => 1, 'policyid' => $policy->id, 'regno' => 'ABC123', 'engineno' => 'E', 'chassisno' => 'C',
        'vehiclemake' => 'M', 'vehiclemodel' => 'M', 'yearofmake' => 2020, 'vehiclecolor' => 'Red', 'vechiclecolorid' => 1, 'contribution' => 15000,
    ])->save();

    $this->actingAs($owner)->post('/policy/pay_policy', ['policyid' => $policy->id, 'agencycredit' => 1]);

    expect(agentsdetailsModel::where('uid', $owner->id)->first()->private_used)->toBe(0)
        ->and($policy->fresh()->status)->toBe('failed');
});
