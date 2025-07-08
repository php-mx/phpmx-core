<?php

use PhpMx\Router;


Router::get('favicon.ico', 'base.favicon');
Router::get('robots.txt', 'base.robots');
Router::get('sitemap.xml', 'base.sitemap');

Router::get('captcha', 'base.captcha');

Router::get('assets/...', 'base.assets');
Router::get('download/...', 'base.download');

Router::get('style.css', 'energize.assets:style');
Router::get('script.js', 'energize.assets:script');

Router::middleware(['energize'], function () {
    Router::page('', 'energize.wellcome');
    Router::page('...', STS_NOT_FOUND);
});
