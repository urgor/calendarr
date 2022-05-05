<?php declare(strict_types=1);

namespace Urgor\Calendarr\ImageTtf;

use Urgor\Calendarr\Reg;

/**
 * Draws background under text.
 */
class Background extends Decorator implements TextDecorable
{
    /** @var int */
    private $color;

    /** @var int */
    private $growth;

    /** @inheritDoc */
    public function __construct(TextDecorable $text, array $properties)
    {
        parent::__construct($text, $properties);
        $this->color = self::getColor($properties['background_color'] ?? '000000');
        $this->growth = $properties['background_growth'] ?? 0;
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
        $box = $this->text->getFontDims(null, null, $text);
        imagefilledrectangle(
            Reg::$img,
            $x - $this->growth - 2,
            $y + $this->growth + 1,
            (int)($x + $box[0] + $this->growth),
            (int)($y - $box[1] - $this->growth),
            $this->color
        );
        return $this->text->imagettftext($text, $size, $angle, $x, $y, $color, $fontFilename);
    }
}
