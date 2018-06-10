# Packagist Mirror

[![Build Status](https://goo.gl/PfY1J8)](https://travis-ci.org/Webysther/packagist-mirror)
[![Minimum PHP Version](https://goo.gl/nXUVen)](https://php.net/)
[![Packagist](https://goo.gl/155cHZ)](https://packagist.org/packages/webysther/packagist-mirror)
[![Codecov](https://goo.gl/T249vj)](https://github.com/Webysther/packagist-mirror)
[![Quality Score](https://goo.gl/3LwbA1)](https://scrutinizer-ci.com/g/Webysther/packagist-mirror)
[![Software License](https://goo.gl/FU2Kw1)](LICENSE)

This is PHP package repository [packagist.org](packagist.org) mirror site.

If you're using PHP Composer, commands like *create-project*, *require*, *update*, *remove* are often used. When those commands are executed, Composer will download information from the packages that are needed also from dependent packages. The number of json files downloaded depends on the complexity of the packages which are going to be used. The further you are from the location of the [packagist.org](packagist.org) server, the more time is needed to download json files. By using mirror, it will help save the time for downloading because the server location is closer.

This project aims to create a local mirror with ease, allowing greater availability for companies that want to use the composer but do not want to depend on the infrastructure of third parties. It is also possible to create a public mirror to reduce the load on the main repository and allow a better distribution of requests around the world.

## Install

Via Composer

``` bash
$ composer require webysther/packagist-mirror
```

Schedule the command to create and update the mirror:

```bash
$ php bin/mirror create --no-progress
```

Via Docker

Follow to [docker repository](https://github.com/Webysther/packagist-mirror-docker).

## Requirements

The following versions of PHP are supported by this version.

* PHP >=7.1

## Testing

``` bash
$ vendor/bin/phpunit
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Credits

- [Webysther Nunes](https://github.com/Webysther)
- [Hiraku NAKANO](https://github.com/hirak)
- [IndraGunawan](https://github.com/IndraGunawan)
- [All Contributors](https://github.com/Webysther/packagist-mirror/contributors)

## License

MIT License. Please see [License File](LICENSE) for more information.
