<?php

namespace Controller\Base;

use PhpMx\Response;

class Script
{
    function default()
    {
        Response::type('js');
        Response::content(view('_front/script'));
        Response::send();
    }
}
