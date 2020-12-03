<?php declare(strict_types=1);

namespace Urgor\Calendarr\Calendarr;

abstract class Pixel
{

    const X = 'x';
    const Y = 'y';

    protected $_current_value, $_begin_value;

    /**
     * Class factory
     * Фабрика класса. Вернёт экземпляр X или Y
     * @param str $type Pixel::X | Pixel::Y   Класс возвращаемого объекта
     * @param float $value Начальная координата
     * @abstract
     * @
     * @return \PixelX
     */
    public static function create($type, $value)
    {
        return self::X === $type ? new PixelX($value) : new PixelY($value);
    }

    public function __construct($value)
    {
        $this->_current_value = $value;
        $this->_begin_value = $value;
    }

    /**
     * Переместит текущую координату в начальную
     * @return float
     */
    public function resetToBegin()
    {
        return $this->_current_value = $this->_begin_value;
    }

    /**
     * для простого получения текущего значения координаты
     * @return float
     */
    public function __invoke()
    {
        return $this->_current_value;
    }

    /**
     * Ставит текущую координату  каа начальную
     * @return float
     */
    public function setCurrentAsBegin()
    {
        return $this->_begin_value = $this->_current_value;
    }

    /**
     * Установить начальное и текущее значение
     * @param float $value
     * @return float
     */
    public function set($value)
    {
        return $this->_begin_value = $this->_current_value = $value;
    }

    /**
     * Вернёт текущее значение координаты
     * @return float
     */
    public function get()
    {
        return $this->_current_value;
    }

}
