<?php

declare(strict_types=1);

namespace NewsApi;

/**
 * Base class providing the HTTP transport, response storage, and input validation
 * that all NewsAPI client implementations share.
 *
 * Sub-classes should call {@see call()} after all validation guards have passed.
 * This class must not be instantiated directly; extend it and supply an API key
 * through the child constructor.
 *
 * @package NewsApi
 */
abstract class AbstractApi implements InterfaceApi
{
    /**
     * Decoded API response, a local validation-error structure, or null when no
     * request has been attempted yet.
     *
     * @var object|array<string, mixed>|null
     */
    private $data = null;

    /**
     * Root URL shared by every NewsAPI v2 endpoint.
     *
     * The concrete endpoint segment and query string are appended at call time.
     */
    protected const URL = 'https://newsapi.org/v2/';

    // -------------------------------------------------------------------------
    // Transport
    // -------------------------------------------------------------------------

    /**
     * Executes a GET request, authenticates it via the {@code X-Api-Key} request
     * header, and stores the decoded JSON payload.
     *
     * Sending the key in a header rather than a query-string parameter prevents it
     * from appearing in server access logs, browser history, and HTTP Referer
     * headers — all common sources of unintentional credential exposure.
     *
     * @param string $url    Fully-qualified NewsAPI endpoint URL (without the apiKey
     *                       query parameter).
     * @param string $apiKey Developer API key forwarded in the X-Api-Key header.
     *
     * @return void
     *
     * @throws \RuntimeException When a cURL handle cannot be initialised.
     * @throws \RuntimeException When cURL reports a transport-level error.
     */
    protected function call(string $url, string $apiKey = ''): void
    {
        $curl = curl_init();

        if ($curl === false) {
            throw new \RuntimeException('Failed to initialise a cURL handle.');
        }

        curl_setopt_array($curl, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => 'UTF-8',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            // Explicitly enforce SSL certificate verification to prevent MITM attacks.
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER     => [
                'Cache-Control: no-cache',
                // Header-based auth keeps the key out of URLs, logs, and history.
                'X-Api-Key: ' . $apiKey,
            ],
        ]);

        $response = curl_exec($curl);
        $err      = curl_error($curl);
        curl_close($curl);

        // Check for a transport error before attempting to store the response so
        // that a failed request does not silently overwrite valid stored data.
        if ($err !== '') {
            throw new \RuntimeException('cURL transport error: ' . $err);
        }

        $this->setData((string) $response);
    }

    // -------------------------------------------------------------------------
    // Data accessors
    // -------------------------------------------------------------------------

    /**
     * Returns the decoded response payload, a local validation-error array, or
     * null when no request has been attempted yet.
     *
     * @return object|array<string, mixed>|null
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Stores API response data on the instance.
     *
     * When {@code $decode} is true (the default) the value is treated as a raw JSON
     * string and decoded before storage.  Pass false together with an associative
     * array to store a pre-built error payload directly without a JSON round-trip.
     *
     * @param string|array<string, mixed> $data   JSON response body or a pre-built
     *                                            error payload array.
     * @param bool                        $decode When true, JSON-decode {@code $data}
     *                                            before storing it.
     *
     * @return void
     */
    protected function setData($data, bool $decode = true): void
    {
        if ($decode) {
            $this->data = json_decode((string) $data);
        } else {
            foreach ((array) $data as $key => $value) {
                $this->data[$key] = $value;
            }
        }
    }

    // -------------------------------------------------------------------------
    // Validation helpers
    // -------------------------------------------------------------------------

    /**
     * Guards against a missing or blank API key.
     *
     * When the key is absent the method stores a local error payload and returns
     * false so the caller can short-circuit before making any network request.
     *
     * @param string $apiKey The developer API key to validate.
     *
     * @return bool True when the key is non-empty; false otherwise.
     */
    protected function validateApiKey(string $apiKey): bool
    {
        if (trim($apiKey) === '') {
            $obj = ['error' => ['apikey' => 'missing apikey']];
            $this->setData($obj, false);
            return false;
        }
        return true;
    }

    /**
     * Guards against an empty query-parameter array.
     *
     * Every NewsAPI endpoint requires at least one search criterion in addition to
     * the API key, so an empty {@code $query} is rejected before any HTTP request
     * is issued.
     *
     * @param array<string, mixed> $query Request parameters (must contain at least
     *                                    one entry).
     *
     * @return bool True when the array is non-empty; false otherwise.
     */
    protected function validateQuery(array $query): bool
    {
        if (empty($query)) {
            $obj = ['error' => ['query' => 'empty query']];
            $this->setData($obj, false);
            return false;
        }
        return true;
    }

    /**
     * Guards against an unsupported endpoint type.
     *
     * Accepted values are the string constants defined on {@see enumType}.
     *
     * @param string $type Endpoint segment to validate (e.g. {@code "top-headlines"}).
     *
     * @return bool True when {@code $type} matches a recognised NewsAPI endpoint;
     *              false otherwise.
     */
    protected function validateType(string $type): bool
    {
        $allowed = [
            enumType::TOP_HEADLINE,
            enumType::EVERYTHING,
            enumType::SOURCES,
        ];

        if (!in_array($type, $allowed, true)) {
            $obj = ['error' => ['type' => 'type is not correct']];
            $this->setData($obj, false);
            return false;
        }
        return true;
    }
}
