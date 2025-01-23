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
use function preg_replace_callback;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\BooleanNodeDefinition;
use Symfony\Component\Config\Definition\Builder\FloatNodeDefinition;
use Symfony\Component\Config\Definition\Builder\IntegerNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\ParentNodeDefinitionInterface;
use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;
use Symfony\Component\Config\Definition\Builder\VariableNodeDefinition;

/**
 * @internal
 */
final class EnvSubstitutionNormalization implements Normalization
{
    public function __construct(
        private readonly EnvReader $envReader,
    ) {
    }

    public function apply(ArrayNodeDefinition $root): void
    {
        foreach ($root->getChildNodeDefinitions() as $childNode) {
            $this->doApply($childNode);
        }
    }

    private function doApply(NodeDefinition $node): void
    {
        if ($node instanceof ScalarNodeDefinition) {
            $filter = match (true) {
                $node instanceof BooleanNodeDefinition => FILTER_VALIDATE_BOOLEAN,
                $node instanceof IntegerNodeDefinition => FILTER_VALIDATE_INT,
                $node instanceof FloatNodeDefinition => FILTER_VALIDATE_FLOAT,
                default => FILTER_DEFAULT,
            };
            $node->beforeNormalization()->ifString()->then(fn (string $v) => $this->replaceEnvVariables($v, $filter))->end();
        }
        if ($node instanceof VariableNodeDefinition) {
            $node->beforeNormalization()->always($this->replaceEnvVariablesRecursive(...))->end();
        }

        if ($node instanceof ParentNodeDefinitionInterface) {
            foreach ($node->getChildNodeDefinitions() as $childNode) {
                $this->doApply($childNode);
            }
        }
    }

    private function replaceEnvVariables(string $value, int $filter = FILTER_DEFAULT): mixed
    {
        $replaced = preg_replace_callback(
            '/\$\{(?:env:)?(?<ENV_NAME>[a-zA-Z_]\w*)(?::-(?<DEFAULT_VALUE>[^\n]*))?}/',
            fn (array $matches): string => $this->envReader->read($matches['ENV_NAME']) ?? $matches['DEFAULT_VALUE'] ?? '',
            $value,
            -1,
            $count,
        );

        if (!$count) {
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
