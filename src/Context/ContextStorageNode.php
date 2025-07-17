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

    #[\Override]
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->localStorage[$offset]);
    }

    #[\Override]
    public function offsetGet(mixed $offset): mixed
    {
        return $this->localStorage[$offset];
    }

    #[\Override]
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->localStorage[$offset] = $value;
    }

    #[\Override]
    public function offsetUnset(mixed $offset): void
    {
        unset($this->localStorage[$offset]);
    }

    #[\Override]
    public function context(): ContextInterface
    {
        return $this->context;
    }

    #[\Override]
    public function detach(): int
    {
        $flags = 0;
        if ($this->head !== $this->head->storage->head()) {
            $flags |= ScopeInterface::INACTIVE;
        }

        static $detached;
        $detached ??= (new \ReflectionClass(self::class))->newInstanceWithoutConstructor();

        if ($this === $this->head->node) {
            assert($this->previous !== $detached);
            $this->head->node = $this->previous;
            $this->previous = $detached;

            return $flags;
        }

        if ($this->previous === $detached) {
            return $flags | ScopeInterface::DETACHED;
        }

        assert($this->head->node !== null);
        for ($n = $this->head->node, $depth = 1;
            $n->previous !== $this;
            $n = $n->previous, $depth++) {
            assert($n->previous !== null);
        }
        $n->previous = $this->previous;
        $this->previous = $detached;

        return $flags | ScopeInterface::MISMATCH | $depth;
    }

    private function __clone()
    {
    }
}
