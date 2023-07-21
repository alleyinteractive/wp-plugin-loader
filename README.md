# WP Plugin Loader

Code-enabled WordPress plugin loading.

## Installation

You can install the package via Composer:

```bash
composer require alleyinteractive/wp-plugin-loader
```

## Usage

Activate the plugin in WordPress and use it like so:

```php
$plugin = Alley\WP\WP_Plugin_Loader\WP_Plugin_Loader\WP_Plugin_Loader();
$plugin->perform_magic();
```
<!--front-end-->
## Testing

Run `composer test` to run tests against PHPUnit and the PHP code in the plugin.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

This project is actively maintained by [Alley
Interactive](https://github.com/alleyinteractive). Like what you see? [Come work
with us](https://alley.com/careers/).

- [Sean Fisher](https://github.com/srtfisher)
- [All Contributors](../../contributors)

## License

The GNU General Public License (GPL) license. Please see [License File](LICENSE) for more information.
