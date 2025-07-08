<?php

namespace Controller\Energize;

use PhpMx\Response;
use PhpMx\View;

class Assets
{
    function style()
    {
        Response::type('css');
        Response::content(View::render('_base/style'));
        Response::send();
    }

    function script()
    {
        Response::type('js');
        Response::content(View::render('_base/script'));
        Response::send();
    }
}
