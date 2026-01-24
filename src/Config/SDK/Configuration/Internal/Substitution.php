<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal;

use Closure;
use InvalidArgumentException;
use function levenshtein;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvReader;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvSourceReader;
use function preg_match;
use function strcspn;
use function strlen;
use function strpos;
use function strrpos;
use function strtolower;
use function substr;
use function substr_count;

/**
 * @internal
 */
final class Substitution
{
    public static function process(string $s, EnvReader $envReader): string
    {
        if (!str_contains($s, '$')) {
            return $s;
        }

        $r = '';
        $terminalSink = static function (string $s, int $i, int $n) use (&$r): void {
            $r .= substr($s, $i, $n - $i);
        };
        $substitutionSink = static fn (string $s, int $i, int $n) => self::processSubstitution($s, $i, $n, $envReader, $terminalSink);
        $escapeSequenceSink = static fn (string $s, int $i, int $n) => self::processEscapeSequence($s, $i, $n, $substitutionSink);

        $escapeSequenceSink($s, 0, strlen($s));

        return $r;
    }

    public static function processEscapeSequence(string $s, int $i, int $n, Closure $sink): void
    {
        for ($t = $i; ($p = strcspn($s, '$', $t, $n - $t - 1) + $t + 1) < $n; $t = $p + 1) {
            if ($s[$p] !== '$') {
                continue;
            }

            $sink($s, $i, $p - 1);
            $sink('$', 0, 1);
            $i = $p + 1;
        }
        $sink($s, $i, $n);
    }

    public static function processSubstitution(string $s, int $i, int $n, EnvReader $envReader, Closure $sink): void
    {
        for ($t = $i; ($p = strcspn($s, '$', $t, $n - $t - 1) + $t + 1) < $n; $t = $p + 1) {
            if ($s[$p] !== '{' || ($s[($e = strcspn($s, "}\n", $p + 1, $n - $p - 1) + $p + 1)] ?? null) !== '}') {
                continue;
            }

            $v = self::performSubstitution($s, $p + 1, $e, $envReader);
            $sink($s, $i, $p - 1);
            $sink($v, 0, strlen($v));
            $i = ($p = $e) + 1;
        }
        $sink($s, $i, $n);
    }

    private static function performSubstitution(string $s, int $i, int $n, EnvReader $envReader, bool $resolvePrefix = true): string
    {
        if ($i === $n) {
            throw new InvalidArgumentException(self::formatErrorMessage('Missing environment variable name', $s, $i, 1, 'cannot be empty'));
        }

        preg_match('/\G[[:alpha:]_][[:alnum:]_]*+/', $s, $matches, 0, $i);
        $envName = $matches[0] ?? '';
        $d = $i + strlen($envName);
        if ($d === $n) {
            return $envReader->read($envName) ?? '';
        }

        if (!$matches || $s[$d] !== ':') {
            throw new InvalidArgumentException(self::formatErrorMessage('Invalid character in environment variable name', $s, $i, strcspn($s, ':', $i, $n - $i), 'has to match [a-zA-Z_][a-zA-Z0-9_]*'));
        }

        if ($s[$d + 1] === '-') {
            return $envReader->read($envName) ?? substr($s, $d + 2, $n - $d - 2);
        }

        if ($resolvePrefix) {
            $processors = [
                'env' => static fn (string $s, int $i, int $n, EnvReader $envReader) => self::performSubstitution($s, $i, $n, $envReader, false),
            ];
            if ($processor = $processors[$envName] ?? null) {
                return $processor($s, $d + 1, $n, $envReader);
            }

            $candidate = null;
            $minScore = 3;
            foreach ($processors as $name => $processor) {
                if (($score = levenshtein($name, strtolower($envName))) >= $minScore) {
                    continue;
                }

                try {
                    $processor($s, $d + 1, $n, new EnvSourceReader([]));
                    $candidate = $name;
                    $minScore = $score;
                } catch (InvalidArgumentException) {
                }
            }

            if ($candidate !== null) {
                throw new InvalidArgumentException(self::formatErrorMessage('Invalid substitution prefix', $s, $i, strlen($envName), sprintf('did you mean "%s"?', $candidate)));
            }
        }

        throw new InvalidArgumentException(self::formatErrorMessage('Invalid substitution', $s, $d, 1, 'did you mean :-?'));
    }

    private static function formatErrorMessage(string $message, string $s, int $position, int $length = 1, ?string $subMessage = null): string
    {
        $start = strrpos($s, "\n", $position - strlen($s));
        $start = $start === false ? 0 : $start + 1;
        $next = strpos($s, "\n", $position) ?: strlen($s);

        $message .= ' (at ';
        if ($line = substr_count($s, "\n", 0, $start)) {
            $message .= 'ln=';
            $message .= $line + 1;
            $message .= ',';
        }
        $message .= 'col=';
        $message .= $position - $start + 1;
        $message .= ')';
        $message .= "\n\t";
        $message .= substr($s, $start, $next - $start);
        $message .= "\n\t";
        for ($i = 0; $i < $position - $start; $i++) {
            $message .= ' ';
        }
        for ($i = 0; $i < $length; $i++) {
            $message .= '^';
        }
        if ($subMessage !== null) {
            $message .= ' --- ';
            $message .= $subMessage;
        }

        return $message;
    }
}
