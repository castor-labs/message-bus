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

use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ExecuteMultipleTest extends TestCase
{
    public function testItDoesNotExecuteMultiple(): void
    {
        $message = new \stdClass();
        $stack = $this->createMock(Stack::class);
        $handler = $this->createMock(Handler::class);
        $middleware = new ExecuteMultiple();

        $stack->expects($this->once())
            ->method('next')
            ->willReturn($handler)
        ;
        $handler->expects($this->once())
            ->method('handle')
            ->with($message)
        ;

        $middleware->process($message, $stack);
    }

    public function testItExecutesMultiple(): void
    {
        $messageOne = new \stdClass();
        $messageTwo = new \stdClass();
        $stack = $this->createMock(Stack::class);
        $handler = $this->createMock(Handler::class);
        $middleware = new ExecuteMultiple();

        $stack->expects($this->exactly(2))
            ->method('next')
            ->willReturn($handler)
        ;
        $handler->expects($this->exactly(2))
            ->method('handle')
            ->withConsecutive([$messageOne], [$messageTwo])
        ;

        $multi = new Multi();
        $multi->push($messageOne);
        $multi->push($messageTwo);

        $middleware->process($multi, $stack);
    }

    public function testItExecutesMultipleFromEnvelope(): void
    {
        $messageOne = new \stdClass();
        $messageTwo = new \stdClass();
        $stack = $this->createMock(Stack::class);
        $handler = $this->createMock(Handler::class);
        $middleware = new ExecuteMultiple();

        $stack->expects($this->exactly(2))
            ->method('next')
            ->willReturn($handler)
        ;
        $handler->expects($this->exactly(2))
            ->method('handle')
            ->withConsecutive([$messageOne], [$messageTwo])
        ;

        $middleware->process(FooEnvelope::wrap(Multi::wrap([$messageOne, $messageTwo])), $stack);
    }
}
