<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\SemConv;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * `make semconv` runs add-version.sh to add the new release to the Version enum. Nothing else
 * fails when the case is missing, so the script is tested here against a copy of the real enum.
 */
#[CoversNothing]
final class AddVersionTest extends TestCase
{
    private const SCRIPT = __DIR__ . '/../../../script/semantic-conventions/add-version.sh';
    private const VERSION_PHP = __DIR__ . '/../../../src/SemConv/Version.php';

    private string $file;

    #[\Override]
    protected function setUp(): void
    {
        $this->file = sys_get_temp_dir() . '/otel-semconv-version-' . bin2hex(random_bytes(4)) . '.php';
        copy(self::VERSION_PHP, $this->file);
    }

    #[\Override]
    protected function tearDown(): void
    {
        unlink($this->file);
    }

    public function test_adds_a_new_version_as_the_first_case(): void
    {
        $this->assertSame(0, $this->addVersion('9.0.0'));

        $this->assertSame('9.0.0', $this->caseValues()[0]);
        $this->assertStringContainsString("    case VERSION_9_0_0 = '9.0.0';\n", file_get_contents($this->file));
        $this->assertValidPhp();
    }

    public function test_adds_a_patch_release_between_older_minors(): void
    {
        $this->assertSame(0, $this->addVersion('1.41.2'));

        $values = $this->caseValues();
        $index = array_search('1.41.2', $values, true);
        $this->assertSame('1.42.0', $values[$index - 1]);
        $this->assertSame('1.41.1', $values[$index + 1]);
        $this->assertValidPhp();
    }

    public function test_adds_a_version_older_than_every_case_as_the_last_case(): void
    {
        $this->assertSame(0, $this->addVersion('1.0.0'));

        $values = $this->caseValues();
        $this->assertSame('1.0.0', end($values));
        $this->assertValidPhp();
    }

    public function test_running_twice_adds_the_case_once(): void
    {
        $this->addVersion('9.0.0');
        $once = file_get_contents($this->file);

        $this->assertSame(0, $this->addVersion('9.0.0'));

        $this->assertSame($once, file_get_contents($this->file));
        $this->assertSame(1, substr_count($once, "'9.0.0'"));
    }

    public function test_leaves_an_existing_version_alone(): void
    {
        $this->assertSame(0, $this->addVersion('1.38.0'));

        $this->assertFileEquals(self::VERSION_PHP, $this->file);
    }

    public function test_rejects_a_version_that_is_not_major_minor_patch(): void
    {
        $this->assertNotSame(0, $this->addVersion('1.46'));

        $this->assertFileEquals(self::VERSION_PHP, $this->file);
    }

    private function addVersion(string $version): int
    {
        exec(sprintf('bash %s %s %s 2>&1', escapeshellarg(self::SCRIPT), escapeshellarg($version), escapeshellarg($this->file)), $output, $code);

        return $code;
    }

    /** @return list<string> */
    private function caseValues(): array
    {
        preg_match_all("/^    case VERSION_\\w+ = '([^']+)';$/m", file_get_contents($this->file), $matches);

        return $matches[1];
    }

    private function assertValidPhp(): void
    {
        exec(sprintf('%s -l %s 2>&1', escapeshellarg(PHP_BINARY), escapeshellarg($this->file)), $output, $code);
        $this->assertSame(0, $code, implode("\n", $output));
    }
}
