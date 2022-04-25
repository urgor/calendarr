<?php declare(strict_types=1);

namespace Urgor\Calendarr;

class Reg
{
    /** @var Config */
    public static $cfg;
    /** @var \GdImage GD image object handler */
    public static $img;
    /** @var Pixel */
    public static $x;
    /** @var Pixel */
    public static $y;

    /**
     * @param string $configFile
     * @return void
     * @throws \Exception
     */
    public static function setConfig(string $configFile)
    {
        self::$cfg = Config::create($configFile);
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
