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

use InvalidArgumentException;

/**
 * Class MiddlewareStack is both a stack and a handler.
 */
final class MiddlewareStack implements Stack, Handler
{
    private Middleware $middleware;
    private Handler $handler;

    /**
     * MiddlewareStack constructor.
     */
    public function __construct(Middleware $middleware, Handler $handler)
    {
        $this->middleware = $middleware;
        $this->handler = $handler;
    }

    /**
     * Creates a middleware stack out of middleware.
     *
     * @param Middleware ...$middleware
     */
    public static function create(Middleware ...$middleware): MiddlewareStack
    {
        $middleware = array_reverse($middleware);
        $stack = new EndOfStack();
        foreach ($middleware as $frame) {
            $stack = new self($frame, $stack);
        }
        if (!$stack instanceof self) {
            throw new InvalidArgumentException('You need at least one middleware to create a stack');
        }

        return $stack;
    }

    /**
     * Handles a command.
     */
    public function handle(object $message): void
    {
        $this->middleware->process($message, $this);
    }

    public function next(): Handler
    {
        return $this->handler;
    }
}
