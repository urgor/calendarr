<?php declare(strict_types=1);

namespace Urgor\Calendarr\Config;

class Ini extends AbstractCfg
{
    public function __construct($configFile)
    {
        $this->data = parse_ini_file($configFile, true);
    }
}
