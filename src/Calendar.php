<?php declare(strict_types=1);

namespace Urgor\Calendarr;

class Calendar
{
    /**
     * Get calendar from disk cache, or create new one and store to cache.
     * @param string $dir Cache directory
     * @param string $prefix Prefix for file name
     * @return void
     * @throws \Exception
     */
    public function fetchFromCache($dir, $prefix)
    {
        $key = md5(json_encode(Reg::$cfg->getAllData()));
        if (file_exists($dir . '/' . $prefix . $key . '.png')) {
            header("Content-Type: image/png");
            readfile($dir . '/' . $prefix . $key . '.png');
        } else {
            $this->drawAndOutput();
            imagepng(Reg::$img, $dir . '/' . $prefix . $key . '.png', 5);
        }
    }

    /**
     * Draw calendar and provide it proper output.
     * @return void
     * @throws \Exception
     */
    public function drawAndOutput()
    {
        Reg::$img = imagecreatetruecolor((int)Reg::$cfg['layout']['xSize'], (int)Reg::$cfg['layout']['ySize']);
        if (!is_resource(Reg::$img)) {
            throw new \Exception('Can not create image resource');
        }

        Reg::$cfg->init();
        Reg::setX(new PixelX(Reg::$cfg['layout']['xSize'] / 2));
        Reg::setY(new PixelY(Reg::$cfg['layout']['ySize'] / 2));

        (new Drawer)->init()->draw();

        header("Content-Type: image/png");
        imagepng(Reg::$img, null, 5);
    }
}
