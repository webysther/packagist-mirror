# Packagist 镜像
原始代码查看[fork来源](https://github.com/Webysther/packagist-mirror)，本仓库变动如下
1. 处理了数据压缩，json直接展示明文

## 安装

引用composer

``` bash
$ composer require jimcy/packagist-mirror-cn
```

安排命令以创建和更新镜像：

```bash
$ php bin/mirror create --no-progress
```

## 要求

此版本支持以下版本的PHP。

* PHP >=7.1

## 测试

``` bash
$ vendor/bin/phpunit
```