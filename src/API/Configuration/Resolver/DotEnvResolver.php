<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Configuration\Resolver;

/**
 * Resolve variables from a `.env` file
 * @psalm-internal \OpenTelemetry\API\Configuration
 */
class DotEnvResolver implements ResolverInterface
{
    private array $values = [];

    public function __construct(string $path = null)
    {
        $path ??= getcwd();
        $filename = "{$path}/.env";
        if (file_exists($filename)) {
            $fp = fopen($filename, 'r');
            while (($buffer = fgets($fp, 4096)) !== false) {
                if (preg_match('/^ *#.*/', $buffer)) {
                    continue;
                }
                [$key, $value] = $this->split($buffer);
                if ($key && $value) {
                    $this->values[$key] = $value;
                }
            }
        }
    }

    public function retrieveValue(string $variableName)
    {
        return $this->values[$variableName];
    }

    public function hasVariable(string $variableName): bool
    {
        return array_key_exists($variableName, $this->values);
    }

    private function split(string $line): array
    {
        $pos = strpos($line, '#'); //start of comment
        if ($pos !== false) {
            $line = substr($line, 0, $pos);
        }
        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) {
            return [false, false];
        }

        return array_map('trim', $parts);
    }
}
