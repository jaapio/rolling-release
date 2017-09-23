<?php

namespace RollingRelease;

use RollingRelease\Command\SelfUpdate;
use Symfony\Component\Console\Application as BaseApplication;

final class Application extends BaseApplication
{

    public static $VERSION = '@package_version@';

    public function __construct()
    {
        self::$VERSION = strpos('@package_version@', '@') === 0
            ? trim(file_get_contents(__DIR__ . '/../../VERSION'))
            : '@package_version@';

        parent::__construct('rolling-release', self::$VERSION);
        $this->addCommands([new SelfUpdate()]);
    }


}
