# 📦 Packagist Mirror

[![Build Status](https://goo.gl/PfY1J8)](https://travis-ci.org/Webysther/packagist-mirror)
[![docker Status](https://goo.gl/u9wbBD)](https://github.com/Webysther/packagist-mirror-docker)
[![docker pulls](https://goo.gl/Jb5Cq4)](https://hub.docker.com/r/webysther/packagist-mirror)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.2-8892BF.svg?style=flat-square&maxAge=3600)](https://php.net/)
[![Packagist](https://img.shields.io/packagist/v/webysther/packagist-mirror.svg?style=flat-square)](https://packagist.org/packages/webysther/packagist-mirror)
[![Codecov](https://img.shields.io/codecov/c/github/Webysther/packagist-mirror.svg?style=flat-square)](https://github.com/Webysther/packagist-mirror)
[![Quality Score](https://goo.gl/3LwbA1)](https://scrutinizer-ci.com/g/Webysther/packagist-mirror)
[![Mentioned in Awesome composer](https://awesome.re/mentioned-badge.svg?style=flat-square)](https://github.com/jakoch/awesome-composer#packagist-mirrors)

❤️ [Recommended by packagist.org](https://packagist.org/mirrors) ❤️

## Announcement: [Composer 2 is now available!](https://blog.packagist.com/composer-2-0-is-now-available/)

**This mirror is for Composer 1; Composer 2 is very fast on its own. We will update to support the version 2 for those need solve the slow internet access or availability problem with the main repository.**

A mirror for [packagist.org](packagist.org) that regularly caches packages from one or more main mirrors to add to a distributed package repository.

![Mirror creation](/resources/public/logo.svg)

If you're using [PHP Composer](https://getcomposer.org/), commands like *create-project*, *require*, *update*, *remove* are often used. When those commands are executed, Composer will download information from the packages that are needed also from dependent packages. The number of json files downloaded depends on the complexity of the packages which are going to be used. The further you are from the location of the [packagist.org](packagist.org) server, the more time is needed to download json files. By using a mirror, it will save you time when downloading json because the server location is closer.

## ⚙️ How it works?

This project aims to create a local mirror with ease, allowing greater availability for companies/countries that want to use composer without depending on the infrastructure of third parties. It is also possible to create a public mirror to reduce the load on the main repository and better distribute requests around the world, helping make the packagist ecosystem faster as a whole!

When creating a mirror, you add a list of other mirrors to use for initial sync, which pulls all packages to your local machine.   After the mirror is created and synced, the next runs will only pull updates.  If any mirror fails to deliver a metadata file, the client will fallback to its configured main mirror, whether that be packagist.org or otherwise. If the client encounters an installation problem or loses connection to a mirror, it can return from where it stopped running.

![Mirror creation](/resources/public/mirror-creation.gif)

## 🌎 Packagist public metadata mirrors observatory around the world

🛫 Amazing data mirrors used to download repositories metadata built using this [recommended repository](https://packagist.org/mirrors) or another:

> Lists are ordered by country and sync frequency.

| Location        | Mirror      | Maintainer | Github | Sync | Since |
| ------|-----|-----|-----|-----|-----|
|Brazil|[packagist.com.br](https://packagist.com.br)|[Webysther](https://github.com/Webysther)|[main](https://github.com/Webysther/packagist-mirror)|Continuously|[Q3'17](https://github.com/Webysther/packagist-mirror/commits/master?after=7230e201b4542b7db33e2b19517352653f751759+419)
|China|[php.cnpkg.org](https://php.cnpkg.org)|[Eagle Wu](https://github.com/wudi)|[fork](https://github.com/cnpkg/packagist-mirror)|Every minute|[Q3'18](https://github.com/cnpkg/packagist-mirror/commits/master)
|China|[packagist.mirrors.sjtug.sjtu.edu.cn](https://packagist.mirrors.sjtug.sjtu.edu.cn)|[Shanghai Jiao Tong University](https://github.com/sjtug)|[fork](https://github.com/sjtug/packagist-mirror)|Every hour|[Q2'19](https://github.com/sjtug/packagist-mirror/commits/master)
|Czech Republic|[packagist.hostuj.to](https://packagist.hostuj.to)|[HOSTUJ TO](https://hostuj.to)|fork|Every 5 minutes|🆕Q1'20
|Finland|[packagist.fi](https://packagist.fi)|[Niko Granö](https://xn--gran-8qa.fi)|fork|Continuously|🆕Q2'20
|France|[packagist.fr](https://packagist.fr)|[Baptiste Pillot](https://github.com/baptistepillot)|[fork](https://github.com/bappli/packagist-mirror)|Every minute|🆕[Q4'20](https://github.com/bappli/packagist-mirror/commits/master)
|Germany|[packagist.hesse.im](https://packagist.hesse.im)|[Benjamin Hesse](https://hesse.im)|[fork](https://github.com/42656e/packagist-mirror)|Every minute|🆕Q3'20
|Germany|[composer.mg100.net](https://composer.mg100.net)|[Alex Gummenscheimer](https://github.com/MG-100)|[fork](https://github.com/MG-100/packagist-mirror)|Every minute|🆕Q1'21
|India |[packagist.in](https://packagist.in) |[Varun Sridharan](https://github.com/varunsridharan)|fork|Every minute|[Q2'19](https://www.registry.in/whois)
|India|[packagist.vrkansagara.in](https://packagist.vrkansagara.in/packages.json)|[Vallabh Kansagara](https://github.com/vrkansagara)|[fork](https://github.com/vrkansagara/packagist-mirror)|Every 5 minutes|[Q4'19](https://packagist.vrkansagara.in/packages.json)|
|Indonesia|[packagist.phpindonesia.id](https://packagist.phpindonesia.id) |[Indra Gunawan](https://github.com/IndraGunawan)|fork|Every 30 seconds|[Q3'18](https://github.com/IndraGunawan/packagist-mirror/commits/master)
|Indonesia|[packagist.ianmustafa.com](https://packagist.ianmustafa.com) |[Ian Mustafa](https://github.com/ianmustafa)|[fork](https://github.com/ianmustafa/packagist-mirror)|Every 30 seconds|[Q3'19](https://github.com/ianmustafa/packagist-mirror/commits/master)
|Indonesia|[packagist.telkomuniversity.ac.id](http://packagist.telkomuniversity.ac.id) |[Telkom University](https://mirror.telkomuniversity.ac.id)|fork|Every 5 minutes|🆕Q1'20
|Japan|[packagist.dev.studio-umi.jp](https://packagist.dev.studio-umi.jp/) |[Studio Umi](https://github.com/studioumi)|fork|Every minute|🆕Q1'20
|Russia|[packagist.org.ru](https://packagist.org.ru) |[Konstantin Tarasov](https://github.com/ktarasov/)|fork|Every 15 minutes|Q3'22
|South Africa|[packagist.co.za](https://packagist.co.za)|[SolidWorx](https://github.com/SolidWorx)|fork|Every 5 minutes|[Q3'18](http://co.za/cgi-bin/whois.sh?Domain=packagist.co.za&Enter=Enter)
|South Korea|[packagist.kr](https://packagist.kr)|[PackagistKR](https://github.com/packagistkr)|[fork](https://github.com/packagistkr/packagist-mirror)|Every minute|[Q3'18](https://github.com/packagistkr/packagist-mirror/issues)
|Thailand|[packagist.mycools.in.th](https://packagist.mycools.in.th)|[Jarak Kritkiattisak](https://github.com/mycools)|[fork](https://github.com/mycools/packagist-mirror)|Every 5 minutes|[Q4'19](https://github.com/mycools/packagist-mirror/commits/master)
|USA|[packagist-mirror.wmcloud.org](https://packagist-mirror.wmcloud.org) |[Wikimedia](https://www.wikimedia.org/)|fork|Every 5 minutes|[Q3'18](https://phabricator.wikimedia.org/T203529)
|Taiwan|[packagist.tw](https://packagist.tw) |[Peter](https://github.com/peter279k)|[fork](https://github.com/open-source-contributions/packagist-mirror)|Every 5 minutes|🆕Q2'20
|Vietnam|[packagist.ondinh.net](https://packagist.ondinh.net/) |[Long Nguyen](https://github.com/olragon)|[main](https://github.com/Webysther/packagist-mirror)|Every 5 minutes|🆕Q3'20

⚠️ Not based on this [source code](https://github.com/Webysther/packagist-mirror):

| Location        | Mirror      | Maintainer | Github | Sync | Since |
| ------|-----|-----|-----|-----|-----|
|China|[mirrors.aliyun.com](https://mirrors.aliyun.com/composer)|[Aliyun](https://mirrors.aliyun.com)||Every 5 minutes|
|China|[mirrors.cloud.tencent.com](https://mirrors.cloud.tencent.com/help/composer.html)|[Tecent Cloud](https://mirrors.cloud.tencent.com)||Every day
|Japan|[packagist.jp](https://packagist.jp) |[Hiraku](https://github.com/hirak)|[forked](https://github.com/hirak/packagist-crawler)|Every 2 minutes|[Q4'14](https://github.com/hirak/packagist-crawler/graphs/contributors)
|Japan|[packagist.kawax.biz](https://packagist.kawax.biz) |[Kawax](https://github.com/kawax)|[another](https://github.com/kawax/packagist-bot)|Every hour|[Q4'18](https://github.com/kawax/packagist-bot/graphs/contributors)

🛑 Not working as a mirror of packagist.org (checked at Q1'20):

| Location        | Mirror      | Maintainer | Github | Reason| At least |
| ------|-----|-----|-----|-----|-----|
|China |[mirrors.huaweicloud.com](https://mirrors.huaweicloud.com/repository/php)|[Huawei Cloud](https://mirrors.huaweicloud.com)||Outdated|[Q3'19](https://mirrors.huaweicloud.com/repository/php/packages.json)
|China |[packagist.phpcomposer.com](https://pkg.phpcomposer.com)|||Outdated|[Q4'19](https://packagist.phpcomposer.com/packages.json)


If you know any new mirror based or not on this one, please create a issue or a pull request with the new data.

Check [status page](https://status.packagist.com.br) for health mirror's.

[![World Map](/resources/public/world_map.svg)](https://packagist.com.br/world_map.svg)

This map shows working mirrors from above at the country level. The colors represent the topology drawn below.

## 🚀 Create your own mirror

[![Topology](/resources/public/network.svg)](https://packagist.com.br/network.svg)

> 💡Tip: use a machine with at least 2GB of RAM to avoid using the disk or swap space during sync.

> ⚠️ When syncing from `DATA_MIRROR` or `MAIN_MIRROR`, your server encodes and decodes all packages as `.gz` files to save disk space.  You may need to enable server-side decoding for legacy composer clients that ask for decompressed packages.

There are currently three supported methods for creating your own mirror.

- [Docker Compose](#docker-compose) - Fully automated solution using Docker Compose.
- [Docker + Nginx + PHP](#docker-nginx-php) - Docker for cron jobs, Nginx + PHP on the host.
- [Nginx + PHP](#nginx-php) - Cron + Nginx + PHP all running on the host.

In all three methods, you need to clone the repository and copy `.env.example` to `.env` and modify to include your values instead of the defaults.

```bash
# Clone this repository
$ git clone https://github.com/websyther/packagist-mirror.git

# Setup environment variables
$ cd packagist-mirror
$ cp .env.example .env
$ nano .env
```

### Docker Compose

Run the following commands to start a container for Nginx, PHP-FPM, and a worker that runs cron jobs.

```bash
# Start all Docker containers
$ docker-compose up -d

# Follow log output
$ docker-compose logs -f
```

Once the initial sync has finished, open https://localhost:9248 to see your site.

> 💡Tip: Add `-f docker-compose.prod.yml` between `docker-compose` and `up` or `down` while running the above commands.  If you are using [traefik](https://traefik.io), the services in this docker-compose file contain labels used by a running traefik container to automatically route traffic matching those labels to that container.  It even auto-renews LetsEncrypt certificates for you.

### Docker Nginx PHP

First, add the following line to `/etc/crontab` to tell the host to start a container for the `packagist-mirror` image on boot, replacing the values for each `-e` flag with your own.  This will start the initial sync and generate the website files to be served by nginx.

> Learn about more the available options for this docker image [here](https://github.com/Webysther/packagist-mirror-docker).

```bash
* * * * * root docker run --name mirror --rm -v /var/www:/public \
-e MAINTAINER_REPO='packagist.com.br' \
-e APP_COUNTRY_NAME='Brazil' \
-e APP_COUNTRY_CODE='br' \
-e MAINTAINER_MIRROR='Webysther' \
-e MAINTAINER_PROFILE='https://github.com/Webysther' \
-e MAINTAINER_REPO='https://github.com/Webysther/packagist-mirror' \
-e URL='packagist.com.br' \
webysther/packagist-mirror
```

Next, add the following to `/etc/nginx/sites-available/packagist.com.br.conf` to host the website files:

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

To monitor sync progress, run the following command:

```bash
docker logs --follow --timestamps --tail 10 mirror
```

## Nginx PHP

After cloning the repository, run the following commands to configure for your host.

``` bash
$ cd packagist-mirror && composer install
$ cp .env.example .env
```

Then, schedule the command to create and update the mirror:

```bash
$ php bin/mirror create -vvv
```

Nginx will now serve your mirror at the configured URL.

## 🐧 Development & Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## 🛣️ Roadmap

#### 2020
- Support for Drupal metadata
- Translate readme.md and index of mirror.
- Fully IaC with terraform.
- More recipes to AWS/Azure/GCP/DigitalOcean and another cloud providers.
- Support for Heroku.
- Support for kubernetes.

#### 2021
- Support gz disabled for limited configuration access to Apache/Nginx.
- Support full mirror mode: Github/Gitlab.
- Integration with [twity](https://github.com/julienj/twity).
- Integration with [composer-registry-manager](https://github.com/slince/composer-registry-manager).

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

- [composer/mirror](https://github.com/composer/mirror) The official composer mirrorring script (used for official packagist.org mirrors)
- [composer-mirror](https://github.com/zencodex/composer-mirror) Create a mirror (open sourced code but not maintained)
- [composer-registry-manager](https://github.com/slince/composer-registry-manager) Easily switch to the composer repository you want
- [packagist-bot](https://github.com/kawax/packagist-bot) Yet Another Packagist Mirror
- [twity](https://github.com/julienj/twity) Provide a web based management of private and public composer packages.
- [velocita-proxy](https://github.com/isaaceindhoven/velocita-proxy) Composer caching reverse proxy

## ☮️ License

MIT License. Please see [License File](LICENSE) for more information.


[![FOSSA Status](https://app.fossa.io/api/projects/git%2Bgithub.com%2FWebysther%2Fpackagist-mirror.svg?type=large)](https://app.fossa.io/projects/git%2Bgithub.com%2FWebysther%2Fpackagist-mirror?ref=badge_large)
