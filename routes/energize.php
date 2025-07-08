<?php

use Energize\Router;

Router::get('style.css', 'energize.assets:style');
Router::get('script.js', 'energize.assets:script');

Router::middleware(['energize'], function () {
    Router::page('', 'energize.wellcome');
    Router::page('...', STS_NOT_FOUND);
});
