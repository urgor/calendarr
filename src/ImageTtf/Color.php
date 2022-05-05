<?php declare(strict_types=1);

namespace Urgor\Calendarr\ImageTtf;

/**
 * Sets color for text.
 */
class Color extends Decorator implements TextDecorable
{
    /**
     * @var int
     */
    private $color;

    /** @inheritDoc */
    public function __construct(TextDecorable $text, array $properties)
    {
        parent::__construct($text, $properties);
        $this->color = self::getColor($properties['color']);
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
        return $this->text->imagettftext($text, $size, $angle, $x, $y, $color ?? $this->color, $fontFilename);
    }
}
