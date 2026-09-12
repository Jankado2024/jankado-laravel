# Jankado Laravel SDK

Official Laravel SDK for securely accessing the Jankado API from a server-side Laravel application.

Supported services:

- NIN validation requests
- NIN verification by NIN or phone number
- BVN verification
- NIN facial verification
- CAC business name and company registration

## Requirements

- PHP 8.1 or later
- Laravel 10, 11, or 12
- A Jankado API bearer token
- A funded Jankado wallet for paid requests

## Installation

```bash
composer require jankado-official/jankado-laravel
```

Add the token to `.env`:

```dotenv
JANKADO_API_TOKEN=your-token
```

The package is discovered automatically by Laravel. You can use the `Jankado` facade in the examples below.

## NIN Validation

```php
use Jankado\Sdk\Facades\Jankado;

$types = Jankado::ninValidation()->types();

$request = Jankado::ninValidation()->submit([
    'validation_type' => 'no-record-validation',
    'nin_number' => '12345678901',
    'is_consent' => true,
]);

$reference = $request['data']['reference'];
$status = Jankado::ninValidation()->find($reference);
$history = Jankado::ninValidation()->all(page: 1, perPage: 15);
```

For safe retries, store and reuse your own idempotency key:

```php
$request = Jankado::ninValidation()->submit(
    data: [
        'validation_type' => 'no-record-validation',
        'nin_number' => '12345678901',
        'is_consent' => true,
    ],
    idempotencyKey: 'your-unique-request-key',
);
```

## NIN Verification

Verify directly with an 11-digit NIN:

```php
$result = Jankado::verification()->nin(
    identifier: '12345678901',
    consent: true,
);
```

Verify through the phone-number method:

```php
$result = Jankado::verification()->nin(
    identifier: '08012345678',
    consent: true,
    method: 'nin-phone',
);
```

## BVN Verification

```php
$result = Jankado::verification()->bvn(
    bvn: '12345678901',
    consent: true,
);
```

## NIN Facial Verification

Convert a local image into the data URI required by the API, then submit it:

```php
use Jankado\Sdk\Resources\Verification;

$selfie = Verification::selfieFromFile(storage_path('app/selfies/customer.jpg'));

$result = Jankado::verification()->ninFacial(
    nin: '12345678901',
    selfie: $selfie,
    consent: true,
);
```

The API token must include the `nin-facial:verify` ability.

## CAC Registration

Retrieve the currently available registration types and prices:

```php
$types = Jankado::cacRegistration()->types();
```

Submit a business name registration. Document values must be readable local file paths:

```php
$request = Jankado::cacRegistration()->submitBusinessName([
    'is_consent' => true,
    'name1' => 'Jankado Example Ventures',
    'name2' => 'Jankado Example Services',
    'business_nature' => 'Technology services',
    'business_email' => 'owner@example.com',
    'business_phone' => '08012345678',
    'business_address' => 'Bauchi, Nigeria',
    'business_lga' => 'Bauchi',
    'business_state' => 'Bauchi',
    'first_name' => 'Example',
    'surname' => 'Owner',
    'phone' => '08012345678',
    'dob' => '1990-01-01',
    'nin' => '12345678901',
    'home_address' => 'Bauchi, Nigeria',
    'lga_origin' => 'Bauchi',
    'lga_residence' => 'Bauchi',
    'marital_status' => 'Single',
    'signature' => storage_path('app/cac/signature.jpg'),
    'passport' => storage_path('app/cac/passport.jpg'),
    'id_document' => storage_path('app/cac/nin-slip.pdf'),
], 'your-unique-request-key');
```

Company registration accepts the company, directors, witness, and document fields required by the API:

```php
$request = Jankado::cacRegistration()->submitCompany(
    $companyData,
    'your-unique-request-key',
);
```

Check CAC registration history or a specific request:

```php
$history = Jankado::cacRegistration()->all(['page' => 1]);
$status = Jankado::cacRegistration()->find('JNKCAC-REFERENCE');
```

If no idempotency key is supplied for a CAC submission, the SDK generates one automatically. For retries across separate processes, supply and reuse your own key.

## Error Handling

```php
use Jankado\Sdk\Exceptions\ApiException;

try {
    $result = Jankado::verification()->bvn('12345678901', true);
} catch (ApiException $exception) {
    $statusCode = $exception->statusCode();
    $response = $exception->response();
    $message = $exception->getMessage();
}
```

Never expose the API token in frontend JavaScript or a mobile application. Call Jankado only from a secure backend.
