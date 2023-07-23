# WP Plugin Loader

Code-enabled WordPress plugin loading.

## Installation

You can install the package via Composer:

```bash
composer require alleyinteractive/wp-plugin-loader
```

## Usage

Load the package via Composer and use it like so:

```php
use Alley\WP\WP_Plugin_Loader;

new WP_Plugin_Loader( [
	'plugin/plugin.php',
	'plugin-name-without-file',
] );
```

The plugin loader will load the specified plugins, be it files or folders under
`plugins`/`client-mu-plugins`, and mark them as activated on the plugins screen.

Also supports preventing activations of plugins via the plugins screen (useful
to fully lock down the plugins enabled on site);

```php
use Alley\WP\WP_Plugin_Loader;

( new WP_Plugin_Loader( [ ... ] )->prevent_activations();
```

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
