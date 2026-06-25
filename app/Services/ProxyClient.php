<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProxyClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $secret
    ) {}

    public function isConfigured(): bool
    {
        return !empty($this->baseUrl) && !empty($this->secret);
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Execute an HTTP call via the proxy.
     *
     * On Windows (local dev): uses Laravel's Http client.
     * On Linux (production): uses the system curl binary to bypass PHP's stale CA bundle.
     */
    public function call(string $method, string $url): ?string
    {
        if (PHP_OS_FAMILY === 'Windows') {
            try {
                $response = Http::withHeaders([
                    'X-Proxy-Secret' => $this->secret,
                    'Accept'         => 'application/json',
                ])->timeout(30)->{strtolower($method)}($url);

                $body = $response->body();
                return ($body !== '') ? $body : null;
            } catch (\Exception $e) {
                Log::error('ProxyClient Http error', ['url' => $url, 'error' => $e->getMessage()]);
                return null;
            }
        }

        $escapedUrl    = escapeshellarg($url);
        $escapedSecret = escapeshellarg('X-Proxy-Secret: ' . $this->secret);
        $methodFlag    = strtoupper($method) === 'POST' ? '-X POST' : '-X GET';
        $cmd           = "curl -s --max-time 30 {$methodFlag} {$escapedUrl} -H {$escapedSecret} -H " . escapeshellarg('Accept: application/json');
        $output        = shell_exec($cmd);

        return ($output !== null && $output !== '') ? $output : null;
    }
}
