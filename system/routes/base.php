<?php

use PhpMx\Router;

Router::get('assets/...', \Controller\Base\Assets::class);
Router::get('download/...', \Controller\Base\Download::class);

Router::get('favicon.ico', \Controller\Base\Favicon::class);
Router::get('robots.txt', \Controller\Base\Robots::class);
Router::get('sitemap.xml', \Controller\Base\Sitemap::class);
Router::get('style.css', \Controller\Base\Style::class);
Router::get('script.js', \Controller\Base\Script::class);

Router::get('captcha', \Controller\Base\Captcha::class);

Router::get('', STS_OK);
