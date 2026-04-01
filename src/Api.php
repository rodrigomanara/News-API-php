<?php

namespace NewsApi;

/**
 * Entry-point for making News API requests.
 *
 * @author Rodrigo
 */
class Api extends AbstractApi
{
    /**
     * @param array<string, mixed> $query Query parameters including 'apiKey'.
     * @param string               $type  One of TOP_HEADLINE, EVERYTHING, or SOURCES.
     */
    public function __construct(array $query = [], string $type = self::TOP_HEADLINE)
    {
        if ($this->validateQuery($query) && $this->validateType(['type' => $type])) {
            $uri = http_build_query($query);
            $url = self::URL . $type . '?' . $uri;
            $this->call($url);
        }
    }
}
