# Jankado Laravel SDK

Official Laravel SDK for the Jankado API. The first supported resource is NIN Validation.

## Requirements

- PHP 8.1 or later
- Laravel 10, 11, or 12
- A Jankado API Bearer token

## Installation

```bash
composer require jankado-official/jankado-laravel
```

Add the token to `.env`:

```dotenv
JANKADO_API_TOKEN=your-token
```

## NIN Validation

```php
use Jankado\Sdk\Facades\Jankado;

$types = Jankado::ninValidation()->types();

$request = Jankado::ninValidation()->submit([
    'validation_type' => 'no-record-validation',
    'nin_number' => '12345678901',
    'is_consent' => true,
]);

$status = Jankado::ninValidation()->find($request['data']['reference']);
$history = Jankado::ninValidation()->all(page: 1, perPage: 15);
```

For safe retries, store and reuse your own idempotency key:

```php
$request = Jankado::ninValidation()->submit($data, 'order-10045-validation');
```

Reusing the same key for the same customer returns the original request and does not charge the wallet twice.

### Modification validation

```php
$request = Jankado::ninValidation()->submit([
    'validation_type' => 'modification-validation',
    'nin_number' => '12345678901',
    'is_consent' => true,
    'first_name' => 'Old',
    'middle_name' => null,
    'surname' => 'Name',
    'phone_number' => '08000000000',
    'former_dob' => '1990-01-01',
    'former_gender' => 'male',
    'new_first_name' => 'New',
    'new_middle_name' => null,
    'new_surname' => 'Name',
    'new_phone_number' => '08000000000',
    'new_dob' => '1990-01-01',
    'new_gender' => 'male',
], 'customer-order-10046');
```

## Error handling

```php
use Jankado\Sdk\Exceptions\ApiException;

try {
    $result = Jankado::ninValidation()->find($reference);
} catch (ApiException $exception) {
    $statusCode = $exception->statusCode();
    $response = $exception->response();
}
```

Never expose the API token in frontend JavaScript or a mobile application. Call Jankado from a secure backend.
