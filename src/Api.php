<?php

declare(strict_types=1);

namespace NewsApi;

/**
 * High-level NewsAPI client that validates all input and fires the request on construction.
 *
 * Typical usage:
 * <code>
 *   $api  = new Api('your-api-key', ['q' => 'PHP', 'language' => 'en']);
 *   $data = $api->getData();
 * </code>
 *
 * @package NewsApi
 */
class Api extends AbstractApi
{
    /**
     * Validates the provided credentials and query, then immediately executes the request.
     *
     * Validation is performed in order:
     *   1. The API key must be non-empty.
     *   2. The endpoint type must be one of the values defined in {@see enumType}.
     *   3. The query array must contain at least one search parameter.
     *
     * If any guard fails, {@see getData()} will return a local error payload and
     * no HTTP request is made.
     *
     * The API key is transmitted via the {@code X-Api-Key} request header and is
     * intentionally kept out of the URL to avoid credential exposure in logs.
     *
     * @param string               $apiKey Developer API key issued by newsapi.org.
     * @param array<string, mixed> $query  Endpoint-specific query parameters
     *                                     (e.g. {@code ['q' => 'PHP', 'language' => 'en']}).
     * @param string               $type   Endpoint type; one of the {@see enumType} constants.
     *                                     Defaults to {@see enumType::TOP_HEADLINE}.
     *
     * @throws \RuntimeException When the underlying cURL transport fails.
     */
    public function __construct(
        string $apiKey,
        array $query = [],
        string $type = enumType::TOP_HEADLINE
    ) {
        if (!$this->validateApiKey($apiKey)) {
            return;
        }

        if (!$this->validateType($type)) {
            return;
        }

        if (!$this->validateQuery($query)) {
            return;
        }

        $uri = http_build_query($query);
        $url = self::URL . $type . ($uri !== '' ? '?' . $uri : '');

        $this->call($url, $apiKey);
    }
}
