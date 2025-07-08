<?php

namespace Controller\Energize;

use Energize\Front;
use PhpMx\View;

class Wellcome
{
    function default()
    {
        Front::layout(null);
        return View::render('energize/wellcome');
    }
}
