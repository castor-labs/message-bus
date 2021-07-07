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

namespace Castor;

use Castor\MessageBus as Bus;
use InvalidArgumentException;
use Traversable;

/**
 * Class MessageBus.
 */
final class MessageBus implements Bus\Handler
{
    /**
     * @var array<int,Bus\Middleware>
     */
    private array $middleware;

    /**
     * Bus constructor.
     */
    public function __construct(Bus\Middleware ...$middleware)
    {
        $this->middleware = $middleware;
    }

    public static function fromIterator(Traversable $traversable): MessageBus
    {
        return new self(...iterator_to_array($traversable));
    }

    public function add(Bus\Middleware $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    /**
     * @throws Bus\HandlingError
     */
    public function handle(object $message): void
    {
        try {
            $stack = Bus\MiddlewareStack::create(...$this->middleware);
        } catch (InvalidArgumentException $e) {
            throw new Bus\HandlingError('There was a problem handling the message', 0, $e);
        }
        $stack->handle($message);
    }
}
