<?php

declare(strict_types=1);

use NewsApi\Api;

/**
 * Verifies the client validation flow and a successful request path.
 */
final class testApi extends PHPUnit\Framework\TestCase
{
    /**
     * Confirms that constructing the client without an API key stores a local
     * validation error without making any network request.
     *
     * @return void
     */
    public function testwithoutKeyApiCall(): void
    {
        $new  = new Api('', ['q' => 'test']);
        $data = $new->getData();

        $this->assertEquals('missing apikey', $data['error']['apikey']);
    }

    /**
     * Confirms that passing an invalid endpoint type stores a local validation error
     * without making any network request.
     *
     * @return void
     */
    public function testWithInvalidTypeReturnsError(): void
    {
        $new  = new Api('valid-key', ['q' => 'test'], 'invalid-endpoint');
        $data = $new->getData();

        $this->assertEquals('type is not correct', $data['error']['type']);
    }

    /**
     * Confirms that a successful request can be consumed as a decoded response object.
     *
     * The HTTP call is replaced with a mock that injects a known JSON payload so that
     * the test does not depend on network availability or a real API key.
     *
     * @return void
     */
    public function testwithKeywithrequiredParametersApiCall(): void
    {
        $new = new class ('test-api-key', ['language' => 'pt']) extends Api {
            /**
             * Overrides the real HTTP transport with a stub that returns a static payload.
             *
             * @param string $url    The request URL that would have been called.
             * @param string $apiKey The API key that would have been forwarded.
             *
             * @return void
             */
            protected function call(string $url, string $apiKey = ''): void
            {
                $this->setData(json_encode(['status' => 'ok']));
            }
        };

        $data = $new->getData();

        $this->assertEquals('ok', $data->status);
    }
}
