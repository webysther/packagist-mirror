<?php

use ProgressBar\Manager as ProgressBarManager;


require_once __DIR__ . '/vendor/autoload.php';

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


$j = 0;
$errors = array();
$providerCounter = 1;
$numberOfProviders = count( (array)$packagejson->{'provider-includes'} );

foreach ($packagejson->{'provider-includes'} as $tpl => $provider) {
    $providerjson = str_replace('%hash%', $provider->sha256, $tpl);
    $packages = json_decode(file_get_contents($cachedir.$providerjson));

    $progressBar = new ProgressBarManager(0, count( (array)$packages->providers ));
    $progressBar->setFormat("      - Package: %current%/%max% [%bar%] %percent%%");
    echo "   - Check Provider {$providerCounter}/{$numberOfProviders}:\n";

    foreach ($packages->providers as $tpl2 => $sha) {
        if (!file_exists($file = $cachedir . "p/$tpl2\$$sha->sha256.json")) {
            $errors[] = "   - $tpl\t$tpl2 file not exists\n";
        } elseif ($sha->sha256 !== hash_file('sha256', $file)) {
            unlink($file);
            $errors[] = "   - $tpl\t$tpl2\tsha256 not match: {$sha->sha256}\n";
        } else {
            ++$j;
        }
        $progressBar->advance();
    }

    ++$providerCounter;
}

if (count($errors)) {
    echo "Errors: \n", implode('', $errors);
}

exit(1);
