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

namespace Castor\MessageBus\Handler;

/**
 * Class ClassSuffixInflector.
 */
final class ClassSuffixInflector implements Inflector
{
    private string $suffix;

    /**
     * ClassSuffixInflector constructor.
     */
    public function __construct(string $suffix = 'Handler')
    {
        $this->suffix = $suffix;
    }

    /**
     * {@inheritDoc}
     */
    public function inflect(object $message): string
    {
        $cmdClass = get_class($message);
        $handlerClass = $cmdClass.$this->suffix;
        if (!class_exists($handlerClass)) {
            throw new InflectionError(sprintf(
                'Handler class %s for command %s does not exist',
                $handlerClass,
                $cmdClass
            ));
        }

        return $handlerClass;
    }
}
