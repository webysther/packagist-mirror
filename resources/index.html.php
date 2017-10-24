<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Packagist</title>

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
code.more {
background-color: #3d3d5c;
padding: 0.8em;
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
    margin: 0 0 -15px 1em;
    padding: 0.5em;
}

</style>
</head>
<body>
<header>
<h1 class="banner">Packagist
  <span>
     <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/05/Flag_of_Brazil.svg/320px-Flag_of_Brazil.svg.png" alt="Brazil flag" height="75">
  </span>
</h1>
</header>
<?php date_default_timezone_set('America/Sao_Paulo'); ?>
<p align="center">Atualizado em <?= date('d/m/y H\hi') ?> horário de Brasília</p>
<p align="center">(sincronizado a cada 15 segundos)</p>

<br>
<div class="pure-u-1-1" style="margin: 1em;">

<p>
É um espelho do repositório de bibliotecas PHP <a href="https://packagist.org">https://packagist.org</a>. Ao referir-se aqui em vez de packagist.org, o comando <code>composer update</code> é mais rápido caso esteja na América Latina.
</p>

<p>Por favor, digite o seguinte comando para habilitar esse espelho.</p>

</div>


<h3 class="cmd">habilitar</h3>
<p>
<code class="more">$ composer config -g repos.packagist composer https://packagist.com.br</code>
</p>

<h3 class="cmd">desabilitar</h3>
<p>
<code class="more">$ composer config -g --unset repos.packagist</code>
</p>


<div class="pure-u-1-1" style="margin: 1em;">

<p>Para outras informações, utilize o site principal <a href="https://getcomposer.org/">composer</a>, nele é possível encontrar todas as informações relevantes de como utilizar o repositório de bibliotecas.</p>

<h2>Como funciona</h2>

<p>Ao executar <code>composer update</code> é realizado o download de um arquivo JSON que contém as informações do repositório e suas respectivas versões, dessa forma é possível ao composer realizar o download do código correto que foi especificado pelo seu sistema. Dependendo da complexidade do pacote um número maior de pacotes é carregado, aumento o número de vezes que é necessário perguntar ao servidor do packagist por mais arquivos JSON, quando o servidor se encontra em uma distância geográfica grande (RTT) a velocidade para cada conexão de pedido de arquivos é demorado. Com o espelho é possível reduzir esse tempo e também reduzir a carga sobre o servidor principal, aumentando a disponibilidade.</p>

<p>Este site está localizado em São Paulo - Brasil, como o servidor DNS e o CDN utilizado. Ele utiliza o pacote <a href="https://github.com/hirak/packagist-crawler">hirak/packagist-crawler</a> para realizar o download e guardar uma versão mais recente de todos os arquivos JSON. </p>

<p>As requisições aos arquivos do projeto em si são realizadas ao <a href="http://github.com">github</a> ou outro gerenciador de repositórios, observe que isso não ficará mais rápido. Para melhorar o desempenho de carregamento é recomendado utilizar o <a href="https://github.com/hirak/prestissimo"
>prestissimo</a> que permite realizar download paralelo.

<h2>Benchmark</h2>

<h3>Execução de instalação padrão do Laravel</h3>

<code class="more">$ composer create-project --prefer-dist laravel/laravel blog</code>

<p>packagist.org: 4 minutos e 14 segundos</p>
<p>packagist.com.br: 3 minutos e 10 segundos</p>

<code class="more">$ composer update</code>

<p>packagist.org: 1 minutos e 41 segundos</p>
<p>packagist.com.br: 1 minuto e 4 segundos</p>

<h2>Considerações legais</h2>

<p>Observe que esse é um site espelho apenas, mantido por <a href="https://twitter.com/webysther">@webysther</a> para permitir uma melhor disponibilidade principalmente no Brasil.<p>

<p>É esperado que ele suporte um grande tráfego de dados, mas não existem garantias sobre sua disponibilidade ou sequer suporte oficial do time que mantém o packagist.org.</p>

<p>Caso o espelho aparente ficar muito lento ou desatualizado recomendamos reportar para <a href="https://twitter.com/webysther">@webysther</a> e desabilitar temporariamente.</p>

</div>

<address style="text-align:center"><a href="https://creativecommons.org/licenses/by-sa/4.0/deed.pt_BR" target="_blank">CC-BY-SA 4.0 BR</a>, Webysther Nunes</address>
</body>
</html>
