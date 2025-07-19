<?php

namespace Controller;

use PhpMx\Router;

Router::get('assets/...', Base\Assets::class);
Router::get('download/...', Base\Download::class);

Router::get('favicon.ico', Base\Favicon::class);
Router::get('robots.txt', Base\Robots::class);
Router::get('sitemap.xml', Base\Sitemap::class);
Router::get('style.css', Base\Style::class);
Router::get('script.js', Base\Script::class);

Router::get('captcha', Base\Captcha::class);

Router::get('', STS_OK);
