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

        $env = "# PRIVATE ENVIRONMENT VARIABLES\n";
        $env .= "DEV = true\n";
        $env .= "CIF = base\n";
        $env .= "CODE = mxcodekey\n";
        $env .= "JWT = jwtkey\n";
        self::file('.env', $env);

        $conf = "# PUBLIC ENVIRONMENT VARIABLES\n";
        self::file('.conf', $conf);

        $ignore = "/.env\n";
        $ignore .= "/class/Model/Db*/Driver\n";
        $ignore .= "/library/cache\n";
        $ignore .= "/library/certificate\n";
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
