<!DOCTYPE html>
<html lang="ja">
<head>
 <meta charset="UTF-8">
 <meta http-equiv="X-UA-Compatible" content="IE=edge">
 <meta name="viewport" content="initial-scale=1.0">
<style>
html, button, input, select, textarea {
font-family:'Lucida Grande','Hiragino Kaku Gothic ProN',Meiryo,sans-serif;
-webkit-text-size-adjust: 100%;
text-size-adjust: 100%;
}

code {
font-family: Consolas,"Liberation Mono",Courier,monospace;
font-size: 14px;
line-height: 1.3;
word-wrap: break-word;
}
 </style>
 <title>packagist.JP</title>
</head>
<body>

<h1>Packagist<span style="font-size:42px">.JP</span></h1>

<p>
<a href="https://packagist.org">https://packagist.org</a>に配置されたパッケージメタファイルの内容を毎日ミラーしています。アジア圏では.orgを参照するより、.jpミラーを参照したほうが<a href="https://getcomposer.org">composer</a>が高速になります。</p>

<p>最終更新： <?= date('c') ?>

<h2>enable</h2>

<code>
$ composer config -g repositories.packagist composer <?= $url ?>
</code>


<h2>disable</h2>
<code>
$ composer config -g --unset repositories.packagist
</code>

<address>
<ul>
    <li><a href="https://github.com/hirak/packagist-crawler">fork me on GitHub</a></li>
    <li><a href="https://twitter.com/Hiraku">@Hiraku</a></li>
</ul>
</address>
</body>
</html>

