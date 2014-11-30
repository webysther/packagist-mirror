<?php
/**
 * ダウンロードに失敗したファイルを一覧で出す
 */
if (file_exists(__DIR__ . '/config.php')) {
    $config = require __DIR__ . '/config.php';
} else {
    $config = require __DIR__ . '/config.default.php';
}

cli_set_process_title('fofofofofofo');

$cachedir = $config->cachedir;

$packagejson = json_decode(file_get_contents($cachedir.'packages.json'));

$i = $j = 0;
foreach ($packagejson->{'provider-includes'} as $tpl => $provider) {
    $providerjson = str_replace('%hash%', $provider->sha256, $tpl);
    $packages = json_decode(file_get_contents($cachedir.$providerjson));

    foreach ($packages->providers as $tpl2 => $sha) {
        if (!file_exists($file = $cachedir . "p/$tpl2\$$sha->sha256.json")) {
            ++$i;
            echo "$tpl\t$tpl2 file not exists\n";
        } elseif ($sha->sha256 !== hash_file('sha256', $file)) {
            ++$i;
            unlink($file);
            echo "$tpl\t$tpl2\tsha256 not match: {$sha->sha256}\n";
        } else {
            ++$j;
        }
    }
}
echo $i, ' / ', $i+$j,  PHP_EOL;

exit($i);
