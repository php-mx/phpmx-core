<?php

use PhpMx\Router;

Router::get('assets/...', 'base.assets');
Router::get('download/...', 'base.download');

Router::get('favicon.ico', 'base.favicon');
Router::get('robots.txt', 'base.robots');
Router::get('sitemap.xml', 'base.sitemap');
Router::get('style.css', 'base.style');
Router::get('script.js', 'base.script');

Router::get('captcha', 'base.captcha');

Router::get('', STS_OK);
