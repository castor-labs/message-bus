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
 * Class EnvelopeTest.
 *
 * @internal
 * @covers \Castor\MessageBus\Envelope
 */
class EnvelopeTest extends TestCase
{
    public function testItGetsOriginalMessage(): void
    {
        $message = new \stdClass();
        $envelope = FooEnvelope::wrap(BarEnvelope::wrap($message));

        self::assertSame($message, $envelope->unwrap());
    }

    public function testItGetsAnSpecificEnvelope(): void
    {
        $message = new \stdClass();
        $envelope = FooEnvelope::wrap(BarEnvelope::wrap($message));

        self::assertInstanceOf(BarEnvelope::class, $envelope->open(BarEnvelope::class));
    }

    public function testItGetsTheSameInstanceWhenEnvelopeCannotBeOpened(): void
    {
        $message = new \stdClass();
        $envelope = FooEnvelope::wrap(BarEnvelope::wrap($message));

        self::assertInstanceOf(FooEnvelope::class, $envelope->open('FakeEnvelope'));
    }

    public function testItGetsTheInnerMessage(): void
    {
        $message = new \stdClass();
        $envelope = FooEnvelope::wrap(BarEnvelope::wrap($message));

        self::assertInstanceOf(BarEnvelope::class, $envelope->getInnerMessage());
    }
}
