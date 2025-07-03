<?php

namespace Controller\Base;

use PhpMx\Assets as MxAssets;
use PhpMx\File;
use PhpMx\Request;
use PhpMx\Response;
use PhpMx\View;

class Assets
{
    function default()
    {
        $file = path('storage/assets', ...Request::route());

        if (!File::check($file))
            $file = path(CORE_PATH, $file);

        MxAssets::send($file);
    }

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
