# Backwards Compatibility

We aim to provide backward compatibility (without any guarantee) even for alpha releases, however the library will raise notices indicating breaking changes and what to do about them. \
If you don't want these notices to appear or change the error message level, you can do so by calling:
```php
OpenTelemetry\SDK\Common\Dev\Compatibility\Util::setErrorLevel(0)
``` 
to turn messages off completely, or (for example)
```php
OpenTelemetry\SDK\Common\Dev\Compatibility\Util::setErrorLevel(E_USER_DEPRECATED)
``` 
to trigger only deprecation notices. Valid error levels are `0` (none), `E_USER_DEPRECATED`, `E_USER_NOTICE`, `E_USER_WARNING` and `E_USER_ERROR`  \
However (as long as in alpha) it is safer to pin a dependency on the library to a specific version and/or make the adjustments
mentioned in the provided messages, since doing otherwise may break things completely for you in the future!
