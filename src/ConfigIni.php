<?php declare(strict_types=1);

namespace Urgor\Calendarr\Calendarr;

class ConfigIni extends Config
{

    public function __construct($configFile)
    {
        $this->data = parse_ini_file($configFile, true);
    }

    protected function prepareStyle()
    {
        foreach ($this->data as $key => $value) {
            if ($pos = strpos($key, '_style')) {
                foreach (['align_x', 'align_y'] as $name) {
                    if (isset($value[$name])) {
                        $value[$name] = filter_var($value[$name], FILTER_VALIDATE_BOOLEAN);
                    } else {
                        $value[$name] = false;
                    }
                }
                $this->data['style'][substr($key, 0, $pos)] = $value;
                unset($this->data[$key]);
            }
        }
    }
}
