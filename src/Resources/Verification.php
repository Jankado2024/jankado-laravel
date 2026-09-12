<?php

namespace Jankado\Sdk\Resources;

use Jankado\Sdk\Client;

class Verification
{
    public function __construct(private readonly Client $client)
    {
    }

    public function nin(string $identifier, bool $consent, string $method = 'nin'): array
    {
        if (! in_array($method, ['nin', 'nin-phone'], true)) {
            throw new \InvalidArgumentException('NIN method must be "nin" or "nin-phone".');
        }

        return $this->client->request('POST', 'verification/nin-verify', [
            'json' => [
                'verify_method' => $method,
                'identifier' => $identifier,
                'isConsent' => $consent,
            ],
        ]);
    }

    public function bvn(string $bvn, bool $consent): array
    {
        return $this->client->request('POST', 'verification/bvn-verify', [
            'json' => ['bvn' => $bvn, 'isConsent' => $consent],
        ]);
    }

    public function ninFacial(string $nin, string $selfie, bool $consent): array
    {
        return $this->client->request('POST', 'verification/nin-facial', [
            'json' => [
                'nin' => $nin,
                'selfie' => $selfie,
                'isConsent' => $consent,
            ],
        ]);
    }

    public static function selfieFromFile(string $path): string
    {
        if (! is_file($path) || ! is_readable($path)) {
            throw new \InvalidArgumentException('The selfie file is not readable: '.$path);
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path) ?: 'image/jpeg';

        return 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($path));
    }
}
