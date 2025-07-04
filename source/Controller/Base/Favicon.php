<?php

namespace Controller\Base;

use PhpMx\Assets;
use PhpMx\File;
use PhpMx\Response;

class Favicon
{
    function default()
    {
        $file = path('storage/assets/favicon.ico');

        if (!File::check($file)) {
            Response::cache(false);
            $file = path('storage/assets/favicon.ico');
        }

        Assets::send($file);
    }
}
