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
     * Execute an HTTP call via the proxy (GET or POST only).
     *
     * On Windows (local dev): uses Laravel's Http client.
     * On Linux (production): uses the system curl binary to bypass PHP's stale CA bundle.
     */
    public function call(string $method, string $url): ?string
    {
        return $this->request($method, $url, null);
    }

    public function postJson(string $url, array $data): ?string
    {
        return $this->request('POST', $url, $data);
    }

    private function request(string $method, string $url, ?array $json): ?string
    {
        $method = strtoupper($method);
        if (!in_array($method, ['GET', 'POST'], true)) {
            throw new \InvalidArgumentException("ProxyClient only supports GET and POST, got {$method}.");
        }

        if (PHP_OS_FAMILY === 'Windows') {
            return $this->viaHttpClient($method, $url, $json);
        }

        return $this->viaCurlBinary($method, $url, $json);
    }

    private function viaHttpClient(string $method, string $url, ?array $json): ?string
    {
        try {
            $pending = Http::withHeaders([
                'X-Proxy-Secret' => $this->secret,
                'Accept'         => 'application/json',
            ])->timeout(30);

            $response = $method === 'POST'
                ? $pending->post($url, $json ?? [])
                : $pending->get($url);

            $body = $response->body();
            return ($body !== '') ? $body : null;
        } catch (\Exception $e) {
            Log::error('ProxyClient Http error', ['url' => $url, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Run the curl binary without a shell. The URL, the secret header and the body are passed
     * as a curl config on STDIN, so the proxy secret never appears in the process list (ps).
     * The HTTP status is read back via --write-out and logged; the body is returned for any
     * status (callers parse the proxy's JSON error messages), or null when there was no response.
     */
    private function viaCurlBinary(string $method, string $url, ?array $json): ?string
    {
        $config = [
            'silent',
            'max-time = 30',
            'request = ' . $this->q($method),
            'url = ' . $this->q($url),
            'header = ' . $this->q('X-Proxy-Secret: ' . $this->secret),
            'header = ' . $this->q('Accept: application/json'),
            'write-out = ' . $this->q("\n%{http_code}"),
        ];

        if ($json !== null) {
            $config[] = 'header = ' . $this->q('Content-Type: application/json');
            $config[] = 'data = ' . $this->q(json_encode($json));
        }

        $process = proc_open(
            ['curl', '--config', '-'],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes
        );

        if (!is_resource($process)) {
            Log::error('ProxyClient: could not start curl', ['url' => $url]);
            return null;
        }

        fwrite($pipes[0], implode("\n", $config) . "\n");
        fclose($pipes[0]);

        $output = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);

        $split = strrpos((string) $output, "\n");
        $code  = $split === false ? '000' : trim(substr($output, $split + 1));
        $body  = $split === false ? '' : substr($output, 0, $split);

        if ($code === '000') {
            Log::error('ProxyClient curl: no response', ['url' => $url, 'error' => trim($stderr)]);
            return null;
        }

        if ((int) $code >= 400) {
            Log::warning('ProxyClient curl: HTTP error status', ['url' => $url, 'status' => $code]);
        }

        return $body !== '' ? $body : null;
    }

    /** Quote a value for a curl config file (backslash, quote and control characters escaped). */
    private function q(string $value): string
    {
        return '"' . strtr($value, [
            '\\' => '\\\\',
            '"'  => '\\"',
            "\n" => '\\n',
            "\r" => '\\r',
            "\t" => '\\t',
        ]) . '"';
    }
}
