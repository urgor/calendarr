<?php declare(strict_types=1);

namespace Urgor\Calendarr;

abstract class Pixel
{
    /** @var float */
    protected $currentValue;
    /** @var float */
    protected $beginValue;

    /**
     * @param float $value
     */
    public function __construct(float $value)
    {
        $this->currentValue = $value;
        $this->beginValue = $value;
    }

    /**
     * Move current coord to begin
     * @return float
     */
    public function resetToBegin(): float
    {
        return $this->currentValue = $this->beginValue;
    }

    /**
     * Get current coordinate
     * @return float
     */
    public function __invoke(): float
    {
        return $this->currentValue;
    }

    /**
     * Ставит текущую координату  каа начальную
     * @return float
     */
    public function setCurrentAsBegin(): float
    {
        return $this->beginValue = $this->currentValue;
    }

    /**
     * Установить начальное и текущее значение
     * @param float $value
     * @return float
     */
    public function set(float $value): float
    {
        return $this->beginValue = $this->currentValue = $value;
    }

    /**
     * Вернёт текущее значение координаты
     * @return float
     */
    public function get(): float
    {
        return $this->currentValue;
    }
}
