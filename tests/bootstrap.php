<?php

declare(strict_types=1);

use DG\BypassFinals;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/SDK/Metrics/compatibility.php';

BypassFinals::enable();

assert_options(ASSERT_ACTIVE, true);
