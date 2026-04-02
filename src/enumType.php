<?php

declare(strict_types=1);

namespace NewsApi;

/**
 * Enumeration of the NewsAPI v2 endpoint path segments.
 *
 * These constants are used both as URL path segments and as valid values for
 * the {@code $type} parameter of {@see Api::__construct()}.
 *
 * This class is a pure constants container and cannot be instantiated.
 *
 * @package NewsApi
 */
final class enumType
{
    /**
     * Endpoint for breaking and top-headline queries.
     *
     * Requires at least one of: {@code sources}, {@code q}, {@code language}, or {@code country}.
     */
    const TOP_HEADLINE = 'top-headlines';

    /**
     * Endpoint for searching across all articles published by all sources.
     *
     * Requires at least one of: {@code q}, {@code sources}, or {@code domains}.
     */
    const EVERYTHING = 'everything';

    /**
     * Endpoint for discovering and filtering available news sources.
     */
    const SOURCES = 'sources';

    /**
     * Prevents instantiation — this class is a constants container only.
     */
    private function __construct()
    {
    }
}