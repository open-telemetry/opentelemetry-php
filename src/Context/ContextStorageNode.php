<?php

declare(strict_types=1);

namespace OpenTelemetry\Context;

use function assert;

/**
 * @internal
 */
final class ContextStorageNode implements ScopeInterface, ContextStorageScopeInterface
{
    private array $localStorage = [];

    public function __construct(
        public ContextInterface $context,
        public ContextStorageHead $head,
        private ?ContextStorageNode $previous = null,
    ) {
    }

    public function offsetExists($offset): bool
    {
        return isset($this->localStorage[$offset]);
    }

    /**
     * @phan-suppress PhanUndeclaredClassAttribute
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->localStorage[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        $this->localStorage[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->localStorage[$offset]);
    }

    public function context(): ContextInterface
    {
        return $this->context;
    }

    public function detach(): int
    {
        $flags = 0;
        if ($this->head !== $this->head->storage->current) {
            $flags |= ScopeInterface::INACTIVE;
        }

        if ($this === $this->head->node) {
            assert($this->previous !== $this);
            $this->head->node = $this->previous;
            $this->previous = $this;

            return $flags;
        }

        if ($this->previous === $this) {
            return $flags | ScopeInterface::DETACHED;
        }

        assert($this->head->node !== null);
        for ($n = $this->head->node, $depth = 1;
            $n->previous !== $this;
            $n = $n->previous, $depth++) {
            assert($n->previous !== null);
        }
        $n->previous = $this->previous;
        $this->previous = $this;

        return $flags | ScopeInterface::MISMATCH | $depth;
    }

    private function __clone()
    {
    }
}
