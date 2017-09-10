Packagist mirror
========================

Crawl packagist.org and download all package.json. After downloading, you can distribute it with a static web server, you can create a mirror of packagist.org.

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

Configuration
------------------

- config.default.php
- config.php

With either of these files, you can change the behavior.
If you want to fix it, copy config.default.php to config.php,
Please customize the one of config.php.

```php
<?php
return (object)array(
    // This is the directory that stores downloaded packages.json.
    'cachedir' => __DIR__ . '/cache/',
    //'cachedir' => '/usr/share/nginx/html/', # nginx
    //'cachedir' => '/usr/local/apache2/htdocs/', # apache
    
    // This is the URL of the download packagist.org. You can specify another mirror site that already exists.
    'packagistUrl' => 'https://packagist.org',
    
    // Parallel number of parallel downloads. Since it places a load on the origin, please make it a suitable place.
    'maxConnections' => 4,
    
    // Lock execution, if this file exists parallel.php dont execute
    'lockfile' => __DIR__ . '/cache/.lock',
    
    // The old json is recorded by the file update.
    'expiredDb' => __DIR__ . '/cache/.expired.db
);
```

Download
------------------

```sh
$ php parallel.php

(...few minutes...)

$ ls cache/
p/
packages.json
```

