<?php declare(strict_types=1);


namespace Urgor\Calendarr\ImageTtf;

/**
 * Interface to decorate text.
 */
interface TextDecorable
{
    /**
     * Main text print method
     * @param string $text Text string
     * @param float|null $size Font size in px
     * @param float|null $angle Text angle in degrees
     * @param int|null $x Base point x coordinate
     * @param int|null $y Base point y coordinate
     * @param int|null $color The color index
     * @param string|null $fontFilename Font file name to use to draw text
     * @return array Returns an array with 8 elements representing four points making the bounding box of the text.
     */
    public function imagettftext(
        string $text,
        ?float $size = null,
        ?float $angle = null,
        ?int $x = null,
        ?int $y = null,
        ?int $color = null,
        ?string $fontFilename = null
    ): array;

    /**
     * Get width and height of font size. Default - average size of one symbol.
     * @param int|null $size
     * @param string|null $font
     * @param string|null $text
     * @return array [ 0 => width, 1 => height]
     */
    public function getFontDims(?int $size = null, ?string $font = null, ?string $text = null): array;
}
