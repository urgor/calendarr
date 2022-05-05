<?php declare(strict_types=1);

namespace Urgor\Calendarr;

class PixelY extends Pixel
{

    public function __construct($value = 0)
    {
        parent::__construct(Reg::$cfg['layout']['ySize'] - $value);
    }

    /**
     * Calculate coordinate shift without remembering
     * @param float $value Shift
     */
    public function add(float $value): float
    {
        return $this->currentValue - $value;
    }

    /**
     * Shift relative current point
     * @param float $value
     * @return float
     */
    public function depose(float $value): float
    {
        return $this->currentValue = $this->currentValue - $value;
    }

    /**
     * Shift relative begin point
     * @param float $value
     * @return float
     */
    public function deposeOfBegin(float $value): float
    {
        return $this->currentValue = $this->beginValue - $value;
    }

}
