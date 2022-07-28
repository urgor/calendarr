<?php declare(strict_types=1);

namespace Urgor\Calendarr\ImageTtf;

use Urgor\Calendarr\Reg;

/**
 * Draws Frame for text.
 */
class Frame extends Decorator implements TextDecorable
{
    /**
     * @var int
     */
    private $color;
    /**
     * @var int
     */
    private $thick;

    /** @inheritDoc */
    public function __construct(TextDecorable $text, array $properties)
    {
        parent::__construct($text, $properties);
        $this->color = self::getColor($properties['frame_color'] ?? '000000');
        $this->thick = (int)($properties['frame_thick'] ?? 1);
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
        // imagerectangle() still acting not properly with imagesetthickness()
        $box = $this->text->imagettftext($text, $size, $angle, $x, $y, $color, $fontFilename);
        imagesetthickness(Reg::$img, $this->thick);
        imageline(Reg::$img, $box[4], $box[5], $box[2], $box[3], $this->color);
        imageline(Reg::$img, $box[2], $box[3], $box[0], $box[1], $this->color);
        imageline(Reg::$img, $box[0], $box[1], $box[6], $box[7], $this->color);
        imageline(Reg::$img, $box[6], $box[7], $box[4], $box[5], $this->color);

        return $box;
    }
}
