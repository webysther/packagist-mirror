#!/usr/bin/env python3
# __author__ klzsysy

from flask import Flask, send_file, make_response, redirect
import requests
import logging
import os
import shutil
import threading


class Share(object):
    def __init__(self):
        self.PROXY_URL_PREFIX = os.getenv('PROXY_URL_PREFIX', 'zipcache')
        self.DOWNLOAD_PREFIX = 'public/' + os.getenv('DOWNLOAD_PREFIX', self.PROXY_URL_PREFIX)
        self.UPSTREAM_URL = os.getenv('UPSTREAM_URL', 'https://dl.laravel-china.org')
        self.header = {"User-Agent": os.getenv("USER_AGENT", "Composer/1.6.5 (Darwin; 17.7.0; PHP 7.1.16)")}


class Logging(object):
    def __init__(self, level=logging.DEBUG, name='app'):
        self.level = level
        self.logger = logging.getLogger(name)
        self.logger.setLevel(self.level)

    def get_logger(self):
        ch = logging.StreamHandler()
        fmt = logging.Formatter('%(asctime)s - %(name)s - %(levelname)s - %(message)s')
        ch.setLevel(self.level)
        ch.setFormatter(fmt)
        self.logger.addHandler(ch)
        return self.logger


S = Share()
Log = Logging()
logger = Log.get_logger()
app = Flask(__name__)


def download(origin, folder, file):
    try:
        r = requests.get(origin, stream=True, headers=S.header)
        if r.status_code == 200:
            if not os.path.isdir(folder):
                try:
                    os.makedirs(folder)
                    logger.debug("create %s" % folder)
                except BaseException as err:
                    logger.error('create %s failure!!\n%s' % (folder, str(err)))
                    return "", 500
            with open(file, 'wb') as f:
                r.raw.decode_content = True
                shutil.copyfileobj(r.raw, f)
        else:
            return "", r.status_code

    except Exception as err:
        return str(err), 500
    else:
        logger.debug('Cache %s Succeeded ' % file)
        return "Cache Succeeded", 201


@app.route('/%s/<path:url>' % S.PROXY_URL_PREFIX)
def proxy(url):
    # headers = dict(request.headers)
    origin_download_url = S.UPSTREAM_URL + '/' + url
    local_file_path = os.path.join(S.DOWNLOAD_PREFIX, url)
    local_file_dir = os.path.dirname(local_file_path)
    local_file_name = os.path.basename(local_file_path)

    if os.path.isfile(local_file_path):
        response = make_response(send_file(local_file_path))
        response.headers["Content-Disposition"] = "attachment; filename={};".format(local_file_name)
        return response

    dl = threading.Thread(target=download, args=(origin_download_url, local_file_dir, local_file_path))
    dl.start()

    return redirect(origin_download_url, code=302)


def main():
    logger.debug('debug start')
    # app.debug = True
    app.run(host='127.0.0.1', port=8000)


if __name__ == '__main__':
    main()
