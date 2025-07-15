<?php

namespace Controller\Base;

use PhpMx\Page;
use PhpMx\View;
use Throwable;

class Error
{
    static function handlePageThrowable(Throwable $e)
    {
        $status = $e->getCode();
        $message = env("STM_$status") ?? 'Erro desconhecido';

        Page::title($message);
        Page::layout(null);

        View::render('_front/error', ['status' => $status, 'message' => $message]);
    }
}
