# packagist local mirror

## 快速开始

```sh
# rpm data folder
data_path="${HOME}/data/packagist-mirror"

# url or ip 
SERVER_URL='http://packagist.example.com'

# user url port, default 80
# EXTERNAL_PORT='8080' # if not use 80

docker run -v ${data_path}:/repo/public --name packagist-mirror -p 80:8080  -e SERVER_URL=${SERVER_URL} -e EXTERNAL_PORT=${EXTERNAL_PORT} -d klzsysy/packagist-mirror

open ${SERVER_URL}
```

## 特点

在原基础上:

- 封装nginx作http服务，默认端口8080
- 更换国内源
- 添加定时运行
- 修改挂载路径
- 修改权限，以便在无root环境运行
- 兼容openshift无特权运行
- **缓存包的zip文件，而不是只有一个index**，通过代理下载用户请求的url，并在用户第二次请求同一个文件时得到缓存

快速一键部署本地 packagist mirror (*^▽^*)

## 额外变量

- `WEEK_SYNC_TIME` 每周同步的时间 `1-7` 1是周一， 例如 `1 2 3` 为周一到周三，默认每天`all`
- `SYNC_INTERVAL` 同步的间隔, 单位为分钟， 默认`30`，每30分钟同步一次（需要优先满足`WEEK_SYNC_TIME`条件）
- `SERVER_URL` 服务器的有效URL, 生成缓存zip文件时使用，默认`http://localhost`
- `EXTERNAL_PORT` 外部服务端口，即最终用户访问服务器的端口，默认80， 不是容器端口，容器默认端口8080
- `MAIN_MIRROR` ,上游地址，默认国内源（官方源网络很容易失败）
