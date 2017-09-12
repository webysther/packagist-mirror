<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Packagist.JP</title>

<style>
html, button, input, select, textarea, div {
font-family:'Lucida Grande','Hiragino Kaku Gothic ProN',Meiryo,sans-serif !important;
-webkit-text-size-adjust: 100%;
text-size-adjust: 100%;
}

code {
font-family: Consolas,"Liberation Mono",Courier,monospace !important;
font-size: 16px;
line-height: 1.3;
word-wrap: break-word;
}
pre {
background-color: #3d3d5c;
padding: 0.5em;
color: white;
}
h1 {
margin:0;
    padding:0;
}
.banner {
    font-size: 300%;
    text-align: center;
}
@media screen and (min-width : 768px){
    .banner{ font-size : 500%;} 
}
 
@media screen and (min-width : 1024px) {
    .banner{ font-size : 700%;} 
}
h3.cmd {
    width: 10em;
    background-color: #B88A7A;
    margin: 0 0 -25px 1em;
    padding: 0.5em;
}

</style>
</head>
<body>
<header>
<h1 class="banner">Packagist<span style="color:red">●</span>JP</h1>
<p align="center">最終同期： <?= date('Y年n月j日 H:i:s') ?> (JST) (2分毎に同期)</p>
</header>


<div class="pure-u-1-1" style="margin: 1em;">
<p>PHPのライブラリリポジトリである<a href="https://packagist.org">https://packagist.org</a>のミラーサイトです。packagist.orgの代わりにこちらを参照することで、<code>composer update</code>の応答速度が速くなります。特にフランスから遠い、アジア圏では顕著な効果が得られます。</p>
<p>有効にするには以下のコマンドを打ち込んでください。</p>
</div>


<h3 class="cmd">enable</h3>
<pre><code>$ composer config -g repos.packagist composer <?= $url ?></code></pre>

<h3 class="cmd">disable</h3>
<pre><code>$ composer config -g --unset repos.packagist</code></pre>


<div class="pure-u-1-1" style="margin: 1em;">

<p>なお、このサイトでは<a href="https://getcomposer.org/">composer自体</a>やpackagist.orgにあるパッケージ情報ページ、検索機能などはミラーしておりません。それぞれ本家サイトをご利用ください。</p>

<h2>仕組み</h2>

<p>composer updateを実行すると、composerはpackagist.orgからパッケージ情報が書かれたJSONファイルをダウンロードし、必要なパッケージやそれに依存するパッケージのJSONファイルを個別にダウンロードしていきます。パッケージの複雑さにもよりますが、update時にダウンロードするJSONファイルは数十から数百に達します。composerは現状全ファイルに対してTLSのコネクション確立からやり直すので、packagist.orgとcomposerを実行しているクライアントとの物理的な距離(RTT)が大きく影響します。</p>

<p>本サイトは日本のさくらVPSを使って配信しています。<a href="https://github.com/hirak/packagist-crawler">hirak/packagist-crawler</a>というスクリプトを使って、あらかじめpackagist.orgをクロールし、同期時点でのパッケージの情報が書かれた全JSONファイルをダウンロードしてあります。</p>

<p>配信は普通のnginxを使い、高負荷時の対策として手前にCDN(CloudFlare)を置いてあります。単にそれだけのサイトです。</p>

<p>このため、ミラーサイトを使った場合に高速化するのは<code>composer update</code>, <code>composer require</code>, <code>composer remove</code>などメタファイルのやり取りが発生する場合だけになります。</p>

<p>Travis-CIなどで<code>composer install</code>する際は、github.comなどとのやり取りになっており、ミラーを有効にしたところで全く高速化されません。</p>


<h2>免責事項</h2>

<p>このサイトは <a href="https://twitter.com/Hiraku">@Hiraku</a> が個人的に運営しています。スペック的には今の数万倍のアクセスが来ようが余裕で捌けますので自由に使っていただいて構いません。利用に際して料金等はかかりませんが、個人運営ですので、障害が起きても何ら保障は致しかねます。その点だけご了承ください。</p>

<p>packagist.jpはただのミラーサイトで、JSONの加工は行っていないので、何か不具合があれば上記disableコマンドで設定を外し、本家packagist.orgを参照するようにしてみてください。</p>

<p>使い方の疑問や要望など、答えられる範囲では答えますので、お尋ねください。</p>

</div>

<address style="text-align:center">Copyright (C) 2014, Hiraku (hiraku at tojiru.net)</address>
</body>
</html>
