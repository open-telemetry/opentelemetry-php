<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\CorrelationContext;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextKey;
use OpenTelemetry\Context\ContextValueNotFoundException;

class CorrelationContext
{
    /**
     * @var CorrelationContext|null
     */
    private ?CorrelationContext $parent;

    /**
     * @var ContextKey|null
     */
    protected ?ContextKey $key;

    /**
     * @var mixed|null
     */
    private $value;

    public function __construct(ContextKey $key = null, $value = null, ?CorrelationContext $parent = null)
    {
        $this->key = $key;
        $this->value = $value;
        $this->parent = $parent;
    }

    /**
     * @param ContextKey $key
     * @param $value
     * @return CorrelationContext
     */
    public function set(ContextKey $key, $value): CorrelationContext
    {
        return new CorrelationContext($key, $value, $this);
    }

    /**
     * @param ContextKey $key
     *
     * @return mixed
     */
    public function get(ContextKey $key)
    {
        if ($this->key === $key) {
            return $this->value;
        }
        if (null === $this->parent) {
            throw new ContextValueNotFoundException();
        }

        return $this->parent->get($key);
    }

    /**
     * @param CorrelationContext $context
     */
    public function getCorrelations($context = null)
    {
        if ($this->parent == null) {
            return [$this->key => $this->value];
        }

        $val =($this->parent->getCorrelations());
        $val[$this->key] = $this->value;
        return $val;
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

    private function removeCorrelationHelper(ContextKey $key, ?CorrelationContext $child)
    {
        if ($this->key != $key) {
            if (null === $this->parent) {
                return;
            }

            $this->parent->removeCorrelationHelper($key, $this);
            return;
        }

        $child->setParent($this->parent);
    }

    public function clearCorrelations()
    {
        // TODO: Write me
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
}
