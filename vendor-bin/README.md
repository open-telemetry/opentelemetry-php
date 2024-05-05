Testing tools installed and managed by https://github.com/bamarni/composer-bin-plugin.

Note that this should only be used for tools that are self-contained (specifically, *not* phpunit).

To add a tool:
- `composer bin <tool-name> require --dev <package>`
- `cd tools`
- `ln -s ../vendor-bin/<tool-name>/vendor/bin/<tool-executable>`

Tools are automatically updated as part of `composer update`.
