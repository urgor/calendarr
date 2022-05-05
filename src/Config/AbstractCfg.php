<?php declare(strict_types=1);

namespace Urgor\Calendarr\Config;

use Urgor\Calendarr\ImageTtf\Decorator;
use Urgor\Calendarr\ImageTtf\Text;
use Urgor\Calendarr\ImageTtf\TextDecorable;

class AbstractCfg implements \ArrayAccess
{

    protected $data;

    /**
     * @throws \Exception
     */
    public static function create($configFile): AbstractCfg
    {
        $info = pathinfo($configFile);
        $class = 'Urgor\Calendarr\Config\\' . ucfirst(strtolower($info['extension']));
        if (!class_exists($class)) {
            throw new \Exception("No $class class implemented for {$info['extension']} config extension.");
        }
        $conf = new $class($configFile);
        return $conf;
    }

    /**
     * @return array All cfg data
     */
    public function getAllData(): array
    {
        return $this->data;
    }

    public function init()
    {
        $this->data['layout']['year'] = (int)date('Y');

        $path = realpath($this->data['layout']['gd_font_path']);
        if (!file_exists($path)) {
            throw new \Exception("There is no {$path} found for fonts, that specified in 'gd_font_path' variable of 'layout' section.");
        }
        putenv('GDFONTPATH=' . $path);

        $this->prepareStyles();
        $this->prepareCustomDays();
    }

    /**
     * @param string $style
     * @return TextDecorable
     * @throws \Exception
     */
    public function getStyle(string $style): TextDecorable
    {
        if (!isset($this->data['style'][$style])) throw new \Exception("No style $style in parsed config.");
        return $this->data['style'][$style];
    }

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        throw new \Exception("Read only mode");
    }

    public function offsetUnset($offset)
    {
        throw new \Exception("Read only mode");
    }

    /**
     * Prepare styles. Creates default and main styles.
     * @return void
     * @throws \Exception
     */
    private function prepareStyles()
    {
        if (!isset($this->data['style_default'])) {
            throw new \Exception('There is no style_default in config. It is necessary!');
        }
        $this->data['style']['default'] = Decorator::createStyle($this->data['style_default'], new Text());
        unset($this->data['style_default']);
        foreach ($this->data as $key => $value) {
            if ('style_' !== substr($key, 0, 6)) continue;
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
        $daysMarkAs = [];
        foreach ($this->data['days_mark_as'] as $style => $days) {
            $daysMarkAs = array_merge($daysMarkAs, array_fill_keys($days, $style));
        }
        $this->data['days_mark_as'] = $daysMarkAs;
    }
}
