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

use Generator;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class MultiTest extends TestCase
{
    public function testItCountsMessages(): void
    {
        $message = new \stdClass();
        $multi = Multi::wrap([$message]);
        self::assertCount(1, $multi);
    }

    public function testItWrapsIterator(): void
    {
        $messageOne = new \stdClass();
        $messageTwo = new \stdClass();
        $generator = static function () use ($messageOne, $messageTwo): Generator {
            yield $messageOne;
            yield $messageTwo;
        };
        $multi = Multi::wrap($generator());
        self::assertCount(2, $multi);
    }
}
