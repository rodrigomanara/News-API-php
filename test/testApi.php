<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use NewsApi\Api;
use PHPUnit\Framework\TestCase;

final class testApi extends TestCase
{
    // -------------------------------------------------------------------------
    // Validation tests (no HTTP call needed)
    // -------------------------------------------------------------------------

    public function testMissingApiKeyReturnsError(): void
    {
        $api  = new Api();
        $data = $api->getData();

        $this->assertIsArray($data);
        $this->assertEquals('missing apikey', $data['error']['apikey']);
    }

    public function testEmptyApiKeyReturnsError(): void
    {
        $api  = new Api(['apiKey' => '']);
        $data = $api->getData();

        $this->assertIsArray($data);
        $this->assertEquals('missing apikey', $data['error']['apikey']);
    }

    public function testInvalidTypeReturnsError(): void
    {
        $api  = new Api(['apiKey' => 'test-key'], 'invalid-type');
        $data = $api->getData();

        $this->assertIsArray($data);
        $this->assertEquals('type is not correct', $data['error']['type']);
    }

    // -------------------------------------------------------------------------
    // HTTP response tests (mocked Guzzle client)
    // -------------------------------------------------------------------------

    private function buildMockClient(string $jsonBody, int $status = 200): Client
    {
        $mock  = new MockHandler([new Response($status, [], $jsonBody)]);
        return new Client(['handler' => HandlerStack::create($mock)]);
    }

    private function buildTrackedMockClient(string $jsonBody, array &$history): Client
    {
        $mock  = new MockHandler([new Response(200, [], $jsonBody)]);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($history));
        return new Client(['handler' => $stack]);
    }

    public function testSuccessfulResponseReturnsOkStatus(): void
    {
        $body   = json_encode(['status' => 'ok', 'totalResults' => 5, 'articles' => []]);
        $client = $this->buildMockClient($body);

        $api  = new Api(['apiKey' => 'test-key', 'language' => 'pt'], Api::TOP_HEADLINE, $client);
        $data = $api->getData();

        $this->assertEquals('ok', $data->status);
        $this->assertEquals(5, $data->totalResults);
    }

    public function testApiErrorResponseReturnsErrorStatus(): void
    {
        $body   = json_encode(['status' => 'error', 'code' => 'apiKeyInvalid', 'message' => 'Your API key is invalid.']);
        $client = $this->buildMockClient($body);

        $api  = new Api(['apiKey' => 'bad-key'], Api::TOP_HEADLINE, $client);
        $data = $api->getData();

        $this->assertEquals('error', $data->status);
        $this->assertEquals('apiKeyInvalid', $data->code);
    }

    public function testTopHeadlinesEndpointIsUsed(): void
    {
        $history = [];
        $client  = $this->buildTrackedMockClient(json_encode(['status' => 'ok']), $history);

        new Api(['apiKey' => 'test-key'], Api::TOP_HEADLINE, $client);

        $this->assertCount(1, $history);
        $this->assertStringContainsString('top-headlines', (string) $history[0]['request']->getUri());
    }

    public function testEverythingEndpointIsUsed(): void
    {
        $history = [];
        $client  = $this->buildTrackedMockClient(json_encode(['status' => 'ok']), $history);

        new Api(['apiKey' => 'test-key'], Api::EVERYTHING, $client);

        $this->assertCount(1, $history);
        $this->assertStringContainsString('everything', (string) $history[0]['request']->getUri());
    }

    public function testSourcesEndpointIsUsed(): void
    {
        $history = [];
        $client  = $this->buildTrackedMockClient(json_encode(['status' => 'ok']), $history);

        new Api(['apiKey' => 'test-key'], Api::SOURCES, $client);

        $this->assertCount(1, $history);
        $this->assertStringContainsString('sources', (string) $history[0]['request']->getUri());
    }

    public function testApiKeyIsPassedAsQueryParameter(): void
    {
        $history = [];
        $client  = $this->buildTrackedMockClient(json_encode(['status' => 'ok']), $history);

        new Api(['apiKey' => 'my-secret-key'], Api::TOP_HEADLINE, $client);

        $uri = (string) $history[0]['request']->getUri();
        $this->assertStringContainsString('apiKey=my-secret-key', $uri);
    }
}


