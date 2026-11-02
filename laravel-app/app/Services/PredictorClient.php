<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

class PredictorClient
{
    protected string $baseUrl;
    protected string $token;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(env('PREDICTOR_URL', 'http://127.0.0.1:8001'), '/');
        $this->token = env('PREDICTOR_TOKEN', 'rahasia-stub-token-123');
        $this->timeout = (int) env('PREDICTOR_TIMEOUT', 10);
    }

    /**
     * Cek status dan mode layanan prediksi
     *
     * @return array
     * @throws ConnectionException|RequestException|\Exception
     */
    public function health(): array
    {
        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'X-Service-Token' => $this->token,
                'Accept' => 'application/json',
            ])
            ->get("{$this->baseUrl}/health");

        if ($response->failed()) {
            $response->throw();
        }

        return $response->json();
    }

    /**
     * Kirim baris fitur untuk diprediksi
     *
     * @param array $rows Array of feature rows
     * @return array
     * @throws ConnectionException|RequestException|\Exception
     */
    public function predict(array $rows): array
    {
        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'X-Service-Token' => $this->token,
                'Accept' => 'application/json',
            ])
            ->post("{$this->baseUrl}/predict", [
                'rows' => $rows,
            ]);

        if ($response->failed()) {
            $response->throw();
        }

        return $response->json();
    }
}

