<?php

namespace Modules\Acl\Routing;

use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Routing\Route;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Arr;
use Modules\Acl\Http\Middleware\DecryptRouteIds;
use Modules\Acl\Services\RouteIdCipher;

/**
 * URL generator that encrypts the route parameters listed in a route's `route.ids:...`
 * middleware. route('admin.restaurant.edit', 30) therefore yields /restaurant/{token}/edit
 * everywhere (views, grids, redirects) with no change at the call sites.
 */
class EncryptedIdUrlGenerator extends UrlGenerator
{
    private ?RouteIdCipher $cipher = null;

    /** Copies the state of the framework's generator so nothing configured on it is lost. */
    public static function fromBase(UrlGenerator $base, RouteIdCipher $cipher): self
    {
        $generator = (new \ReflectionClass(self::class))->newInstanceWithoutConstructor();
        foreach (get_object_vars($base) as $property => $value) {
            $generator->{$property} = $value;
        }
        $generator->cipher = $cipher;

        return $generator;
    }

    public function toRoute($route, $parameters, $absolute)
    {
        $encrypted = $this->encryptedParameters($route);

        if ($encrypted !== [] && $this->cipher) {
            $parameters = $this->encryptParameters($route, Arr::wrap($parameters), $encrypted);
        }

        return parent::toRoute($route, $parameters, $absolute);
    }

    /** Parameter names listed in the route's route.ids middleware. */
    private function encryptedParameters(Route $route): array
    {
        $names = [];
        foreach ((array) $route->middleware() as $middleware) {
            if (!is_string($middleware)) {
                continue;
            }
            [$name, $args] = array_pad(explode(':', $middleware, 2), 2, '');
            if ($name === 'route.ids' || $name === DecryptRouteIds::class) {
                $names = array_merge($names, array_filter(explode(',', $args)));
            }
        }

        return array_values(array_unique($names));
    }

    private function encryptParameters(Route $route, array $parameters, array $encrypted): array
    {
        // Positional values fill the path placeholders that were not passed by name, in order.
        $positional = array_values(array_filter(array_keys($parameters), 'is_int'));

        foreach ($route->parameterNames() as $name) {
            if (array_key_exists($name, $parameters)) {
                $key = $name;
            } elseif ($positional !== []) {
                $key = array_shift($positional);
            } else {
                continue;
            }

            if (in_array($name, $encrypted, true)) {
                $parameters[$key] = $this->encryptValue($parameters[$key]);
            }
        }

        // Named parameters outside the path are emitted as query parameters.
        foreach (array_diff($encrypted, $route->parameterNames()) as $name) {
            if (array_key_exists($name, $parameters)) {
                $parameters[$name] = $this->encryptValue($parameters[$name]);
            }
        }

        return $parameters;
    }

    private function encryptValue(mixed $value): mixed
    {
        if ($value instanceof UrlRoutable) {
            $value = $value->getRouteKey();
        }

        // Only plain IDs are encrypted; anything else (e.g. an existing token) is left as is.
        if (is_int($value) || (is_string($value) && ctype_digit($value))) {
            return $this->cipher->encrypt($value);
        }

        return $value;
    }
}
