<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextKey;

/**
 * @extends Context<Baggage>
 */
class Baggage extends Context
{
    protected $parent;

    /**
     * Return the k/v Correlation pairs from the Baggage
     * The yielded values are of the form `ContextKey => mixed`
     *
     * @return \Generator
     */
    public function getCorrelations()
    {
        if (null !== $this->parent) {
            yield from $this->parent->getCorrelations();
        }
        yield $this->key => $this->value;
    }

    /**
     * @param ContextKey $key
     *
     * @return null|self
     */
    public function removeCorrelation(ContextKey $key): ?self
    {
        if ($this->key === $key) {
            return $this->parent;
        }

        $this->removeCorrelationHelper($key, null);

        return $this;
    }

    /**
     * @param ContextKey $key
     * @param Baggage|null $child
     */
    private function removeCorrelationHelper(ContextKey $key, ?Baggage $child)
    {
        if ($this->key !== $key) {
            if (null === $this->parent) {
                return;
            }
            $this->parent->removeCorrelationHelper($key, $this);

            return;
        }

        if ($child !== null && $this->parent !== null) {
            $child->setParent($this->parent);
        }
    }

    /**
     * @param Baggage $parent
     */
    protected function setParent(Baggage $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * When called on a Baggage, this function will destroy all Correlation data
     */
    public function clearCorrelations(): void
    {
        if (null !== $this->parent) {
            $this->parent->clearCorrelations();
        }
        $this->key = null;
        $this->value = null;
        $this->parent = null;
    }
}
