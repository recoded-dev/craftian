<?php

namespace Recoded\Craftian\Http;

use GuzzleHttp\HandlerStack;
use Psr\Http\Message\UriInterface;
use Recoded\Craftian\Craftian;
use Recoded\Craftian\Http\Middleware\Authenticate;

class Client extends \GuzzleHttp\Client
{
    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct([
            'handler' => $this->handler(),
            'headers' => [
                'User-Agent' => 'Craftian', // TODO add version
            ],
            'craftian' => Craftian::httpConfig(),
        ] + $config);
    }

    protected function handler(): callable
    {
        $handler = HandlerStack::create();

        $handler->push(new Authenticate(), 'authenticate');

        return $handler;
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
     * @param array<string, mixed> $options Request options to apply. See \GuzzleHttp\RequestOptions.
     * @return object|array<mixed>
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     */
    public function json(string $method, UriInterface|string $uri = '', array $options = []): object|array
    {
        $decoded = json_decode(
            json: $this->request(...func_get_args())->getBody(),
            flags: JSON_THROW_ON_ERROR,
        );

        if (!is_array($decoded) && !is_object($decoded)) {
            return [];
        }

        return $decoded;
    }
}
