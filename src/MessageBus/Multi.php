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

use Countable;
use Generator;
use IteratorAggregate;

/**
 * Class Multi.
 */
class Multi implements Countable, IteratorAggregate
{
    /**
     * @var object[]
     */
    private array $messages;

    /**
     * Multi constructor.
     */
    public function __construct(array $messages = [])
    {
        $this->messages = $messages;
    }

    public function push(object $message): void
    {
        $this->messages[] = $message;
    }

    public static function wrap(iterable $commands): Multi
    {
        if (is_array($commands)) {
            return new self($commands);
        }
        $multi = new self();
        foreach ($commands as $command) {
            $multi->push($command);
        }

        return $multi;
    }

    public function count(): int
    {
        return count($this->messages);
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getIterator(): Generator
    {
        yield from $this->messages;
    }
}
