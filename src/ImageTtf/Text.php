<?php declare(strict_types=1);

namespace Urgor\Calendarr\ImageTtf;

use Urgor\Calendarr\Reg;

/**
 * Decorable text class
 */
class Text extends Decorator implements TextDecorable
{
    /**
     * Here is no options, because it's decorable core text class.
     */
    public function __construct()
    {
    }

    /** @inheritDoc */
    public function imagettftext(
        string $text,
        ?float $size = null,
        ?float $angle = null,
        ?int $x = null,
        ?int $y = null,
        ?int $color = null,
        ?string $fontFilename = null
    ): array {
        return imagettftext(
                Reg::$img,
                $size ?? 0,
                $angle ?? 0,
                $x ?? (int)Reg::$x->get(),
                $y ?? (int)Reg::$y->get(),
                $color ?? 0,
                $fontFilename ?? '',
                $text) ?? [];
    }

    /** @inheritDoc */
    public function getFontDims(?int $size = null, ?string $font = null, ?string $text = null): array
    {
        $defaultText = 'moTUWEThfrsasu';
        $box = imagettfbbox($size ?? 0, 0, $font ?? '', $text ?? $defaultText);
        return [abs($box[4] - $box[0]) / ($text ? 1 : mb_strlen($defaultText)), abs($box[5] - $box[1])];
    }
}
