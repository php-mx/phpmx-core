<?php

use PhpMx\Router;

Router::get('api', 'phpMx.status');

Router::get('favicon.ico', 'phpMx.favicon');
Router::get('robots.txt', 'phpMx.robots');
Router::get('sitemap.xml', 'phpMx.sitemap');

Router::get('captcha', 'phpMx.captcha');

Router::get('assets/...', 'phpMx.assets');
Router::get('download/...', 'phpMx.download');

Router::get('style.css', 'phpMx.assets:style');
Router::get('script.js', 'phpMx.assets:script');
