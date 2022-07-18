<?php declare(strict_types=1);

namespace Urgor\Calendarr\Config;

class PHPArray extends Cfg
{
    public function __construct(array $data)
    {
        $this->data = $data;
    }
}
