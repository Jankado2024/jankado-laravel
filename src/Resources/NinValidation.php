<?php

namespace Jankado\Sdk\Resources;

use Jankado\Sdk\Client;

class NinValidation
{
    public function __construct(private readonly Client $client)
    {
    }

    public function types(): array
    {
        return $this->client->request('GET', 'nin-validation/types');
    }

    public function submit(array $data, ?string $idempotencyKey = null): array
    {
        $idempotencyKey ??= self::generateIdempotencyKey();

        return $this->client->request('POST', 'nin-validation/requests', [
            'headers' => ['Idempotency-Key' => $idempotencyKey],
            'json' => $data,
        ]);
    }

    public function all(int $page = 1, int $perPage = 15): array
    {
        return $this->client->request('GET', 'nin-validation/requests', [
            'query' => [
                'page' => max(1, $page),
                'per_page' => min(max(1, $perPage), 50),
            ],
        ]);
    }

    public function find(string $reference): array
    {
        return $this->client->request(
            'GET',
            'nin-validation/requests/'.rawurlencode($reference),
        );
    }

    public static function generateIdempotencyKey(): string
    {
        return 'sdk-'.bin2hex(random_bytes(16));
    }
}
