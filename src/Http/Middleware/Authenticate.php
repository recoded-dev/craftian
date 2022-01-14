<?php

namespace Recoded\Craftian\Http\Middleware;

use Psr\Http\Message\RequestInterface;

class Authenticate
{
    public function __invoke(callable $next): \Closure
    {
        return function (RequestInterface $request, array $options) use ($next) {
            $host = $request->getUri()->getHost();
            $authentications = $options['craftian']['authentication'] ?? [];

            if (isset($authentications[$host])) {
                $request = $request->withHeader('Authentication', 'Basic ' . $authentications[$host]);
            }

            return $next($request, $options);
        };
    }
}
