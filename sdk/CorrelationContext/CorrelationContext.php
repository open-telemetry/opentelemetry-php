<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\CorrelationContext;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextKey;

class CorrelationContext extends Context
{
    /**
     * @var CorrelationContext|null
     */
    private $parent;

    /**
     * @param Context $context
     */
    public function getCorrelations($context = null)
    {
        // TODO: Write me
    }

    /**
     * @param ContextKey $key
     *
     * @return Context
     */
    public function removeCorrelation(ContextKey $key): Context
    {
        if ($this->key === $key) {
            return $this->parent;
        }

        $this->removeCorrelationHelper($key, null);

        return $this;
    }

    private function removeCorrelationHelper(ContextKey $key, ?Context $child)
    {
        if ($this->key != $key) {
            if (null === $this->parent) {
                return;
            }

            $this->parent->removeCorrelationHelper($key, $this);
        }

        $child->setParent($this->parent);
    }

    public function clearCorrelations()
    {
        // TODO: Write me
    }
}
