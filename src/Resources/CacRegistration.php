<?php

namespace Jankado\Sdk\Resources;

use Jankado\Sdk\Client;

class CacRegistration
{
    private const FILE_FIELDS = [
        'signature',
        'passport',
        'id_document',
        'nin_slip',
        'witness_signature',
    ];

    public function __construct(private readonly Client $client)
    {
    }

    public function types(): array
    {
        return $this->client->request('GET', 'cac-registration/types');
    }

    public function submitBusinessName(array $data, ?string $idempotencyKey = null): array
    {
        return $this->submit('business_name', $data, $idempotencyKey);
    }

    public function submitCompany(array $data, ?string $idempotencyKey = null): array
    {
        return $this->submit('company', $data, $idempotencyKey);
    }

    public function submit(string $type, array $data, ?string $idempotencyKey = null): array
    {
        if (! in_array($type, ['business_name', 'company'], true)) {
            throw new \InvalidArgumentException('CAC registration type must be "business_name" or "company".');
        }

        $multipart = [];
        $streams = [];
        $this->appendMultipart($multipart, $streams, array_merge(
            $data,
            ['registration_type' => $type],
        ));

        try {
            return $this->client->request('POST', 'cac-registration/requests', [
                'headers' => [
                    'Idempotency-Key' => $idempotencyKey ?: $this->newIdempotencyKey(),
                ],
                'multipart' => $multipart,
            ]);
        } finally {
            foreach ($streams as $stream) {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
        }
    }

    public function all(array $query = []): array
    {
        return $this->client->request('GET', 'cac-registration/requests', [
            'query' => $query,
        ]);
    }

    public function find(string $reference): array
    {
        return $this->client->request(
            'GET',
            'cac-registration/requests/'.rawurlencode($reference),
        );
    }

    private function appendMultipart(
        array &$multipart,
        array &$streams,
        array $values,
        string $prefix = '',
    ): void {
        foreach ($values as $key => $value) {
            $name = $prefix === '' ? (string) $key : $prefix.'['.$key.']';

            if (is_array($value)) {
                $this->appendMultipart($multipart, $streams, $value, $name);
                continue;
            }

            if (in_array((string) $key, self::FILE_FIELDS, true)) {
                if (! is_string($value) || ! is_file($value) || ! is_readable($value)) {
                    throw new \InvalidArgumentException('CAC document is not readable: '.$name);
                }

                $stream = fopen($value, 'rb');
                if ($stream === false) {
                    throw new \InvalidArgumentException('Unable to open CAC document: '.$name);
                }

                $streams[] = $stream;
                $multipart[] = [
                    'name' => $name,
                    'contents' => $stream,
                    'filename' => basename($value),
                ];
                continue;
            }

            if ($value !== null) {
                $multipart[] = [
                    'name' => $name,
                    'contents' => is_bool($value) ? ($value ? '1' : '0') : (string) $value,
                ];
            }
        }
    }

    private function newIdempotencyKey(): string
    {
        return 'jankado-sdk-'.bin2hex(random_bytes(16));
    }
}
