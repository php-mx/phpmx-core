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
        self::structure('library/certificate');

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

        $env = "DEV = true\n";
        self::file('.env', $env);

        $ignore = "/.env\n";
        $ignore .= "/class/Model/Db*/Driver\n";
        $ignore .= "/storage/cache\n";
        $ignore .= "/storage/certificate\n";
        $ignore .= "/vendor\n";
        self::file('.gitignore', $ignore);

        $mx = "<?php\n\n";
        $mx .= "require_once \"./vendor/autoload.php\";\n\n";
        $mx .= "date_default_timezone_set(\"America/Sao_Paulo\");\n\n";
        $mx .= "echo \"---< MX-CMD >---\\n\\n\";\n\n";
        $mx .= "\$terminalArgs = \$argv;\n\n";
        $mx .= "array_shift(\$terminalArgs);\n\n";
        $mx .= "\PhpMx\Terminal::run(...\$terminalArgs);\n\n";
        $mx .= "echo \"\\n---< MX-CMD >---\\n\";\n\n";
        $mx .= "die;";
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
