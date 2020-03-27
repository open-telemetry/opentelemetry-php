<?php

declare(strict_types=1);

namespace OpenTelemetry\Trace;

interface LinksIterator extends \Iterator
{

    /**
     * Should be valid to call rewind as many times as desired UNTIL next() has been called; then it is implementation
     * defined whether it is valid or not. The implementation should throw if it cannot be rewound.
     */
    public function rewind(): void;

    public function valid(): bool;

    /**
     * @return int The order the link was added.
     */
    public function key(): int;

    /**
     * @return Link Should throw if the iterator is !valid().
     */
    public function current(): Link;

    public function next(): void;
}
