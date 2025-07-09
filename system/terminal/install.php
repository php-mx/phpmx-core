<?php

use PhpMx\Dir;
use PhpMx\File;
use PhpMx\Terminal;

return new class extends Terminal {

    function __invoke()
    {
        self::structure('class');
        self::structure('class/Controller');

        self::echoLine();

        self::structure('library');
        self::structure('library/assets');

        self::echoLine();

        self::structure('system');
        self::structure('system/helper');
        self::structure('system/helper/function');
        self::structure('system/helper/constant');
        self::structure('system/helper/script');
        self::structure('system/routes');
        self::structure('system/view');

        self::echoLine();

        self::promote('system/helper/script/path.php');
        self::promote('.htaccess');
        self::promote('index.php');

        self::echoLine();

        $env = '';
        $env .= "\nDEV = true";
        $env .= "\n";
        self::file('.env', $env);

        $ignore = '';
        $ignore .= "\n/.env";
        $ignore .= "\n/class/Model/Db*/Driver";
        $ignore .= "\n/storage/cache";
        $ignore .= "\n/storage/certificate";
        $ignore .= "\n/vendor";
        $ignore .= "\n";
        self::file('.gitignore', $ignore);

        $mx = '<?php';
        $mx .= "\n";
        $mx .= "\nrequire \"./vendor/autoload.php\";";
        $mx .= "\n";
        $mx .= "\ndate_default_timezone_set(\"America/Sao_Paulo\");";
        $mx .= "\n";
        $mx .= "\necho \"---< MX-CMD >---\\n\\n\";";
        $mx .= "\n";
        $mx .= "\n\$terminalArgs = \$argv;";
        $mx .= "\n";
        $mx .= "\narray_shift(\$terminalArgs);";
        $mx .= "\n\PhpMx\Terminal::run(...\$terminalArgs);";
        $mx .= "\n";
        $mx .= "\necho \"\\n---< MX-CMD >---\\n\";";
        $mx .= "\n";
        $mx .= "\ndie;";
        self::file('mx', $mx);

        self::echoLine();

        self::run('composer 1');

        self::echo('PHPMX Installed');
    }

    protected static function structure($path)
    {
        if (!Dir::check($path)) {
            Dir::create("$path");
            self::echo("$path [created]");
        } else {
            self::echo("$path [ignored]");
        }
    }

    protected static function promote($file)
    {
        if (!File::check($file)) {
            self::run("promote $file");
            self::echo("$file [promoted]");
        } else {
            self::echo("$file [ignored]");
        }
    }

    protected static function file($file, $content)
    {
        if (!File::check($file)) {
            File::create($file, $content);
            self::echo("$file [created]");
        } else {
            self::echo("$file [ignored]");
        }
    }
};
