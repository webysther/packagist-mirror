<?php
namespace Spindle\HttpClient;

require_once __DIR__ . '/vendor/autoload.php';

if (file_exists(__DIR__ . '/config.php')) {
    $config = require __DIR__ . '/config.php';
} else {
    $config = require __DIR__ . '/config.default.php';
}

if (file_exists($config->olds)) {
    throw new \RuntimeException("$config->olds exists");
}

$globals = new \stdClass;
$globals->q = new \SplQueue;
for ($i=0; $i<$config->maxConnections; ++$i) {
    $req = new Request;
    $req->setOption('encoding', 'gzip');
    $req->setOption('userAgent', 'https://github.com/hirak/packagist-crawler');
    $globals->q->enqueue($req);
}

$globals->mh = new Multi;

do {
    $globals->retry = false;
    $providers = downloadProviders($config, $globals);
    downloadPackages($config, $globals, $providers);
    $globals->retry = checkFiles($config);
} while ($globals->retry);

flushFiles($config);
exit;

/**
 * packages.json & provider-latest$xxx.json downloader
 *
 */
function downloadProviders($config, $globals)
{
    $cachedir = $config->cachedir;

    $packagesCache = $cachedir . 'packages.json';

    $req = new Request($config->packagistUrl . '/packages.json');
    $req->setOptions(array(
        'encoding' => 'gzip',
        'timeCondition' => \CURL_TIMECOND_IFMODSINCE,
    ));
    if (file_exists($packagesCache)) {
        $req->setOption('timeValue', filemtime($packagesCache));
    }

    $res = $req->send();

    if (200 === $res->getStatusCode()) {
        $packages = json_decode($res->getBody());
        foreach (explode(' ', 'notify notify-batch search') as $k) {
            if (0 === strpos($packages->$k, '/')) {
                $packages->$k = 'https://packagist.org' . $packages->$k;
            }
        }
        file_put_contents($packagesCache . '.new', json_encode($packages));
    } else {
        //throw new \RuntimeException('no changes', $res->getStatusCode());
        copy($packagesCache, $packagesCache . '.new');
        $packages = json_decode(file_get_contents($packagesCache));
    }

    if (empty($packages->{'provider-includes'})) {
        throw new \RuntimeException('packages.json schema change?');
    }

    $providers = array();
    $olds = new \SplFileObject($config->olds, 'a');

    foreach ($packages->{'provider-includes'} as $tpl => $version) {
        $fileurl = str_replace('%hash%', $version->sha256, $tpl);
        $cachename = $cachedir . $fileurl;
        $providers[] = $cachename;

        if (file_exists($cachename)) continue;

        $req->setOption('url', $config->packagistUrl . '/' . $fileurl);
        $res = $req->send();

        error_log($res->getStatusCode(). "\t". $res->getUrl());
        if (200 === $res->getStatusCode()) {
            $oldcache = $cachedir . str_replace('%hash%', '*', $tpl);
            if ($glob = glob($oldcache)) {
                foreach ($glob as $old) {
                    //unlink($old);
                    $olds->fwrite($old . \PHP_EOL);
                }
            }
            if (!file_exists(dirname($cachename))) {
                mkdir(dirname($cachename), 0777, true);
            }
            file_put_contents($cachename, $res->getBody());
        } else {
            $globals->retry = true;
        }
    }

    return $providers;
}

/**
 * composer.json downloader
 *
 */
function downloadPackages($config, $globals, $providers)
{
    $cachedir = $config->cachedir;
    $i = 0;
    $urls = array();

    $olds = new \SplFileObject($config->olds, 'a');

    foreach ($providers as $providerjson) {
        $list = json_decode(file_get_contents($providerjson));
        if (!$list || empty($list->providers)) continue;

        $list = $list->providers;

        foreach ($list as $packageName => $provider) {
            $url = "$config->packagistUrl/p/$packageName\$$provider->sha256.json";
            $cachefile = $cachedir . str_replace("$config->packagistUrl/", '', $url);
            if (file_exists($cachefile)) continue;

            $req = $globals->q->dequeue();
            $req->packageName = $packageName;
            $req->setOption('url', $url);
            $globals->mh->attach($req);
            $globals->mh->start(); //non block

            if (count($globals->q)) continue;

            /** @type Request[] $requests */
            do {
                $requests = $globals->mh->getFinishedResponses(); //block
            } while (0 === count($requests));

            foreach ($requests as $req) {
                $res = $req->getResponse();
                $globals->q->enqueue($req);

                if (200 === $res->getStatusCode()) {
                    $cachefile = $cachedir
                        . str_replace("$config->packagistUrl/", '', $res->getUrl());

                    if ($glob = glob("{$cachedir}p/$req->packageName\$*")) {
                        foreach ($glob as $old) {
                            //unlink($old);
                            $olds->fwrite($old . \PHP_EOL);
                        }
                    }
                    if (!file_exists(dirname($cachefile))) {
                        mkdir(dirname($cachefile), 0777, true);
                    }
                    file_put_contents($cachefile, $res->getBody());
                } else {
                    error_log($res->getStatusCode(). "\t". $res->getUrl());
                    $globals->retry = true;
                }
            }
        }
    }


    if (0 === count($globals->mh)) return;
    //残りの端数をダウンロード
    $globals->mh->waitResponse();
    foreach ($globals->mh as $req) {
        $res = $req->getResponse();

        if (200 === $res->getStatusCode()) {
            $cachefile = $cachedir
                . str_replace("$config->packagistUrl/", '', $res->getUrl());
            if ($glob = glob("{$cachedir}p/$req->packageName\$*")) {
                foreach ($glob as $old) {
                    //unlink($old);
                    $olds->fwrite($old . \PHP_EOL);
                }
            }
            if (!file_exists(dirname($cachefile))) {
                mkdir(dirname($cachefile), 0777, true);
            }
            file_put_contents($cachefile, $res->getBody());
        } else {
            error_log($res->getStatusCode(). "\t". $res->getUrl());
            $globals->retry = true;
        }
    }

}

function flushFiles($config)
{
    rename(
        $config->cachedir . 'packages.json.new',
        $config->cachedir . 'packages.json'
    );

    error_log('finished! flushing...');

//    sleep(10); //何秒あれば十分なのか？

    $olds = new \SplFileObject($config->olds, 'r');

    foreach ($olds as $oldfile) {
        $oldfile = rtrim($oldfile);
        if (file_exists($oldfile)) unlink($oldfile);
    }

    unset($olds);
    unlink($config->olds);
}

/**
 * ダウンロードされたファイルが正しいか確認する
 *
 */
function checkFiles($config)
{
    $cachedir = $config->cachedir;

    $packagejson = json_decode(file_get_contents($cachedir.'packages.json.new'));

    $i = $j = 0;
    foreach ($packagejson->{'provider-includes'} as $tpl => $provider) {
        $providerjson = str_replace('%hash%', $provider->sha256, $tpl);
        $packages = json_decode(file_get_contents($cachedir.$providerjson));

        foreach ($packages->providers as $tpl2 => $sha) {
            if (!file_exists($file = $cachedir . "p/$tpl2\$$sha->sha256.json")) {
                ++$i;
            } elseif ($sha->sha256 !== hash_file('sha256', $file)) {
                ++$i;
                unlink($file);
            } else {
                ++$j;
            }
        }
    }

    error_log($i . ' / ' . $i + $j);
    return $i;
}
