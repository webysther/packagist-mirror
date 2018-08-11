<?php
/**
 * User: mr.jin
 * Date: 18/8/11
 * Time: 下午9:16
 * Description: 用于处理packagist UserName 和 Github UserName 不同造成数据拉取不到的问题
 */

if (strlen($_SERVER['REQUEST_URI']) < 10) {
    header('HTTP/1.0 404 Not Found');
    return;
}

$uri = explode('/', substr($_SERVER['REQUEST_URI'], 4, strlen($_SERVER['REQUEST_URI'])));
if (count($uri) != 3) {
    header('HTTP/1.0 404 Not Found');
}
$package = $uri[0] . "/" . $uri[1];
$end = explode('.', $uri[2]);
$hash = $end[0];
$type = $end[1];

$url = "https://packagist.org/packages/" . $package . ".json";

$data = file_get_contents($url);

if (strlen($data) < 15) {
    header('HTTP/1.0 404 Not Found');
    return;
}

$packagesArr = json_decode($data, true);
$repository = $packagesArr['package']['repository'];
$repositoryUri = substr($repository, 19, strlen($repository) - 1);

$dataUrl = "https://dl.composer.jinfeijie.cn/$repositoryUri/legacy.$type/$hash";

Header("HTTP/1.1 301 Moved Permanently");
Header("Location: $dataUrl");