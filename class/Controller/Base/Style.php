<?php

namespace Controller\Base;

use PhpMx\Response;
use PhpMx\View;

class Style
{
    function __invoke()
    {
        Response::type('css');
        Response::content(View::render('_front/style'));
        Response::send();
    }
}
