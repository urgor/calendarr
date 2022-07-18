<?php declare(strict_types=1);

namespace Urgor\Calendarr\Config;

use Exception;
use Urgor\Calendarr\ImageTtf\Decorator;
use Urgor\Calendarr\ImageTtf\Text;
use Urgor\Calendarr\ImageTtf\TextDecorable;

class Cfg implements \ArrayAccess
{

    protected $data;

    /**
     * @throws Exception
     */
    public static function create($configFile): Cfg
    {
        $info = pathinfo($configFile);
        $class = 'Urgor\Calendarr\Config\\' . ucfirst(strtolower($info['extension']));
        if (!class_exists($class)) {
            throw new Exception("No $class class implemented for {$info['extension']} config extension.");
        }
        return new $class($configFile);
    }

    /**
     * @return array All cfg data
     */
    public function getAllData(): array
    {
        return $this->data;
    }

    /**
     * @throws Exception
     */
    public function init()
    {
        if (!isset($this->data['layout'])) {
            throw new Exception('There is no "layout" section in config. It is necessary!');
        }
        $keys = array_diff(['background', 'radius', 'kerning', 'spacing', 'xSize', 'ySize', 'shape', 'gd_font_path'],
            array_keys($this->data['layout']));
        if (count($keys) > 0) {
            throw new Exception('There is no ' . implode(', ', $keys) . ' key(s) in "layout" section.');
        }

        $path = realpath($this->data['layout']['gd_font_path']);
        if (false === $path) {
            throw new Exception("There is no {$path} found for fonts, that specified in 'gd_font_path' variable of 'layout' section.");
        }
        putenv('GDFONTPATH=' . $path);

        if (!isset($this->data['style_default'])) {
            throw new Exception('There is no style_default in config. It is necessary!');
        }

        $this->prepareStyles();
        $this->prepareCustomDays();
    }

    /**
     * @param string $style
     * @return TextDecorable
     * @throws Exception
     */
    public function getStyle(string $style): TextDecorable
    {
        if (!isset($this->data['style'][$style])) {
            throw new Exception("No style $style in parsed config.");
        }
        return $this->data['style'][$style];
    }

    public function offsetExists($offset): bool
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->data[$offset] ?? null;
    }

    /**
     * @throws Exception
     */
    public function offsetSet($offset, $value)
    {
        throw new Exception("Read only mode");
    }

    /**
     * @throws Exception
     */
    public function offsetUnset($offset)
    {
        throw new Exception("Read only mode");
    }

    /**
     * Prepare styles. Creates default and main styles.
     * @return void
     * @throws Exception
     */
    private function prepareStyles()
    {
        $this->data['layout']['year'] = isset($this->data['layout']['year']) ? (int)$this->data['layout']['year'] : (int)date('Y');

        $this->data['style']['default'] = Decorator::createStyle($this->data['style_default'], new Text());
        unset($this->data['style_default']);
        foreach ($this->data as $key => $value) {
            if ('style_' !== substr($key, 0, 6)) {
                continue;
            }
            $this->data['style'][substr($key, 6)] = Decorator::createStyle($value, $this->data['style']['default']);
        }

        foreach (['DOW', 'month', 'year'] as $style) {
            if (!isset($this->data['style'][$style])) {
                $this->data['style'][$style] = $this->data['style']['default'];
            }
        }
    }

    /**
     * Prepare custom days coloring
     * @return void
     */
    private function prepareCustomDays()
    {
        if (!$this->offsetExists('days_mark_as')) {
            $this->data['days_mark_as'] = [];
            return;
        }
        $daysMarkAs = [];
        foreach ($this->data['days_mark_as'] as $style => $days) {
            $daysMarkAs = array_merge($daysMarkAs, array_fill_keys($days, $style));
        }
        $this->data['days_mark_as'] = $daysMarkAs;
    }
}
