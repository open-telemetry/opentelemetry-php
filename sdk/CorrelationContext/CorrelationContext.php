<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\CorrelationContext;

use OpenTelemetry\Context\Context;

class CorrelationContext implements Context
{
    /**
     * @param Context $context
     *
     * @return Generator|mixed[]
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

    private function removeCorrelationHelper(ContextKey $key, Context $child)
    {
        if ($this->key != $key) {
            if (is_null($this->parent)) {
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
