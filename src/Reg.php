<?php declare(strict_types=1);

namespace Urgor\Calendarr;

use Urgor\Calendarr\Config\AbstractCfg;

class Reg
{
    /** @var AbstractCfg */
    public static $cfg;
    /** @var \GdImage GD image object handler */
    public static $img;
    /** @var Pixel */
    public static $x;
    /** @var Pixel */
    public static $y;

    /**
     * @param AbstractCfg $config
     * @return void
     * @throws \Exception
     */
    public static function setConfig(AbstractCfg $config)
    {
        self::$cfg = $config;
    }

    /**
     * @param Pixel $y
     * @return void
     */
    public static function setY(Pixel $y)
    {
        self::$y = $y;
    }

    /**
     * @param Pixel $x
     * @return void
     */
    public static function setX(Pixel $x)
    {
        self::$x = $x;
    }
}
