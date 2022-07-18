<?php declare(strict_types=1);

namespace Urgor\Calendarr\Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use Urgor\Calendarr\Config\PHPArray;
use Urgor\Calendarr\ImageTtf\TextDecorable;
use Urgor\Calendarr\Reg;

class ConfigTest extends TestCase
{
    public function setUp(): void
    {
        Reg::$img = imagecreatetruecolor(100, 100);
    }

    public function testFailOne()
    {
        $this->expectException(Exception::class);
        $cfg = new PHPArray([]);
        $cfg->init();
        $cfg = new PHPArray(['layout' => []]);
        $cfg->init();
    }

    public function testFailTwo()
    {
        $this->expectException(Exception::class);
        $cfg = new PHPArray([
            'layout' => [
                'background' => 'ffffff',
                'radius' => 130,
                'kerning' => 12,
                'spacing' => 2,
                'xSize' => 530,
                'ySize' => 840,
            ]
        ]);
        $cfg->init();
    }

    public function testFailThree()
    {
        $this->expectException(Exception::class);
        $cfg = new PHPArray([
            'layout' => [
                'background' => 'ffffff',
                'radius' => 130,
                'kerning' => 12,
                'spacing' => 2,
                'xSize' => 530,
                'ySize' => 840,
                'shape' => 'ellipse',
                'gd_font_path' => 'zzzzz',
            ]
        ]);
        $cfg->init();
    }

    public function testPhpArray()
    {
        $cfg = new PHPArray([
            'layout' => [
                'background' => 'ffffff',
                'radius' => 130,
                'kerning' => 12,
                'spacing' => 2,
                'xSize' => 530,
                'ySize' => 840,
                'shape' => 'ellipse',
                'gd_font_path' => '.',
            ],
            'style_default' => [
                'color' => '7FB54A',
                'font' => 'UbuntuMono-B.ttf',
                'size' => 12,
            ],
        ]);
        $cfg->init();

        $this->asserttrue(in_array(TextDecorable::class, class_implements($cfg->getStyle('default'))));
    }
}
