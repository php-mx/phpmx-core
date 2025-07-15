<?php

namespace Controller\Base;

use PhpMx\Response;
use PhpMx\View;

class Script
{
    function default()
    {
        Response::type('js');
        Response::content(View::render('_front/script'));
        Response::send();
    }
}
