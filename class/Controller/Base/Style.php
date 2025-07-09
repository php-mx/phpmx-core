<?php

namespace Controller\Base;

use PhpMx\Response;

class Style
{
    function default()
    {
        Response::type('css');
        Response::content(view('_front/style'));
        Response::send();
    }
}
