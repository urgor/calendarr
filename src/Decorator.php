<?php

namespace Calendarr;

class Decorator
{

    private static $config;
    private static $image;
    private static $colorMap = [];
    private static $dateNow;
    private static $currentDayBox = false;

    public static function init()
    {
        imagesavealpha(Reg::$img, true);
        imagefill(Reg::$img, 0, 0, self::getColor(Reg::$cfg['layout']['background']));

        self::$dateNow = new \DateTime();
        self::$dateNow->modify('today'); // там надо для сравнения
        // year
        if (Reg::$cfg['style']['year']) {
            Decorator::year(Reg::$cfg['layout']['year']);
        }
    }

    private static function coor_rand($fuziness)
    {
        return mt_rand(0, $fuziness) - $fuziness / 2;
    }

    public static function day(\DateTime $day)
    {
        $styleName = 'weekday';
        if (in_array($day->format('N'), ['6', '7'])) {
            $styleName = 'weekend';
        }
        if ('01' == $day->format('d')) {
            $styleName = 'kalends';
        }

        foreach (['d.m.Y', 'd.m'] as $format) {
            $dayStr = $day->format($format);
            if (array_key_exists($dayStr, Reg::$cfg['days_mark_as'])) {
                $styleName = Reg::$cfg['days_mark_as'][$dayStr];
            }
        }

        $style = Reg::$cfg['style'][$styleName];
        $box = imagettfbbox($style['size'], 0, $style['font'], $day->format('d'));

        if ($day->format('Y-m-d') === date('Y-m-d')) {
            if (isset(Reg::$cfg['current_day_backgroud_gstyle'])) {
                imagefilledrectangle(Reg::$img,
                    Reg::$x->add($box[0]), Reg::$y->add($box[1]),
                    Reg::$x->add($box[4]), Reg::$y->add(-$box[5]),
                    self::getColor(Reg::$cfg['current_day_backgroud_gstyle']['color']));
            }
        }

        $box = imagettftext(Reg::$img, $style['size'], 0, Reg::$x->get(), Reg::$y->get(),
            self::getColor($style['color']), $style['font'], $day->format('d'));

        if (isset(Reg::$cfg['stroke_past_gstyle']) && $day->getTimestamp() < self::$dateNow->getTimestamp()) {
            $style = Reg::$cfg['stroke_past_gstyle'];
            imagesetthickness(Reg::$img, $style['thick']);
            switch ($style['type']) {
                case 'double_strict':
                    imageline(Reg::$img, $box[6], $box[7], $box[2], $box[3], Decorator::getColor($style['color']));
                case 'strict':
                    imageline(Reg::$img, $box[4], $box[5], $box[0], $box[1], Decorator::getColor($style['color']));
                    break;
                case 'double_rough':
                    imageline(Reg::$img, $box[6], $box[7], $box[2] + self::coor_rand(4), $box[3] + self::coor_rand(4),
                        Decorator::getColor($style['color']));
                case 'rough':
                    imageline(Reg::$img, $box[4], $box[5], $box[0] + self::coor_rand(4), $box[1] + self::coor_rand(4),
                        Decorator::getColor($style['color']));
            }
        }
        // левый нижний угол 0;1, правый нижний 2;3, верхний правый 4;5, верхний левый 6;7
        if ($day->format('Y-m-d') === date('Y-m-d')) {
            self::$currentDayBox = [$box[6], $box[7], $box[2], $box[3]];
        }
    }

