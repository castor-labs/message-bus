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

use Castor\MessageBus\ClosureMiddleware;
use Castor\MessageBus\HandlingError;
use Castor\MessageBus\Middleware;
use Castor\MessageBus\MiddlewareStack;
use Castor\MessageBus\Stack;
use PHPUnit\Framework\TestCase;

/**
 * Class MessageBusTest.
 *
 * @internal
 * @coversNothing
 */
class MessageBusTest extends TestCase
{
    public function testItExecutesMiddlewareMock(): void
    {
        $message = new \stdClass();
        $middleware = $this->createMock(Middleware::class);

        $middleware->expects($this->once())
            ->method('process')
            ->with($message, $this->isInstanceOf(Stack::class))
        ;

        $bus = new MessageBus();
        $bus->add($middleware);
        $bus->handle($message);
    }

    public function testItExecutesAssertMiddleware(): void
    {
        $message = new \stdClass();
        $middleware = ClosureMiddleware::make(function (object $passedMessage, Stack $stack) use ($message) {
            self::assertSame($message, $passedMessage);
            self::assertInstanceOf(MiddlewareStack::class, $stack);
        });

        $bus = new MessageBus();
        $bus->add($middleware);
        $bus->handle($message);
    }

    public function testItThrowsExceptionOnEmptyBus(): void
    {
        $message = new \stdClass();
        $bus = new MessageBus();
        $this->expectException(HandlingError::class);
        $bus->handle($message);
    }

    public function testItThrowsExceptionOnExhaustedBus(): void
    {
        $message = new \stdClass();
        $middleware = ClosureMiddleware::make(function (object $passedMessage, Stack $stack) use ($message) {
            self::assertSame($message, $passedMessage);
            self::assertInstanceOf(MiddlewareStack::class, $stack);
            $stack->next()->handle($passedMessage);
        });
        $bus = new MessageBus();
        $bus->add($middleware);
        $this->expectException(HandlingError::class);
        $bus->handle($message);
    }
}
