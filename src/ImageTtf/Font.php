<?php declare(strict_types=1);

namespace Urgor\Calendarr\ImageTtf;

/**
 * Sets font for text.
 */
class Font extends Decorator implements TextDecorable
{
    /**
     * @var string
     */
    private $font;

    /** @inheritDoc */
    public function __construct(TextDecorable $text, array $properties)
    {
        parent::__construct($text, $properties);
        $this->font = $properties['font'];
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
        return $this->text->imagettftext($text, $size, $angle, $x, $y, $color, $fontFilename ?? $this->font);
    }

    /** @inheritDoc */
    public function getFontDims(?int $size = null, ?string $font = null, ?string $text = null): array
    {
        return $this->text->getFontDims($size, $font ?? $this->font, $text);
    }
}
