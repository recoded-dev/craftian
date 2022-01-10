<?php

namespace Recoded\Craftian\Http;

use Psr\Http\Message\UriInterface;

class Client extends \GuzzleHttp\Client
{
    public function __construct(array $config = [])
    {
        parent::__construct([
            'headers' => [
                'User-Agent' => 'Craftian', // TODO add version
            ],
        ] + $config);
    }

    /**
     * Create and send an HTTP request and return json.
     *
     * Use an absolute path to override the base path of the client, or a
     * relative path to append to the base path of the client. The URL can
     * contain the query string as well.
     *
     * @param string $method HTTP method.
     * @param \Psr\Http\Message\UriInterface|string $uri URI object or string.
     * @param array $options Request options to apply. See \GuzzleHttp\RequestOptions.
     * @return object|array
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     */
    public function json(string $method, UriInterface|string $uri = '', array $options = []): object|array
    {
        return json_decode(
            json: $this->request(...func_get_args())->getBody(),
            flags: JSON_THROW_ON_ERROR,
        );
    }
}
