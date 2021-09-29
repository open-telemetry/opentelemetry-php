<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use ArrayIterator;
use function count;
use OpenTelemetry\Trace as API;

class Links implements API\Links
{
    /** @var list<API\Link> */
    private $links;

    /** @param list<API\Link> $links */
    public function __construct(iterable $links = [])
    {
        $this->links = $links;
    }

    /** @inheritDoc */
    public function addLink(API\Link $link): API\Links
    {
        $this->links[] = $link;

        return $this;
    }

    /** @psalm-mutation-free */
    public function count(): int
    {
        return count($this->links);
    }

    public function getIterator(): API\LinksIterator
    {
        return new class($this->links) implements API\LinksIterator {
            private $inner;
            public function __construct($events)
            {
                $this->inner = new ArrayIterator($events);
            }

            public function key(): int
            {
                return $this->inner->key();
            }

            public function current(): API\Link
            {
                return $this->inner->current();
            }

            public function rewind(): void
            {
                $this->inner->rewind();
            }

            public function valid(): bool
            {
                return $this->inner->valid();
            }

            public function next(): void
            {
                $this->inner->next();
            }
        };
    }
}
