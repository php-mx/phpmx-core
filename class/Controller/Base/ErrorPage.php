<?php

namespace Controller\Base;

use Exception;
use PhpMx\Front;
use PhpMx\View;
use Throwable;

class ErrorPage
{
    function __invoke(int $errCode = STS_NOT_FOUND)
    {
        return self::handlePageThrowable(new Exception('Erro personalizado', $errCode));
    }

    static function handlePageThrowable(Throwable $e)
    {
        $status = $e->getCode();

        if (!is_httpStatusError($status))
            $status = STS_INTERNAL_SERVER_ERROR;

        if (env('DEV'))
            $message = $e->getMessage();


        $message = $message ?? env("STM_$status") ?? 'Erro desconhecido';

        Front::title($message);
        Front::layout(null);

        return View::render('_front/error', ['status' => $status, 'message' => $message]);
    }
}
