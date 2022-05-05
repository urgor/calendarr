<?php declare(strict_types=1);

namespace Urgor\Calendarr\ImageTtf;

/**
 * Sets text font size.
 */
class Size extends Decorator implements TextDecorable
{
    /**
     * @var int
     */
    private $size;

    /** @inheritDoc */
    public function __construct(TextDecorable $text, array $properties)
    {
        parent::__construct($text, $properties);
        $this->size = (int)$properties['size'];
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
        return $this->text->imagettftext($text, $size ?? $this->size, $angle, $x, $y, $color, $fontFilename);
    }

    /** @inheritDoc */
    public function getFontDims(?int $size = null, ?string $font = null, ?string $text = null): array
    {
        return $this->text->getFontDims($size ?? $this->size, $font, $text);
    }
}
