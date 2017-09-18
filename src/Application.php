<?php

namespace RollingRelease;

use RollingRelease\Command\SelfUpdate;
use Symfony\Component\Console\Application as BaseApplication;

final class Application extends BaseApplication
{

    const VERSION = '@package_version@';

    public function __construct()
    {
        parent::__construct('rolling-release', self::VERSION);
        $this->addCommands([new SelfUpdate()]);
    }


}
