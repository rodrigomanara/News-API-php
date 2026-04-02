# News API SDK for PHP

[![PHP Composer](https://github.com/rodrigomanara/News-API-php/actions/workflows/php.yml/badge.svg)](https://github.com/rodrigomanara/News-API-php/actions/workflows/php.yml)
[![Latest Stable Version](https://poser.pugx.org/rmanara/news-api-php/v/stable)](https://packagist.org/packages/rmanara/news-api-php)
[![License](https://poser.pugx.org/rmanara/news-api-php/license)](https://packagist.org/packages/rmanara/news-api-php)

A lightweight PHP SDK for the [NewsAPI v2](https://newsapi.org/docs/) service.  
Search through millions of articles from over 30,000 news sources and blogs — including breaking news and niche publications.

---

## Requirements

| Requirement | Version |
|---|---|
| PHP | `>= 7.4` |
| ext-curl | any |
| ext-json | any |

---

## Installation

```bash
composer require rmanara/news-api-php:^1.0
```

---

## Quick start

```php
require_once __DIR__ . '/vendor/autoload.php';

$api  = new \NewsApi\Api('YOUR_API_KEY', ['q' => 'PHP', 'language' => 'en']);
$data = $api->getData();

// $data is a stdClass decoded from the JSON response.
echo $data->totalResults;
foreach ($data->articles as $article) {
    echo $article->title . PHP_EOL;
}
```

> **Security note:** the API key is transmitted via the `X-Api-Key` request header
> and is never appended to the URL.  This keeps it out of server access logs,
> browser history, and HTTP Referer headers.

---

## Constructor

```php
new \NewsApi\Api(
    string $apiKey,                          // Required. Key from newsapi.org.
    array  $query  = [],                     // Endpoint query parameters.
    string $type   = enumType::TOP_HEADLINE  // Endpoint type constant.
)
```

Validation runs before any network request is made, in this order:

1. `$apiKey` must be non-empty.
2. `$type` must be one of the `enumType` constants.
3. `$query` must contain at least one parameter.

If any check fails, `getData()` returns a local error array and no HTTP call is made.

---

## Endpoints

Use the `\NewsApi\enumType` constants to select an endpoint:

| Constant | Endpoint | Required query params |
|---|---|---|
| `enumType::TOP_HEADLINE` *(default)* | `top-headlines` | one of: `sources`, `q`, `language`, `country` |
| `enumType::EVERYTHING` | `everything` | one of: `q`, `sources`, `domains` |
| `enumType::SOURCES` | `sources` | none — all params optional |

---

## Examples

### Top headlines

```php
$api  = new \NewsApi\Api('YOUR_API_KEY', ['country' => 'gb']);
$data = $api->getData();
```

### Search everything

```php
use NewsApi\Api;
use NewsApi\enumType;

$api  = new Api('YOUR_API_KEY', ['q' => 'climate change', 'language' => 'en'], enumType::EVERYTHING);
$data = $api->getData();
```

### Discover sources

```php
use NewsApi\Api;
use NewsApi\enumType;

$api     = new Api('YOUR_API_KEY', ['language' => 'en', 'country' => 'us'], enumType::SOURCES);
$sources = $api->getData();
```

---

## Error handling

### Local validation errors

When a validation guard fails before any request is made, `getData()` returns an
associative array:

```php
// Missing or empty API key
$api  = new \NewsApi\Api('', ['q' => 'test']);
$data = $api->getData();
// ['error' => ['apikey' => 'missing apikey']]

// Unsupported endpoint type
$api  = new \NewsApi\Api('YOUR_API_KEY', ['q' => 'test'], 'bad-type');
$data = $api->getData();
// ['error' => ['type' => 'type is not correct']]

// Empty query array
$api  = new \NewsApi\Api('YOUR_API_KEY', []);
$data = $api->getData();
// ['error' => ['query' => 'empty query']]
```

### Transport errors

A `\RuntimeException` is thrown when cURL fails (e.g. DNS resolution error,
connection timeout):

```php
try {
    $api  = new \NewsApi\Api('YOUR_API_KEY', ['q' => 'PHP']);
    $data = $api->getData();
} catch (\RuntimeException $e) {
    // Handle transport failure
    echo $e->getMessage();
}
```

---

## Running the tests

```bash
composer test
```

---

## License

MIT © [Rodrigo Manara](https://github.com/rodrigomanara)
