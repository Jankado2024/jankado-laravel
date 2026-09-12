<?php

namespace Jankado\Sdk;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Jankado\Sdk\Exceptions\ApiException;
use Jankado\Sdk\Resources\CacRegistration;
use Jankado\Sdk\Resources\NinValidation;
use Jankado\Sdk\Resources\Verification;

class Client
{
    private ClientInterface $http;

    public function __construct(
        private readonly string $token,
        string $baseUrl = 'https://jankado.com.ng',
        ?ClientInterface $http = null,
    ) {
        if (trim($this->token) === '') {
            throw new \InvalidArgumentException('A Jankado API token is required.');
        }

        $baseUrl = rtrim($baseUrl, '/');
        if (! str_ends_with($baseUrl, '/api/v1')) {
            $baseUrl .= '/api/v1';
        }

        $this->http = $http ?? new HttpClient([
            'base_uri' => $baseUrl.'/',
            'timeout' => 30,
            'connect_timeout' => 10,
            'http_errors' => false,
        ]);
    }

    public function ninValidation(): NinValidation
    {
        return new NinValidation($this);
    }

    public function verification(): Verification
    {
        return new Verification($this);
    }

    public function cacRegistration(): CacRegistration
    {
        return new CacRegistration($this);
    }

    public function request(string $method, string $uri, array $options = []): array
    {
        $options['headers'] = array_merge([
            'Authorization' => 'Bearer '.$this->token,
            'Accept' => 'application/json',
            'User-Agent' => 'Jankado-Laravel-SDK/1.1',
        ], $options['headers'] ?? []);

        try {
            $response = $this->http->request($method, ltrim($uri, '/'), $options);
        } catch (GuzzleException $exception) {
            throw new ApiException(
                'Unable to communicate with the Jankado API: '.$exception->getMessage(),
                0,
                [],
                $exception,
            );
        }

        $statusCode = $response->getStatusCode();
        $body = (string) $response->getBody();
        $payload = $body === '' ? [] : json_decode($body, true);

        if (! is_array($payload)) {
            throw new ApiException('The Jankado API returned invalid JSON.', $statusCode, ['raw' => $body]);
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            $message = $payload['message'] ?? $payload['error'] ?? 'Jankado API request failed.';
            throw new ApiException((string) $message, $statusCode, $payload);
        }

        return $payload;
    }
}
