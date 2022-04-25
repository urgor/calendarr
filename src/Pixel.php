<?php declare(strict_types=1);

namespace Urgor\Calendarr;

abstract class Pixel
{
    /** @var float */
    protected $_current_value;
    /** @var float */
    protected $_begin_value;

    /**
     * @param float $value
     */
    public function __construct(float $value)
    {
        $this->_current_value = $value;
        $this->_begin_value = $value;
    }

    /**
     * Move current coord to begin
     * @return float
     */
    public function resetToBegin(): float
    {
        return $this->_current_value = $this->_begin_value;
    }

    /**
     * Get current coordinate
     * @return float
     */
    public function __invoke(): float
    {
        return $this->_current_value;
    }

    /**
     * Ставит текущую координату  каа начальную
     * @return float
     */
    public function setCurrentAsBegin(): float
    {
        return $this->_begin_value = $this->_current_value;
    }

    /**
     * Установить начальное и текущее значение
     * @param float $value
     * @return float
     */
    public function set(float $value): float
    {
        return $this->_begin_value = $this->_current_value = $value;
    }

    /**
     * Вернёт текущее значение координаты
     * @return float
     */
    public function get(): float
    {
        return $this->_current_value;
    }

}
