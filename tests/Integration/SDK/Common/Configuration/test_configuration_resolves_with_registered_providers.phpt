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

namespace OpenTelemetry\Tests\Integration\SDK\Common\Configuration;

require_once 'vendor/autoload.php';

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
