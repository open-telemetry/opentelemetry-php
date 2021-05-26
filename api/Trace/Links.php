<?php

declare(strict_types=1);

namespace OpenTelemetry\Trace;

interface Links extends \IteratorAggregate, \Countable
{
    public function count(): int;
    public function getIterator(): LinksIterator;

    /**
     * Adding a link should not invalidate nor change any existing iterators.
     * @param SpanContext $context
     * @param Attributes|null $attributes
     * @return Links Return $this to allow setting multiple links at once in a chain.
     */
    public function addLink(SpanContext $context, ?Attributes $attributes = null): Links;
}
