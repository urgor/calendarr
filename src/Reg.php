<?php declare(strict_types=1);

namespace Urgor\Calendarr\Calendarr;

class Reg
{

    public static $cfg, $img, $x, $y;

    public static function setConfig($configFile)
    {
        self::$cfg = Config::create($configFile);
    }

    public static function setY($y)
    {
        self::$y = $y;
    }

    public static function setX($x)
    {
        self::$x = $x;
    }

    public function fetchCache($dir, $prefix)
    {
        $key = self::$cfg->getKey();
        if (file_exists($dir . '/' . $prefix . $key . '.png')) {
            header("Content-Type: image/png");
            readfile($dir . '/' . $prefix . $key . '.png');
        } else {
            self::drawCalendar();
            imagepng(self::$img, $dir . '/' . $prefix . $key . '.png', 5);
        }
    }

    public function drawCalendar()
    {
        Reg::$img = imagecreatetruecolor(Reg::$cfg['layout']['xSize'], Reg::$cfg['layout']['ySize']);
        if (!is_resource(Reg::$img)) {
            throw new Exception('Can not create image ressource', 1);
        }

        Reg::setX(Pixel::create(Pixel::X, Reg::$cfg['layout']['xSize'] / 2));
        Reg::setY(Pixel::create(Pixel::Y, Reg::$cfg['layout']['ySize'] / 2));

        Decorator::init();
        (new Calendar)->draw();
        Decorator::afterDraw();
        header("Content-Type: image/png");
        imagepng(Reg::$img, null, 5);
    }
}
