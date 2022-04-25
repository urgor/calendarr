<?php declare(strict_types=1);

namespace Urgor\Calendarr;

class Calendar
{
    public function fetchFromCache($dir, $prefix)
    {
        $key = Reg::$cfg->getKey();
        if (file_exists($dir . '/' . $prefix . $key . '.png')) {
            header("Content-Type: image/png");
            readfile($dir . '/' . $prefix . $key . '.png');
        } else {
            self::drawAndOutput();
            imagepng(Reg::$img, $dir . '/' . $prefix . $key . '.png', 5);
        }
    }

    public function drawAndOutput()
    {
        Reg::$img = imagecreatetruecolor((int)Reg::$cfg['layout']['xSize'], (int)Reg::$cfg['layout']['ySize']);
        if (!is_resource(Reg::$img)) {
            throw new \Exception('Can not create image ressource', 1);
        }

        Reg::setX(new PixelX(Reg::$cfg['layout']['xSize'] / 2));
        Reg::setY(new PixelY(Reg::$cfg['layout']['ySize'] / 2));

        Decorator::init();
        (new Drawer)->draw();
        Decorator::afterDraw();
        header("Content-Type: image/png");
        imagepng(Reg::$img, null, 5);
    }
}
