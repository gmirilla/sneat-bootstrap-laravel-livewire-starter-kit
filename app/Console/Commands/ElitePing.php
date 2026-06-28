<?php

namespace App\Console\Commands;

use App\Services\ProxyClient;
use Illuminate\Console\Command;

class ElitePing extends Command
{
    protected $signature   = 'elite:ping {--broker= : Also test policies for this broker_id}';
    protected $description = 'Test connectivity to the Elite proxy and broker APIs';

    public function handle(ProxyClient $proxy): int
    {
        $this->newLine();
        $this->line('<fg=cyan;options=bold>── Elite Proxy Connectivity Check ──</>');
        $this->newLine();

        // ── Config ────────────────────────────────────────────────────────────
        $url    = $proxy->getBaseUrl();
        $secret = config('variables.PROXY_SECRET', '');

        $this->line('  PROXY_URL    : ' . ($url    ?: '<fg=red>not set</>'));
        $this->line('  PROXY_SECRET : ' . ($secret ? str_repeat('*', max(4, strlen($secret) - 4)) . substr($secret, -4) : '<fg=red>not set</>'));
        $this->line('  OS family    : ' . PHP_OS_FAMILY);
        $this->newLine();

        if (!$proxy->isConfigured()) {
            $this->error('Proxy is not fully configured. Set PROXY_URL and PROXY_SECRET in .env.');
            return self::FAILURE;
        }

        // ── Test 1: List Brokers ──────────────────────────────────────────────
        $this->line('<options=bold>1. GET /api/elite/brokers</>');

        $start = microtime(true);
        $raw   = $proxy->call('GET', $url . '/api/elite/brokers');
        $ms    = round((microtime(true) - $start) * 1000);

        if ($raw === null) {
            $this->error("   FAIL – no response ({$ms} ms). Check PROXY_URL and proxy server status.");
            return self::FAILURE;
        }

        $json = json_decode($raw, true);

        if (($json['status'] ?? '') !== 'success') {
            $this->error("   FAIL ({$ms} ms) – " . ($json['message'] ?? 'unexpected response'));
            $this->line('   Raw: ' . substr($raw, 0, 300));
            return self::FAILURE;
        }

        $count = count($json['data'] ?? []);
        $this->line("   <fg=green>OK</> ({$ms} ms) – {$count} broker/agent record(s) returned");

        if ($count > 0) {
            $this->table(
                ['ID', 'Name', 'Type', 'Email'],
                array_slice(array_map(fn($b) => [
                    $b['broker_id'],
                    $b['name'],
                    $b['cust_type'],
                    $b['email'] ?? '—',
                ], $json['data']), 0, 5)
            );
            if ($count > 5) {
                $this->line("   … and " . ($count - 5) . " more (showing first 5)");
            }
        }

        $this->newLine();

        // ── Test 2: Policies by Broker ────────────────────────────────────────
        $brokerId = $this->option('broker')
            ?? (($json['data'][0]['broker_id'] ?? null));

        if (!$brokerId) {
            $this->warn('   Skipping policy test — no broker_id available.');
            return self::SUCCESS;
        }

        $this->line("<options=bold>2. GET /api/elite/broker/policies?broker_id={$brokerId}</>");

        $start = microtime(true);
        $raw   = $proxy->call('GET', $url . '/api/elite/broker/policies?broker_id=' . $brokerId);
        $ms    = round((microtime(true) - $start) * 1000);

        if ($raw === null) {
            $this->error("   FAIL – no response ({$ms} ms).");
            return self::FAILURE;
        }

        $json = json_decode($raw, true);

        if (($json['status'] ?? '') !== 'success') {
            $this->error("   FAIL ({$ms} ms) – " . ($json['message'] ?? 'unexpected response'));
            $this->line('   Raw: ' . substr($raw, 0, 300));
            return self::FAILURE;
        }

        $pcount = count($json['data'] ?? []);
        $this->line("   <fg=green>OK</> ({$ms} ms) – {$pcount} polic(ies) for broker #{$brokerId}");

        if ($pcount > 0) {
            $this->table(
                ['Policy No', 'Insured', 'Product', 'From', 'To'],
                array_slice(array_map(fn($p) => [
                    $p['policy_no'],
                    $p['name'],
                    $p['product_type'],
                    $p['date_from'],
                    $p['date_to'],
                ], $json['data']), 0, 5)
            );
        }

        $this->newLine();
        $this->line('<fg=green;options=bold>All checks passed.</>');
        $this->newLine();

        return self::SUCCESS;
    }
}
