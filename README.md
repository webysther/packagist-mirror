# Packagist Mirror

[![Build Status](https://goo.gl/PfY1J8)](https://travis-ci.org/Webysther/packagist-mirror)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.2-8892BF.svg?style=flat-square&maxAge=3600)](https://php.net/)
[![Packagist](https://img.shields.io/packagist/v/webysther/packagist-mirror.svg?style=flat-square)](https://packagist.org/packages/webysther/packagist-mirror)
[![Codecov](https://goo.gl/T249vj)](https://github.com/Webysther/packagist-mirror)
[![Quality Score](https://goo.gl/3LwbA1)](https://scrutinizer-ci.com/g/Webysther/packagist-mirror)
[![Software License](https://goo.gl/FU2Kw1)](LICENSE)
[![Mentioned in Awesome composer](https://awesome.re/mentioned-badge.svg)](https://github.com/jakoch/awesome-composer#packagist-mirrors)

This is PHP package repository [packagist.org](packagist.org) mirror site.

If you're using PHP Composer, commands like *create-project*, *require*, *update*, *remove* are often used. When those commands are executed, Composer will download information from the packages that are needed also from dependent packages. The number of json files downloaded depends on the complexity of the packages which are going to be used. The further you are from the location of the [packagist.org](packagist.org) server, the more time is needed to download json files. By using mirror, it will help save the time for downloading because the server location is closer.

This project aims to create a local mirror with ease, allowing greater availability for companies that want to use the composer but do not want to depend on the infrastructure of third parties. It is also possible to create a public mirror to reduce the load on the main repository and allow a better distribution of requests around the world.

## Packagist metadata mirrors around the world

Data mirrors used to download repositories metadata built using this [recommended repository](https://packagist.org/mirrors) or another:

- Africa, South Africa [packagist.co.za](https://packagist.co.za)
- Asia, China [mirrors.aliyun.com/composer](https://mirrors.aliyun.com/composer)
- Asia, China [pkg.phpcomposer.com](https://packagist.phpcomposer.com)
- Asia, Indonesia [packagist.phpindonesia.id](https://packagist.phpindonesia.id)
- Asia, [India packagist.in](https://packagist.in) 
- Asia, Japan [packagist.jp](https://packagist.jp) (legacy based of)
- South America, Brazil [packagist.com.br](https://packagist.com.br) (our mirror)

![World Map](/resources/public/world_map.svg)

The colors represent the topology drawn below.

## Create your own mirror

![Topology](/resources/public/network.svg)

With docker and nginx:

The mirror creation save all data as .gz to save disk space and CPU, you need to enable reverse gz decode when a client ask for the decompressed version, normally used only for legacy composer clients.

Change you [nginx configuration](https://www.nginx.com/resources/wiki/start/topics/examples/full/) of [*gzip_static*](http://nginx.org/en/docs/http/ngx_http_gunzip_module.html) and [*gunzip*](http://nginx.org/en/docs/http/ngx_http_gzip_static_module.html) as is:

Create a website on a default nginx instalattion:
```bash
sudo vim /etc/nginx/sites-available/packagist.com.br.conf
```

```bash
server {
    index.html;

    server_name packagist.com.br www.packagist.com.br;

    location / {
        try_files $uri $uri/ =404;
        gzip_static on;
        gunzip on;
    }
}
```

Tip: use a machine with 2GB at least of memory, with that all metadata keep to the memory helping the nginx and disk to not be consumed at all.

After install nginx edit `/etc/crontab`:

```bash
* * * * * root docker run --name mirror --rm -v /var/www:/public \
-e MAINTAINER_REPO='packagist.com.br' \
-e APP_COUNTRY_NAME='Brazil' \
-e APP_COUNTRY_NAME='br' \
-e MAINTAINER_MIRROR='Webysther' \
-e MAINTAINER_PROFILE='https://github.com/Webysther' \
-e MAINTAINER_REPO='https://github.com/Webysther/packagist-mirror' \
-e URL='packagist.com.br' \
webysther/packagist-mirror
```
to more options about image go to [docker repository](https://github.com/Webysther/packagist-mirror-docker).

Put inside your `~/.*rc` (`~/.bashrc`/`~/.zshrc`/`~/.config/fish/config.fish`):
```bash
alias logs='watch -n 0.5 docker logs --tail 10 -t mirror'
```

Update your env vars and see monitoring packagist mirror creation:
```bash
source ~/.*rc
logs
```

## Install 

Using with [docker repository](https://github.com/Webysther/packagist-mirror-docker) or composer local:

``` bash
$ git clone https://github.com/Webysther/packagist-mirror.git
$ cd packagist-mirror && composer install
$ cp .env.example .env
```

Schedule the command to create and update the mirror:

```bash
$ php bin/mirror create -vvv
```

## Development & Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Requirements

The following versions of PHP are supported by this version.

* PHP >=7.2

## Testing

``` bash
$ vendor/bin/phpunit
```

## Credits

- [Webysther Nunes](https://github.com/Webysther)
- [Hiraku NAKANO](https://github.com/hirak)
- [IndraGunawan](https://github.com/IndraGunawan)
- [All Contributors](https://github.com/Webysther/packagist-mirror/contributors)

## License

MIT License. Please see [License File](LICENSE) for more information.
