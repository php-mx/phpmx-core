<?php

use PhpMx\Router;

Router::get('assets/...', \Controller\Mx\Assets::class);
Router::get('download/...', \Controller\Mx\Download::class);

Router::get('favicon.ico', \Controller\Mx\Favicon::class);
Router::get('robots.txt', \Controller\Mx\Robots::class);
Router::get('sitemap.xml', \Controller\Mx\Sitemap::class);

Router::get('captcha', \Controller\Mx\Captcha::class);

Router::get('', STS_OK);
