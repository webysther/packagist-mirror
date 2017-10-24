# Packagist Mirror Creation

[![Build Status](https://goo.gl/8XxkEZ)](https://travis-ci.org/Webysther/mirror)
[![Minimum PHP Version](https://goo.gl/PnnkKQ)](https://php.net/)
[![Packagist](https://goo.gl/7HFLGg)](https://packagist.org/packages/webysther/mirror)
[![Coverage Status](https://goo.gl/jn3gpk)](https://scrutinizer-ci.com/g/Webysther/mirror/code-structure)
[![Quality Score](https://goo.gl/Mo4Ekf)](https://scrutinizer-ci.com/g/Webysther/mirror)
[![Software License](https://goo.gl/ieFvw1)](LICENSE.md)

Crawl packagist and download all metadata about packages.

After downloading, you can distribute it with a static web server, 
you can create a mirror of packagist.org.

## Install

Via Composer

``` bash
$ composer require webysther/mirror
```

Via Docker

Follow to [docker repository](https://github.com/Webysther/packagist-mirror-docker).

## Requirements

The following versions of PHP are supported by this version.

* PHP 7.0
* PHP 7.1

## Testing

``` bash
$ composer qa:paratest
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Credits

- [Webysther Nunes](https://github.com/Webysther)
- [Hiraku NAKANO](https://github.com/hirak)
- [All Contributors](https://github.com/Webysther/mirror/contributors)

## License

Public domain. Please see [License File](LICENSE.md) for more information.
