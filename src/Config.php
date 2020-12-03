<?php declare(strict_types=1);

namespace Urgor\Calendarr\Calendarr;

class Config implements \ArrayAccess
{

    protected $data;

    protected function __construct()
    {
    }

    public static function create($configFile)
    {
        $info = pathinfo($configFile);
        if (!in_array(strtolower($info['extension']), ['ini'])) {
            throw new Exception("Unknow config type");
        }
        $class = '\Calendarr\Config' . ucfirst(strtolower($info['extension']));
        $conf = new $class($configFile);
        $conf->prepare();
        $conf->prepareStyle();
        return $conf;
    }

    /**
     * Create key for caching
     * @return str String of md5
     */
    public function getKey()
    {
        return md5(json_encode($this->data));
    }

    protected function prepare()
    {
        // default
        $this->data['layout']['year'] = (int)date('Y');

        //special mark days
        $daysMarkAs = [];
        foreach ($this->data['days_mark_as'] as $colorType => $value) {
            $daysMarkAs = array_merge($daysMarkAs,
                array_fill_keys(array_map('trim', explode(',', $value)), $colorType));
        }
        $this->data['days_mark_as'] = $daysMarkAs;

        // handle $_REQUEST
        foreach ([
                     'shape' => ['circle', 'ellipse'],
                     'year' => function ($val) {
                         return filter_var($val, FILTER_SANITIZE_NUMBER_INT, [
                             'options' => [
                                 'default' => (int)date('Y'),
                                 'min_range' => 0,
                             ]
                         ]);
                     },
                     'radius' => function ($val) {
                         return $val > 0 ? (int)$val : $this->data['layout']['radius'];
                     },
                     'xSize' => function ($val) {
                         return $val > 0 ? (int)$val : $this->data['layout']['xSize'];
                     },
                     'ySize' => function ($val) {
                         return $val > 0 ? (int)$val : $this->data['layout']['ySize'];
                     },
                 ] as $param => $filter) {
            if (array_key_exists($param, $_REQUEST)) {
                if (is_callable($filter)) {
                    $this->data['layout'][$param] = $filter($_REQUEST[$param]);
                }
                if (is_array($filter)) {
                    if (in_array($_REQUEST[$param], $filter)) {
                        $this->data['layout'][$param] = $_REQUEST[$param];
                    }
                }
            }
        }

        // lang
        $this->data['lang']['DOW'] = explode(',', $this->data['lang']['DOW']);
        $this->data['lang']['months'] = explode(',', $this->data['lang']['months']);
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
        throw new Exception("Read only mode");
    }

    public function offsetUnset($offset)
    {
        throw new Exception("Read only mode");
    }
}
