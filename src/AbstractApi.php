<?php

namespace NewsApi;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Base API class providing shared HTTP communication and data handling logic.
 *
 * @author Rodrigo
 */
class AbstractApi implements InterfaceApi
{
    private mixed $data = null;

    private ?Client $httpClient = null;

    const URL = 'https://newsapi.org/v2/';
    const TOP_HEADLINE = 'top-headlines';
    const EVERYTHING = 'everything';
    const SOURCES = 'sources';

    /**
     * Override the Guzzle client (useful for testing with a MockHandler).
     */
    public function setClient(Client $client): void
    {
        $this->httpClient = $client;
    }

    /**
     * Perform a GET request using Guzzle and store the decoded response.
     *
     * @throws GuzzleException
     */
    protected function call(string $url): void
    {
        $client = $this->httpClient ?? new Client([
            'timeout' => 30,
            'headers' => [
                'Cache-Control' => 'no-cache',
            ],
        ]);

        $response = $client->get($url);
        $body = (string) $response->getBody();
        $this->data = json_decode($body);
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    /**
     * Set internal data either from a raw JSON string or from an associative array.
     *
     * @param mixed $data
     * @param bool  $decode Whether to JSON-decode the value (true) or merge it directly (false).
     */
    protected function setData(mixed $data, bool $decode = true): void
    {
        if ($decode) {
            $this->data = json_decode($data);
        } else {
            foreach ($data as $key => $value) {
                $this->data[$key] = $value;
            }
        }
    }

    /**
     * Validate that the query array contains a non-empty 'apiKey'.
     *
     * @param array<string, mixed> $query
     * @return bool
     */
    protected function validateQuery(array $query): bool
    {
        $valid = array_key_exists('apiKey', $query) && !empty($query['apiKey']);

        if (!$valid) {
            $this->setData(['error' => ['apikey' => 'missing apikey']], false);
        }

        return $valid;
    }

    /**
     * Validate that the query array contains a supported endpoint 'type'.
     *
     * @param array<string, mixed> $query
     * @return bool
     */
    protected function validateType(array $query): bool
    {
        $allowedTypes = [self::TOP_HEADLINE, self::EVERYTHING, self::SOURCES];
        $valid = isset($query['type']) && in_array($query['type'], $allowedTypes, true);

        if (!$valid) {
            $this->setData(['error' => ['type' => 'type is not correct']], false);
        }

        return $valid;
    }
}

