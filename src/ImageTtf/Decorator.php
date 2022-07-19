<?php declare(strict_types=1);

namespace Urgor\Calendarr\ImageTtf;

use Urgor\Calendarr\Reg;

/**
 * Necessary decorator methods.
 */
abstract class Decorator implements TextDecorable
{
    /**
     * @var TextDecorable
     */
    protected $text;

    /**
     * @param TextDecorable $text Core text object
     * @param array $properties Style properties to implement by this $text
     */
    public function __construct(TextDecorable $text, array $properties)
    {
        $this->text = $text;
    }

    /**
     * Creates specified style extended of current style.
     * @param string $styleName Which style had to create, based on current.
     * @return TextDecorable
     * @throws \Exception
     */
    public function extendedBy(string $styleName): TextDecorable
    {
        return Reg::$cfg['extended_style_' . $styleName]
            ? self::createStyle(Reg::$cfg['extended_style_' . $styleName], $this)
            : $this;
    }

    /**
     * Get font size of current style.
     * @param int|null $size
     * @param string|null $font
     * @param string|null $text
     * @return array [0 => width, 1 => height ]
     */
    public function getFontDims(?int $size = null, ?string $font = null, ?string $text = null): array
    {
        return $this->text->getFontDims($size, $font, $text);
    }

    /**
     * Style factory.
     * @param array $properties
     * @param TextDecorable $baseStyle
     * @return TextDecorable
     * @throws \Exception
     */
    public static function createStyle(array $properties, TextDecorable $baseStyle): TextDecorable
    {
        foreach ($properties as $property => $values) {
            $class = 'Urgor\Calendarr\ImageTtf\\' . ucfirst(explode('_', $property)[0]);
            if (!class_exists($class)) {
                throw new \Exception("No class {$class} defined for style property {$property}.");
            }
            $baseStyle = new $class($baseStyle, $properties);
        }
        return $baseStyle;
    }

    /**
     * Make color resource from css hex color representation string.
     */
    public static function getColor(string $color): int
    {
        return imagecolorallocatealpha(Reg::$img,
            hexdec(substr($color, 0, 2)),
            hexdec(substr($color, 2, 2)),
            hexdec(substr($color, 4, 2)),
            mb_strlen($color) > 6 ? (int)floor(hexdec(substr($color, 6, 2)) / 2) : 0
        );
    }
}
