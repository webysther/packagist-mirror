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
- ext-zlib
- ext-PDO
- ext-pdo\_sqlite


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
    'maxConnections' => 4,
    'lockfile' => __DIR__ . '/cache/.lock',
    'expiredDb' => __DIR__ . '/cache/.expired.db
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

### expiredDb
ファイル更新によって古くなったjsonが記録されています。

## License

著作権は放棄するものとします。
利用に際して制限はありませんし、作者への連絡や著作権表示なども必要ありません。
スニペット的にコードをコピーして使っても問題ありません。

[ライセンスの原文](LICENSE)

CC0-1.0 (No Rights Reserved)
- https://creativecommons.org/publicdomain/zero/1.0/
- http://sciencecommons.jp/cc0/about (Japanese)

