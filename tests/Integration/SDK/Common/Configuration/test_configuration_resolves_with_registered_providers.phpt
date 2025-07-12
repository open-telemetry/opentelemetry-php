--TEST--
SDK environment configuration test with SPI.
--INI--
CONFIG_SOURCE=from-ini
CONFIG_SOURCE_INI_ONLY=from-ini
--ENV--
CONFIG_SOURCE=from-env
CONFIG_SOURCE_ENV_ONLY=from-env
--FILE--
<?php

namespace OpenTelemetry\SDK;

class SdkAutoloader
{
    /**
     * Horrible hack. Is there a better way? Anybody, please!?
     *
     * Prevent Configuration being loaded by the SdkAutoloader::isEnabled()
     * method in SDK/_autoload.php
     */
    public static function autoload()
    {
        // Look away, I'm hideous.
    }
}

namespace Test\Plugin;

use OpenTelemetry\Config\SDK\Configuration\Environment\ArrayEnvSource;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvSourceProvider;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvSource;

require_once 'vendor/tbachert/spi/src/ServiceLoader.php';
require_once 'vendor/tbachert/spi/src/ServiceProviderRequirement.php';
require_once 'src/Config/SDK/Configuration/Environment/EnvSourceProvider.php';
require_once 'src/Config/SDK/Configuration/Environment/EnvSource.php';

class TestEnvSourceProvider implements EnvSourceProvider
{
    public function getEnvSource(): EnvSource
    {
        return new ArrayEnvSource([
            'CONFIG_SOURCE' => 'from-test',
            'CONFIG_SOURCE_TEST_ONLY' => 'from-test',
        ]);
    }
}

// In real world applications, should the SPI plugin not be enabled, then this may occur
// after CompositeResolver has already instantiated - in which case this is too late to do anything.
\Nevay\SPI\ServiceLoader::register(\OpenTelemetry\Config\SDK\Configuration\Environment\EnvSourceProvider::class, TestEnvSourceProvider::class);


/**
 * Now the real composer autoloading can take place and trigger file includes.
 */
require_once 'vendor/autoload.php';

/**
 * More typical application usage to follow.
 */
namespace OpenTelemetry\Tests\Integration\SDK\Common\Configuration;

use OpenTelemetry\SDK\Common\Configuration\Configuration;

echo Configuration::getString('CONFIG_SOURCE') . "\n";
echo Configuration::getString('CONFIG_SOURCE_TEST_ONLY') . "\n";
echo Configuration::getString('CONFIG_SOURCE_ENV_ONLY') . "\n";
echo Configuration::getString('CONFIG_SOURCE_INI_ONLY') . "\n";

?>
--EXPECTF--
from-test
from-test
from-env
from-ini
