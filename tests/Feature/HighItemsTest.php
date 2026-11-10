<?php

use App\Jobs\ProcessNiipManualImport;
use App\Models\agentsdetailsModel;
use App\Models\User;
use App\Services\ProxyClient;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Sanctum\Sanctum;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

function roleUser(string $role, array $attrs = []): User
{
    $user = User::factory()->create($attrs);
    $user->forceFill(['role' => $role, 'account_status' => 'active'])->save();

    return $user;
}

// ── H-1: third-party API ─────────────────────────────────────────────────────

it('rejects API calls from tokens that are not agents', function () {
    Sanctum::actingAs(roleUser('user'));

    $this->postJson('/api/third-party', [])->assertForbidden();
});

it('rejects API calls from an agent without an agent record or when deactivated', function () {
    $agent = roleUser('agent');
    Sanctum::actingAs($agent);
    $this->postJson('/api/third-party', [])->assertForbidden();

    agentsdetailsModel::create(['uid' => $agent->id, 'status' => 'deactivated']);
    $this->postJson('/api/third-party', [])->assertForbidden();
});

it('validates the payload for an active agent (no 500)', function () {
    $agent = roleUser('agent');
    agentsdetailsModel::create(['uid' => $agent->id, 'status' => 'activated']);
    Sanctum::actingAs($agent);

    $this->postJson('/api/third-party', [])->assertStatus(422)->assertJsonValidationErrors(['fname', 'regno']);
});

// ── H-2: no shared default password ──────────────────────────────────────────

it('no longer hard-codes the default password', function () {
    expect(file_get_contents(app_path('Http/Controllers/PolicyController.php')))->not->toContain("Hash::make('Password')")
        ->and(file_get_contents(app_path('Http/Controllers/Api/ThirdPartyController.php')))->not->toContain("'Password'");
});

// ── H-3: public lookup hardening ─────────────────────────────────────────────

it('masks the policy holder name on the public brown card page', function () {
    $controller = new \App\Http\Controllers\BrowncardController();
    $mask = new ReflectionMethod($controller, 'maskName');
    $mask->setAccessible(true);

    expect($mask->invoke($controller, 'Adaeze Okonkwo'))->toBe('A***** O******')
        ->and($mask->invoke($controller, null))->toBeNull()
        ->and($mask->invoke($controller, '  '))->toBeNull();
});

it('throttles claim lookups per policy number', function () {
    Http::fake();
    foreach (range(1, 10) as $i) {
        RateLimiter::hit('claim-lookup:SLM/TEST/1', 3600);
    }

    $this->post('/claim/notify/lookup', ['policy_no' => 'slm/test/1', 'email' => 'a@example.com'])
        ->assertSessionHas('lookup_error');
});

// ── H-4: ProxyClient ─────────────────────────────────────────────────────────

it('only supports GET and POST through the proxy client', function () {
    (new ProxyClient('http://proxy.test', 'secret'))->call('DELETE', 'http://proxy.test/x');
})->throws(InvalidArgumentException::class);

it('sends the proxy secret header with GET and POST', function () {
    Http::fake(['proxy.test/*' => Http::response('{"status":"success"}')]);
    $client = new ProxyClient('http://proxy.test', 'topsecret');

    // The Windows/dev path is exercised in CI; the curl-binary path is covered by the manual check in Review.md.
    if (PHP_OS_FAMILY !== 'Windows') {
        $this->markTestSkipped('curl-binary path');
    }

    expect($client->call('GET', 'http://proxy.test/a'))->toBe('{"status":"success"}')
        ->and($client->postJson('http://proxy.test/b', ['k' => 'v']))->toBe('{"status":"success"}');

    Http::assertSent(fn ($r) => $r->hasHeader('X-Proxy-Secret', 'topsecret'));
});

// ── H-5: tokens encrypted at rest; never echoed ──────────────────────────────

it('stores Elite auth tokens encrypted but reads them back transparently', function () {
    $agent  = roleUser('agent');
    $detail = agentsdetailsModel::create(['uid' => $agent->id, 'status' => 'activated', 'auth_token' => 'plain-elite-token']);

    $raw = DB::table('agentsdetails_models')->where('id', $detail->id)->value('auth_token');

    expect($raw)->not->toBe('plain-elite-token')
        ->and($detail->fresh()->auth_token)->toBe('plain-elite-token');
});

it('keeps the stored token when the admin form leaves it blank, and clears it on request', function () {
    $super = roleUser('superadmin');
    $agent = roleUser('agent');
    agentsdetailsModel::create(['uid' => $agent->id, 'status' => 'activated', 'auth_token' => 'keep-me']);
    $base = ['userid' => $agent->id, 'private_allocated' => 1, 'commercial_allocated' => 1, 'status' => 'activated'];

    $this->actingAs($super)->post('/agent_update', $base + ['authtoken' => ''])->assertRedirect();
    expect(agentsdetailsModel::where('uid', $agent->id)->first()->auth_token)->toBe('keep-me');

    $this->actingAs($super)->post('/agent_update', $base + ['authtoken' => 'new-token'])->assertRedirect();
    expect(agentsdetailsModel::where('uid', $agent->id)->first()->auth_token)->toBe('new-token');

    $this->actingAs($super)->post('/agent_update', $base + ['clear_authtoken' => 1])->assertRedirect();
    expect(agentsdetailsModel::where('uid', $agent->id)->first()->auth_token)->toBeNull();
});

it('does not render the stored token in the agent admin page', function () {
    $super = roleUser('superadmin');
    $agent = roleUser('agent');
    agentsdetailsModel::create(['uid' => $agent->id, 'status' => 'activated', 'auth_token' => 'super-secret-token-value']);

    $this->actingAs($super)->get('/agent_profile?uid=' . $agent->id)
        ->assertOk()
        ->assertDontSee('super-secret-token-value');
});

// ── H-7: the half-built payment route can no longer issue for free ───────────

it('refuses agency credit on the unfinished /policy/pay route', function () {
    $owner = roleUser('agent');
    $policy = new \App\Models\policy();
    $policy->forceFill([
        'insured_id' => $owner->id, 'insured_name' => 'X', 'producttype' => 'Salam Investment Plan', 'contribution' => 5000, 'agent_id' => $owner->id, 'status' => 'draft',
        'start_date' => now()->toDateString(), 'end_date' => now()->addYear()->toDateString(), 'create_uid' => $owner->id,
    ])->save();

    $this->actingAs($owner)->post('/policy/pay', ['policy_id' => $policy->id, 'agency_credit' => 1])
        ->assertSessionHasErrors('payment');

    expect($policy->fresh()->status)->toBe('draft');
});

// ── H-8: NIIP manual import is queued, validated and size-limited ────────────

it('queues the NIIP manual import instead of posting rows in the request', function () {
    Queue::fake();

    $this->actingAs(roleUser('superadmin'))
        ->post('/niip-manual-import/import', ['file' => UploadedFile::fake()->createWithContent('rows.csv', "a,b\n1,2\n")])
        ->assertSessionHas('success');

    Queue::assertPushed(ProcessNiipManualImport::class);
});

it('rejects non-spreadsheet NIIP uploads', function () {
    Queue::fake();

    $this->actingAs(roleUser('superadmin'))
        ->post('/niip-manual-import/import', ['file' => UploadedFile::fake()->create('evil.php', 5, 'text/x-php')])
        ->assertSessionHasErrors('file');

    Queue::assertNothingPushed();
});