    /**
     * Немного рисования после отрисовки дней
     */
    public static function afterDraw()
    {
        if (self::$currentDayBox && isset(Reg::$cfg['current_day_frame_gstyle'])) {
            imagesetthickness(Reg::$img, Reg::$cfg['current_day_frame_gstyle']['thick']);
            $color = Decorator::getColor(Reg::$cfg['current_day_frame_gstyle']['color']);
            imageline(Reg::$img, self::$currentDayBox[0], self::$currentDayBox[1], self::$currentDayBox[2],
                self::$currentDayBox[1], $color);
            imageline(Reg::$img, self::$currentDayBox[2], self::$currentDayBox[1], self::$currentDayBox[2],
                self::$currentDayBox[3], $color);
            imageline(Reg::$img, self::$currentDayBox[2], self::$currentDayBox[3], self::$currentDayBox[0],
                self::$currentDayBox[3], $color);
            imageline(Reg::$img, self::$currentDayBox[0], self::$currentDayBox[3], self::$currentDayBox[0],
                self::$currentDayBox[1], $color);
        }
    }

    /**
     * Надписи месяцев с центрированием
     *
     * @param string $text Название месяца
     * @param float $angle Угол наклона
     * @param array $B "Правая" точка (точка назначения)
     */
    public static function months($text, $angle, $B)
    {
        if (!isset(Reg::$cfg['style']['month'])) {
            return;
        }

        $A = ['x' => Reg::$x->get(), 'y' => Reg::$y->get()];
        $a = abs(abs($A['y']) - abs($B['y']));
        $b = abs(abs($A['x']) - abs($B['x']));
        $c = sqrt(pow($a, 2) + pow($b, 2));

        $style = Reg::$cfg['style']['month'];
        $box = imagettfbbox($style['size'], 0, $style['font'], $text);

        $ct = ($c - ($box[2] - $box[0])) / 2;
        $a = $ct * sin($angle * pi() / 180);
        $b = $ct * cos($angle * pi() / 180);

        imagettftext(Reg::$img, $style['size'], $angle, Reg::$x->deposeOfBegin($b), Reg::$y->deposeOfBegin($a),
            self::getColor($style['color']), $style['font'], $text);
    }

    public static function __callstatic($name, $args)
    {
        if (!array_key_exists($name, Reg::$cfg['style'])) {
            throw new Exception('Style ' . $name . 'is not defined', 1);
        }
        $style = Reg::$cfg['style'][$name];
        $text = reset($args);
        $x = Reg::$x->get();
        $y = Reg::$y->get();
        if ($style['align_x'] || $style['align_x']) {
            $box = imagettfbbox($style['size'], 0, $style['font'], $text);
            if ($style['align_x']) {
                $x = Reg::$x->add(-abs($box[4] - $box[0]) / 2);
            }
            if ($style['align_y']) {
                $y = Reg::$y->add(abs($box[5] - $box[1]) / 2);
            }
        }

        imagettftext(Reg::$img, $style['size'], 0, $x, $y, self::getColor($style['color']), $style['font'], $text);
    }

    /**
     * Get width and height of font size
     * @param str $style Style name
     * @return array [width, height]
     */
    public static function getFontDims($style)
    {
        if (!array_key_exists($style, Reg::$cfg['style'])) {
            throw new Exception('Style ' . $style . 'is not defined', 1);
        }

        $box = imagettfbbox(Reg::$cfg['style'][$style]['size'], 0, Reg::$cfg['style'][$style]['font'],
            "moTUWEThfrsasu"); // get size
        return [abs($box[4] - $box[0]) / 14, abs($box[5] - $box[1])];
    }

    /**
     * Make   color resource from hex string
     */
    private static function getColor($color)
    {
        if (!isset(self::$colorMap[$color])) {
            $len = strlen($color);
            self::$colorMap[$color] = imagecolorallocatealpha(Reg::$img,
                hexdec(substr($color, 0, 2)),
                6 == $len ? hexdec(substr($color, 2, 2)) : hexdec(substr($color, 0, 2)),
                6 == $len ? hexdec(substr($color, 4, 2)) : hexdec(substr($color, 0, 2)),
                8 == $len ? floor(hexdec(substr($color, 6, 2)) / 2) : 0
            );
        }

        return self::$colorMap[$color];
    }
}
