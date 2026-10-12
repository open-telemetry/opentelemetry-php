<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal;

use const FILTER_DEFAULT;
use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_BOOLEAN;
use const FILTER_VALIDATE_FLOAT;
use const FILTER_VALIDATE_INT;
use function filter_var;
use function is_array;
use function is_string;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvReader;
use OpenTelemetry\Config\SDK\Configuration\Internal\Node\BooleanNode;
use OpenTelemetry\Config\SDK\Configuration\Internal\Node\FloatNode;
use OpenTelemetry\Config\SDK\Configuration\Internal\Node\IntegerNode;
use OpenTelemetry\Config\SDK\Configuration\Internal\Node\VariableNode;
use Symfony\Component\Config\Definition\ArrayNode;
use Symfony\Component\Config\Definition\NodeInterface;
use Symfony\Component\Config\Definition\PrototypedArrayNode;
use Symfony\Component\Config\Definition\ScalarNode;

/**
 * @internal
 */
final class EnvSubstitutionNormalization implements Normalization
{
    public function __construct(
        private readonly EnvReader $envReader,
    ) {
    }

    #[\Override]
    public function applyToNode(NodeInterface $node, mixed $value): mixed
    {
        if ($node instanceof PrototypedArrayNode && is_array($value)) {
            foreach ($value as $k => $v) {
                if (($r = $this->applyToNode($node->getPrototype(), $v)) !== $v) {
                    $value[$k] = $r;
                }
            }

            return $value;
        }
        if ($node instanceof ArrayNode && is_array($value)) {
            foreach ($value as $k => $v) {
                // Keys with no declared child are left alone: either they are unknown and about to
                // be rejected, or a `beforeNormalization()` closure is going to fold them away.
                if (!$child = $node->getChildren()[$k] ?? null) {
                    continue;
                }
                if (($r = $this->applyToNode($child, $v)) !== $v) {
                    $value[$k] = $r;
                }
            }

            return $value;
        }
        if ($node instanceof ScalarNode && is_string($value)) {
            $filter = match (true) {
                $node instanceof BooleanNode => FILTER_VALIDATE_BOOLEAN,
                $node instanceof IntegerNode => FILTER_VALIDATE_INT,
                $node instanceof FloatNode => FILTER_VALIDATE_FLOAT,
                default => FILTER_DEFAULT,
            };

            return $this->replaceEnvVariables($value, $filter);
        }
        if ($node instanceof VariableNode) {
            return $this->replaceEnvVariablesRecursive($value);
        }

        return $value;
    }

    private function replaceEnvVariables(string $value, int $filter = FILTER_DEFAULT): mixed
    {
        $replaced = Substitution::process($value, $this->envReader);

        if ($value === $replaced) {
            return $value;
        }
        if ($replaced === '') {
            return null;
        }

        return filter_var($replaced, $filter, FILTER_NULL_ON_FAILURE) ?? $replaced;
    }

    private function replaceEnvVariablesRecursive(mixed $value): mixed
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                if (($r = $this->replaceEnvVariablesRecursive($v)) !== $v) {
                    $value[$k] = $r;
                }
            }
        }
        if (is_string($value)) {
            $value = $this->replaceEnvVariables($value);
        }

        return $value;
    }
}
