<?php

declare(strict_types=1);

namespace NewsApi;

/**
 * Contract for all NewsAPI client classes.
 *
 * Implementations must expose a way to retrieve the most recent response from
 * the API, whether that is a decoded upstream payload or a locally generated
 * validation-error structure.
 *
 * @package NewsApi
 */
interface InterfaceApi
{
    /**
     * Returns the decoded response payload or the last validation-error structure.
     *
     * Will be null if the client was constructed but no request has been attempted
     * (e.g. validation failed before a network call could be made and no local error
     * payload was stored).
     *
     * @return object|array<string, mixed>|null
     */
    public function getData();
}
