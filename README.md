packagist-crawler
========================

packagist.orgをクロールして、全てのpackage.jsonをダウンロードします。
ダウンロードし終わったあとでstaticなweb serverで配信すれば、packagist.orgのミラーを作ることができます。

Requirement
------------------
- PHP > 5.3
- ext-curl
- ext-hash
- ext-json


Install
------------------

```sh
$ git clone https://github.com/hirak/packagist-crawler
$ cd packagist-crawler
$ composer install
```

Download!
------------------

```sh
$ php parallel.php

(...few minutes...)

$ ls cache/
p/
packages.json
```


Configuration
------------------

- config.default.php
- config.php

このどちらかのファイルがあると、挙動を変えることができます。
修正したいときはconfig.default.phpをconfig.phpにコピーして、
config.phpの方をカスタマイズしてください。

```php
<?php
return (object)array(
    'cachedir' => __DIR__ . '/cache/',
    //'cachedir' => '/usr/share/nginx/html/',
    //'cachedir' => '/usr/local/apache2/htdocs/',
    'packagistUrl' => 'https://packagist.org',
    //'packagistUrl' => 'http://composer-proxy.jp/proxy/packagist',
    'olds' => __DIR__ . '/cache/.olds',
    'maxConnections' => 4,
);
```

### cachedir
ダウンロードしたpackages.jsonを格納するディレクトリです。

### packagistUrl
ダウンロード元のpackagist.orgのURLです。
デフォルトではオリジンからダウンロードしますが、
既に存在する他のミラーサイトを指定することができます。

### maxConnections
並列ダウンロードの並列数です。
増やした方が速くダウンロードできますが、
オリジンに負荷をかけるので適当なところにしてください。

