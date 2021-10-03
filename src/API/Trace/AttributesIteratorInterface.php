<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

interface AttributesIteratorInterface extends \Iterator
{
    public function key(): string;
    public function current(): AttributeInterface;

    /**
     * Should be valid to call rewind as many times as desired UNTIL next() has been called; then it is implementation
     * defined whether it is valid or not. The implementation should throw if it cannot be rewound.
     */
    public function rewind(): void;
    public function valid(): bool;
    public function next(): void;
}
