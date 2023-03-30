# OpenTelemetry protobuf files

## Protobuf Runtime library

There exist two protobuf runtime libraries that offer the same set of APIs, allowing developers to choose the one that 
best suits their needs.

The first and easiest option is to install the Protobuf PHP Runtime Library through composer. This can be the easiest 
way to get started quickly. Either run `composer require google/protobuf`. Or update your `composer.json` as follows:

```json
"require": {
  "google/protobuf": "^v3.3.0"
}
```

Alternatively, and the recommended option for production is to install the Protobuf C extension for PHP. The extension
makes both exporters _significantly_ more performant. This can be easily installed with the following command:
```sh
$ sudo pecl install protobuf
```
