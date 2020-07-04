<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextKey;

class CorrelationContext extends Context
{
    /**
     * @var CorrelationContext|null
     */
    protected $parent;

    /**
     * Return the k/v Correlation pairs from the CorrelationContext
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
     * @return CorrelationContext
     */
    public function removeCorrelation(ContextKey $key): CorrelationContext
    {
        if ($this->key === $key) {
            return $this->parent;
        }

        $this->removeCorrelationHelper($key, null);

        return $this;
    }

    /**
     * @param ContextKey $key
     * @param CorrelationContext|null $child
     */
    private function removeCorrelationHelper(ContextKey $key, ?CorrelationContext $child)
    {
        if ($this->key !== $key) {
            if (null === $this->parent) {
                return;
            }
            $this->parent->removeCorrelationHelper($key, $this);

            return;
        }

        $child->setParent($this->parent);
    }

    /**
     * @param CorrelationContext $parent
     *
     * @return null
     */
    protected function setParent(CorrelationContext $parent)
    {
        $this->parent = $parent;
    }

    /**
     * When called on a CorrelationContext, this function will destroy all Correlation data
     *
     * @return null
     */
    public function clearCorrelations()
    {
        if (null !== $this->parent) {
            $this->parent->clearCorrelations();
        }
        $this->key = null;
        $this->value = null;
        $this->parent = null;
    }
}
