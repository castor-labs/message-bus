<?php

declare(strict_types=1);

/**
 * @project Castor Message Bus
 * @link https://github.com/castor-labs/message-bus
 * @package castor/message-bus
 * @author Matias Navarro-Carter mnavarrocarter@gmail.com
 * @license MIT
 * @copyright 2021 CastorLabs Ltd
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Castor\MessageBus;

use Closure;
use Psr\Container\ContainerInterface;
use RuntimeException;

/**
 * Class ClosureMiddleware.
 */
final class ClosureMiddleware implements Middleware
{
    private Closure $closure;

    /**
     * ClosureMiddleware constructor.
     */
    public function __construct(Closure $closure)
    {
        $this->closure = $closure;
    }

    public static function lazy(string $service, ContainerInterface $container): ClosureMiddleware
    {
        return new self(static function (object $command, Stack $stack) use ($service, $container) {
            $middleware = $container->get($service);
            if (is_callable($middleware)) {
                $middleware = self::make($middleware);
            }
            if ($middleware instanceof Middleware) {
                $middleware->process($command, $stack);

                return;
            }

            throw new RuntimeException(sprintf(
                'Service %s must implement %s or be a callable',
                $service,
                Middleware::class
            ));
        });
    }

    public static function make(callable $callable): ClosureMiddleware
    {
        return new self(Closure::fromCallable($callable));
    }

    public function process(object $message, Stack $stack): void
    {
        ($this->closure)($message, $stack);
    }
}
