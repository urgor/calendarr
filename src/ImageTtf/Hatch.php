<?php declare(strict_types=1);

namespace Urgor\Calendarr\ImageTtf;

use Urgor\Calendarr\Reg;

/**
 * Crosses out text
 */
class Hatch extends Decorator implements TextDecorable
{
    const TYPE_STRICT = 'strict';
    const TYPE_DOUBLE_STRICT = 'double_strict';
    const TYPE_ROUGH = 'rough';
    const TYPE_DOUBLE_ROUGH = 'double_rough';

    /**
     * @var int
     */
    private $color;
    /**
     * @var int
     */
    private $thick;
    /**
     * @var string
     */
    private $type;

    /** @inheritDoc */
    public function __construct(TextDecorable $text, array $properties)
    {
        parent::__construct($text, $properties);
        $this->color = self::getColor($properties['hatch_color'] ?? '000000');
        $this->thick = (int)($properties['hatch_thick'] ?? 1);
        $this->type = $properties['hatch_type'] ?? self::TYPE_STRICT;
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
        $box = $this->text->imagettftext($text, $size, $angle, $x, $y, $color, $fontFilename);

        imagesetthickness(Reg::$img, $this->thick);
        switch ($this->type) {
            case self::TYPE_DOUBLE_STRICT:
                imageline(Reg::$img, $box[6], $box[7], $box[2], $box[3], $this->color);
            case self::TYPE_STRICT:
                imageline(Reg::$img, $box[4], $box[5], $box[0], $box[1], $this->color);
                break;
            case self::TYPE_DOUBLE_ROUGH:
                imageline(Reg::$img, $box[6], $box[7], $box[2] + self::coorRand(4), $box[3] + self::coorRand(4), $this->color);
            case self::TYPE_ROUGH:
                imageline(Reg::$img, $box[4], $box[5], $box[0] + self::coorRand(4), $box[1] + self::coorRand(4), $this->color);
        }

        return $box;
    }

    /**
     * Controllable randomize number to create some fuzziness.
     * @param $fuziness
     * @return float|int
     */
    private static function coorRand($fuziness)
    {
        return mt_rand(0, $fuziness) - $fuziness / 2;
    }
}
