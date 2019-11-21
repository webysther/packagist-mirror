# 📦 Packagist Mirror

[![Build Status](https://goo.gl/PfY1J8)](https://travis-ci.org/Webysther/packagist-mirror)
[![docker Status](https://goo.gl/u9wbBD)](https://github.com/Webysther/packagist-mirror-docker)
[![docker pulls](https://goo.gl/Jb5Cq4)](https://hub.docker.com/r/webysther/packagist-mirror)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.2-8892BF.svg?style=flat-square&maxAge=3600)](https://php.net/)
[![Packagist](https://img.shields.io/packagist/v/webysther/packagist-mirror.svg?style=flat-square)](https://packagist.org/packages/webysther/packagist-mirror)
[![Codecov](https://img.shields.io/codecov/c/github/Webysther/packagist-mirror.svg?style=flat-square)](https://github.com/Webysther/packagist-mirror)
[![Quality Score](https://goo.gl/3LwbA1)](https://scrutinizer-ci.com/g/Webysther/packagist-mirror)
[![Mentioned in Awesome composer](https://awesome.re/mentioned-badge.svg?style=flat-square)](https://github.com/jakoch/awesome-composer#packagist-mirrors)

This is repository make possible to you create a metadata mirror and keep in sync with [packagist.org](packagist.org).

![Mirror creation](/resources/public/logo.svg)

❤️ [Recommended by packagist.org](https://packagist.org/mirrors) ❤️

If you're using [PHP Composer](https://getcomposer.org/), commands like *create-project*, *require*, *update*, *remove* are often used. When those commands are executed, Composer will download information from the packages that are needed also from dependent packages. The number of json files downloaded depends on the complexity of the packages which are going to be used. The further you are from the location of the [packagist.org](packagist.org) server, the more time is needed to download json files. By using mirror, it will help save the time for downloading because the server location is closer.

## ⚙️ How it works?

This project aims to create a local mirror with ease, allowing greater availability for companies/countries that want to use the composer but do not want to depend on the infrastructure of third parties. It is also possible to create a public mirror to reduce the load on the main repository and allow a better distribution of requests around the world, help us creating a public mirror!

When creating your local copy the child mirror (you) adds a list of other mirrors to use when creating your own mirror, if any mirror fails to deliver the metadata file the client automatically fetches the original file from the main mirror, could be packagist.org or even another. If you lose your connection to the server or any other problem that prevents you from continuing is okay, it can return from where it stopped running. After the mirror is created, the next runs will only look for the main mirror delta optimally and efficiently.

![Mirror creation](/resources/public/mirror-creation.gif)

## 🌎 Packagist public metadata mirrors observatory around the world

🛫 Amazing data mirrors used to download repositories metadata built using this [recommended repository](https://packagist.org/mirrors) or another:

| Location        | Mirror      | Maintainer | Github | Sync |
| ------|-----|-----|-----|-----|
|Brazil|[packagist.com.br](https://packagist.com.br)|[Webysther](https://github.com/Webysther)|[main](https://github.com/Webysther/packagist-mirror)|Continuously|
|China|[php.cnpkg.org](https://php.cnpkg.org)|[Eagle Wu](https://github.com/wudi)|[fork](https://github.com/cnpkg/packagist-mirror)|Every minute|
|China|[mirrors.aliyun.com](https://mirrors.aliyun.com/composer)|[Aliyun](https://mirrors.aliyun.com)||Every 5 minutes|
|China|[nas.tbpark.org:5081/phpcomposer](https://nas.tbpark.org:5081/phpcomposer)|[赵丰 (Zhao Feng)](https://github.com/zhaofeng-shu33)|[fork](https://github.com/zhaofeng-shu33/packagist-mirror)|Every 5 minutes|
|China|[packagist.mirrors.sjtug.sjtu.edu.cn](https://packagist.mirrors.sjtug.sjtu.edu.cn)|[Shanghai Jiao Tong University](https://github.com/sjtug)|[fork](https://github.com/sjtug/packagist-mirror)|Every hour|
|China|[mirrors.cloud.tencent.com](https://mirrors.cloud.tencent.com/help/composer.html)|[Tecent Cloud](https://mirrors.cloud.tencent.com)||Every day
|Indonesia|[packagist.phpindonesia.id](https://packagist.phpindonesia.id) |[Indra Gunawan](https://github.com/IndraGunawan)|fork|Every 30 seconds
|Indonesia|[packagist.ianmustafa.com](https://packagist.ianmustafa.com) |[Ian Mustafa](https://github.com/ianmustafa)|[fork](https://github.com/ianmustafa/packagist-mirror)|Every 30 seconds
|Japan|[packagist.jp](https://packagist.jp) |[Hiraku](https://github.com/hirak)|[forked](https://github.com/hirak/packagist-crawler)|Every 2 minutes|
|South Africa|[packagist.co.za](https://packagist.co.za)|[SolidWorx](https://github.com/SolidWorx)|fork|Every 5 minutes|
|South Korea|[packagist.kr](https://packagist.kr)|[PackagistKR](https://github.com/packagistkr)|[fork](https://github.com/packagistkr/packagist-mirror)|Every minute|
|Thailand|[packagist.mycools.in.th](https://packagist.mycools.in.th)|[Jarak Kritkiattisak](https://github.com/mycools)|[fork](https://github.com/mycools/packagist-mirror)|Every 5 minutes|

Not working as a mirror of packagist.org (checked 2019-11):

| Location        | Mirror      | Maintainer | Github | Reason|
| ------|-----|-----|-----|-----|
|China |[mirrors.huaweicloud.com](https://mirrors.huaweicloud.com/repository/php)|[Huawei Cloud](https://mirrors.huaweicloud.com)||+100 packages not found, outdated until 2019-09, there no [2019$10 provider](https://mirrors.huaweicloud.com/repository/php/packages.json), [sample working](https://packagist.com.br/packages.json).|
|China |[packagist.phpcomposer.com](https://pkg.phpcomposer.com)|||No providers inside [packages.json](https://packagist.phpcomposer.com/packages.json) at least 2019-11|
|India |[packagist.in](https://packagist.in) |[Varun Sridharan](https://github.com/varunsridharan)|fork|Temporary server maintenance|
||[packagist-mirror.wmflabs.org](https://packagist-mirror.wmflabs.org) |[Wikimedia](https://www.wikimedia.org/)|fork|+100 packages not found, outdated until 2019-11|

If you know any new mirror based or not on this one, please create a issue or a pull request with the new data.

Check [status page](https://status.packagist.com.br) for health mirror's.

[![World Map](/resources/public/world_map.svg)](https://packagist.com.br/world_map.svg)

The colors represent the topology drawn below, only show servers in better condition at country level.

## 🚀 Create your own mirror

[![Topology](/resources/public/network.svg)](https://packagist.com.br/network.svg)

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

💡Tip: use a machine with 2GB at least of memory, with that all metadata keep to the memory helping the nginx and disk to not be consumed at all.

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

## 👷 Install 

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

## 🐧 Development & Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## 🛣️ Roadmap

- Translate readme.md and index of mirror.
- More recipes to AWS/Azure/GCP/DigitalOcean and another cloud providers.
- Support gz disabled for limited configuration access to Apache/Nginx.
- Support full mirror mode (for countries/companies with limited access to internet).
- Status report for all mirrors around the world.
- Stats after initial mirror creation.

## 📋 Requirements

The following versions of PHP are supported by this version.

* PHP >=7.2

## 🧪 Testing

``` bash
$ vendor/bin/phpunit
```

## 🥂 Credits

- [Webysther Nunes](https://github.com/Webysther)
- [Hiraku NAKANO](https://github.com/hirak)
- [IndraGunawan](https://github.com/IndraGunawan)
- [All Contributors](https://github.com/Webysther/packagist-mirror/contributors)

## 💙 Other correlated projects

- [Zencodex](https://github.com/zencodex/composer-mirror) create a mirror
- [IndraGunawan](https://github.com/IndraGunawan/packagist-mirror) create a mirror (outdated)
- [Hirak](https://github.com/hirak/packagist-crawler) create a mirror (outdated)
- [Slince](https://github.com/slince/composer-registry-manager) Easily switch to the composer repository you want

## ☮️ License

MIT License. Please see [License File](LICENSE) for more information.


[![FOSSA Status](https://app.fossa.io/api/projects/git%2Bgithub.com%2FWebysther%2Fpackagist-mirror.svg?type=large)](https://app.fossa.io/projects/git%2Bgithub.com%2FWebysther%2Fpackagist-mirror?ref=badge_large)
