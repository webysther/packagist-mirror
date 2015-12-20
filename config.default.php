<?php
/**
 * 何か変更したい場合は、このファイルをコピーしてconfig.phpを作り、そちらで編集すること。
 */

return (object)array(
    'cachedir' => __DIR__ . '/cache/',
    //'cachedir' => '/usr/share/nginx/html/',
    //'cachedir' => '/usr/local/apache2/htdocs/',
    'packagistUrl' => 'https://packagist.org',
    'lockfile' => __DIR__ . '/cache/.lock',
    'expiredDb' => __DIR__ . '/cache/.expired.db',
    'maxConnections' => 2,
    'generateGz' => true,
    'expireMinutes' => 24 * 60,
    'url' => 'http://localhost',
);
