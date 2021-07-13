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

/**
 * Class MessageBus.
 */
final class MessageBus implements Bus\Handler
{
    /**
     * @var Bus\Middleware[]
     */
    private array $middleware;

    /**
     * Bus constructor.
     *
     * @psalm-param array<int,Bus\Middleware> $messages
     */
    public function __construct(Bus\Middleware ...$middleware)
    {
        $this->middleware = $middleware;
    }

    /**
     * @psalm-param iterable<int,Bus\Middleware> $iterable
     */
    public static function fromIterable(iterable $iterable): MessageBus
    {
        if (is_array($iterable)) {
            return new self(...$iterable);
        }
        $bus = new self();
        foreach ($iterable as $middleware) {
            $bus->add($middleware);
        }

        return $bus;
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
