<?php

use Energize\Front;
use PhpMx\Log;
use PhpMx\Request;
use PhpMx\Response;
use PhpMx\View;

return new class extends Front {

    function __invoke(Closure $next)
    {
        if (IS_API) return $next();

        try {
            $content = $next();
            if (is_httpStatus($content)) throw new Exception(env("STM_$content"), $content);
            $content = $this->renderize($content);
        } catch (Throwable $e) {
            $content = $this->renderizeThrowable($e);
        }

        if (env('DEV') && is_array($content))
            $content['log'] = Log::getArray();

        Response::content($content);
        Response::send();
    }

    protected function renderize($content): string|array
    {
        $content = $content ?? '';

        if (is_array($content)) $content = implode('', $content);

        if (!IS_PARTIAL) {
            $content = $this->renderizeLayout($content);
            $content = $this->renderizeBase($content);

            if (env('DEV')) $content = prepare("[#]\n<!--[#]-->", [$content, Log::getString()]);

            return $content;
        }

        if (Request::header('Layout-State') != self::$LAYOUT_STATE)
            $content = self::renderizeLayout($content);

        return [
            'info' => [
                'mx' => true,
                'status' => Response::getStatus(),
                'error' => is_httpStatusError(Response::getStatus()),
                'message' => env('STM_' . Response::getStatus()),
                'alert' => self::$ALERT,
            ],
            'data' => [
                'head' => self::$HEAD,
                'state' => self::$LAYOUT_STATE,
                'content' => $content
            ]
        ];
    }

    protected function renderizeBase($content = ''): string
    {
        $version = cache('energize-front-version', fn() => [
            'script' => md5(View::render("_base/script")),
            'style' => md5(View::render("_base/style"))
        ]);

        $template = View::render('_base/base', ['HEAD' => self::$HEAD]);

        return prepare($template, [
            'CONTENT' => $content,
            'LAYOUT_STATE' => self::$LAYOUT_STATE,
            'ALERT' => encapsulate(self::$ALERT),
            'SCRIPT' => url('script.js', ['v' => $version['script']]),
            'STYLE' => url('style.css', ['v' => $version['style']]),
        ]);
    }

    protected function renderizeLayout($content = ''): string
    {
        if (is_null(self::$LAYOUT))
            return "<div id='CONTENT'>\n$content\n</div>";

        $template = View::render("_base/layout/" . self::$LAYOUT, ['HEAD' => self::$HEAD]);

        return prepare($template, [
            'CONTENT' => $content
        ]);
    }

    protected function renderizeThrowable(Throwable $e)
    {
        $status = $e->getCode();
        $message = $e->getMessage();

        if ($status == STS_REDIRECT)
            $this->redirect($e);

        if (!is_httpStatus($status))
            $status = !is_class($e, Error::class) ? STS_BAD_REQUEST : STS_INTERNAL_SERVER_ERROR;

        if (empty($message))
            $message = env("STM_$status");

        if (is_json($message))
            $message = json_decode($message, true);

        if (!is_array($message) || !isset($message['message']))
            $message = ['message' => $message];

        Response::status($status);
        Response::header('Mx-Error-Message', $message['message']);
        Response::header('Mx-Error-Status', $status);

        if (env('DEV')) {
            Response::header('Mx-Error-File', $e->getFile());
            Response::header('Mx-Error-Line', $e->getLine());
        }

        if (IS_GET) {
            $errorController = new \Controller\Energize\Error;
            $content = $errorController->handleThrowable($e);
            $content = $this->renderize($content);
        }

        $content = $content ?? ['info' => ['mx' => true,], 'data' => null];

        if (is_array($content)) {
            $content['info']['status'] = $status;
            $content['info']['error'] = $status > 399;
            $content['info'] = [...$content['info'], ...$message];
            $content['info']['alert'] = self::$ALERT;
        }

        return $content;
    }

    protected function redirect(Throwable $e): never
    {
        if (IS_PARTIAL) {
            $url = !empty($e->getMessage()) ? url($e->getMessage()) : url('.');

            $scheme = [
                'info' => [
                    'mx' => true,
                    'status' => STS_REDIRECT,
                    'error' => false,
                    'location' => $url,
                    'alert' => self::$ALERT,
                ],
                'data' => null
            ];

            Response::header('Mx-Location', $url);
            Response::status(STS_OK);

            if (env('DEV')) $scheme['log'] = Log::getArray();

            Response::content($scheme);
        } else {
            Response::header('location', $e->getMessage());
            Response::status(STS_REDIRECT);
        }
        Response::send();
    }
};
