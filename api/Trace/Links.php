<?php

declare(strict_types=1);

namespace OpenTelemetry\Trace;

interface Links extends \IteratorAggregate, \Countable
{
    public function getIterator(): LinksIterator;

    /**
     * Adding a link should not invalidate nor change any existing iterators.
     * @return Links Return $this to allow setting multiple links at once in a chain.
     */
    public function addLink(Link $link): Links;
}
