<?php declare(strict_types=1);

namespace Calendarr;

class PixelY extends Pixel
{

    public function __construct($value = 0)
    {
        parent::__construct(Reg::$cfg['layout']['ySize'] - $value);
    }

    /**
     * Расч`т смещения координаты без запоминания
     * @param int $value Смещение
     */
    public function add($value)
    {
        return $this->_current_value - $value;
    }

    /**
     * Сдвинет относительно текущей точки
     * @param float $value
     * @return float
     */
    public function depose($value)
    {
        return $this->_current_value = $this->_current_value - $value;
    }

    /**
     * Сдвинет относительно начальной точки
     * @param float $value
     * @return float
     */
    public function deposeOfBegin($value)
    {
        return $this->_current_value = $this->_begin_value - $value;
    }

}
