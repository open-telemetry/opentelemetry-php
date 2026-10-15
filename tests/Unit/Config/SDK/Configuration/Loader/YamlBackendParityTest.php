<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\SDK\Configuration\Loader;

use function dirname;
use FilesystemIterator;
use function in_array;
use OpenTelemetry\Config\SDK\Configuration\Internal\ConfigurationLoader;
use OpenTelemetry\Config\SDK\Configuration\Loader\YamlExtensionFileLoader;
use OpenTelemetry\Config\SDK\Configuration\Loader\YamlSymfonyFileLoader;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use function sprintf;
use function str_replace;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use function yaml_parse_file;

/**
 * Either YAML backend may be the one installed — both are only suggested — so a configuration file
 * has to mean the same thing under each.
 * {@see \OpenTelemetry\Config\SDK\Configuration\Internal\ConfigurationFileLoader} resolves
 * {@see YamlSymfonyFileLoader} before {@see YamlExtensionFileLoader}, so in a checkout that has
 * symfony/yaml the extension loader is never reached through `parseFile()`; this walks the fixture
 * corpus through both directly instead.
 *
 * The backends are not interchangeable in general: libyaml applies YAML 1.1 scalar typing where
 * symfony/yaml applies the 1.2 core schema the spec asks for, so `yes`, `017`, `0o17`, `1:30`,
 * bare dates and out-of-range integers resolve differently. No fixture uses those forms, and this
 * test exists to keep that true.
 */
#[CoversNothing]
#[RequiresPhpExtension('yaml')]
final class YamlBackendParityTest extends TestCase
{
    /** Stands in for "this backend would not parse the file at all". */
    private const REJECTED = '__rejected__';

    #[DataProvider('fixtureProvider')]
    public function test_both_backends_parse_fixture_identically(string $file): void
    {
        try {
            $symfony = Yaml::parseFile($file, Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE);
        } catch (ParseException) {
            $symfony = self::REJECTED;
        }

        // Silenced like the production loader does: an invalid file is reported by the `false`
        // return, and the fixture corpus deliberately includes one.
        $extension = @yaml_parse_file($file);
        if ($extension === false) {
            $extension = self::REJECTED;
        }

        if ($symfony === self::REJECTED || $extension === self::REJECTED) {
            // A deliberately invalid fixture must be rejected by both, not just by one.
            $this->assertSame(self::REJECTED, $symfony, sprintf('ext-yaml rejected "%s", symfony/yaml accepted it', $file));
            $this->assertSame(self::REJECTED, $extension, sprintf('symfony/yaml rejected "%s", ext-yaml accepted it', $file));

            return;
        }

        $this->assertSame($symfony, $extension, sprintf(
            'The YAML backends disagree on "%s". libyaml applies YAML 1.1 scalar typing, '
                . 'symfony/yaml the 1.2 core schema: `yes`/`on`, `017`, `0o17`, `1:30`, bare dates '
                . 'and out-of-range integers all resolve differently. Rewrite the value in a form '
                . 'both read the same way — quote it, or use `true`/`false` — rather than relaxing '
                . 'this assertion: whichever backend a user has installed has to read the fixture '
                . 'the same way.',
            $file,
        ));
    }

    /**
     * Precedence is decided by registration order in `ConfigurationFileLoader`, not by
     * `supports()`: both loaders answer for any `.yaml` path once their own backend is available.
     * That ext-yaml is the one selected when symfony/yaml is absent cannot be asserted here, since
     * a checkout always has symfony/yaml in `require-dev`.
     */
    public function test_both_backends_claim_yaml_files(): void
    {
        $locator = new FileLocator(__DIR__);

        $this->assertTrue((new YamlExtensionFileLoader(new ConfigurationLoader(null), $locator))->supports('config.yaml'));
        $this->assertTrue((new YamlSymfonyFileLoader(new ConfigurationLoader(null), $locator))->supports('config.yaml'));
    }

    /**
     * @return iterable<array{string}>
     */
    public static function fixtureProvider(): iterable
    {
        $root = dirname(__DIR__, 5);
        $directories = [
            $root . '/Integration/Config/configurations',
            $root . '/Unit/SDK/fixtures',
            $root . '/Unit/Config/SDK/Configuration/configurations',
        ];

        foreach ($directories as $directory) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
            );

            foreach ($files as $file) {
                if (!in_array($file->getExtension(), ['yaml', 'yml'], true)) {
                    continue;
                }

                yield str_replace($root . '/', '', $file->getPathname()) => [$file->getPathname()];
            }
        }
    }
}
