<?php

declare(strict_types=1);

/*
 * Basic usage example for the NewsApi\Api client.
 *
 * Replace YOUR_API_KEY with a valid key from https://newsapi.org.
 * This script builds a top-headlines request, fetches the response,
 * and dumps the decoded payload for quick manual inspection.
 */

require_once __DIR__ . '/vendor/autoload.php';

// The API key is the first argument; query parameters are the second.
// The key is sent via the X-Api-Key header and never appears in the URL.
$api  = new \NewsApi\Api('YOUR_API_KEY', ['q' => 'Reino Unido', 'language' => 'pt']);
$data = $api->getData();
