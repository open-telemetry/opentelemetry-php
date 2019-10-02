## OpenTelemetry PHP Repository 

Welcome to the opentelemetry-php repository.  We will be updating this repository with the initial implementation.


- [SIG Meetings](#meetings)
- [Tracing](#tracing)
- [Testing](#testing)
## Meetings
Our meetings are held every Wednesday at 10:30am PST / 1:30pm EST.  Please reach out on [gitter.im](https://gitter.im/open-telemetry/open-telemetry-php) if you'd like to be invited.  The public calendar invite will be shared once it becomes available.

Meeting Agenda / Notes will be housed [here](https://docs.google.com/document/d/1WLDZGLY24rk5fRudjdQAcx_u81ZQWCF3zxiNT-sz7DI/edit?usp=sharing)

## Tracing
This library is under active development.  The current status can be tracked by the milestones in the github issues tab.
Below is a simple example use:

```php
<?php
use OpenTelemetry\Tracing\Builder;
use OpenTelemetry\Tracing\SpanContext;
$spanContext = SpanContext::generate(); // or extract from headers
$tracer = Builder::create()->setSpanContext($spanContext)->getTracer();
// start a span, register some events
$span = $tracer->createSpan('session.generate');
// set attributes as array
$span->setAttributes([ 'remote_ip' => '5.23.99.245' ]);
// set attribute one by one
$span->setAttribute('country', 'Russia');
$span->addEvent('found_login', [
  'id' => 67235,
  'username' => 'nekufa',
]);
$span->addEvent('generated_session', [
  'id' => md5(microtime(true))
]);
$span->end(); // pass status as an optional argument
```

# Testing
To make sure the tests in this repo work as you expect, you can use the included docker test wrapper:

1.)  Make sure that you have docker installed
2.)  Execute `script/dockertest` from your bash compatible shell  
3.)  You should see the test script output
