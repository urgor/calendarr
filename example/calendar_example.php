<?php
use Urgor\Calendarr\Reg;
use Urgor\Calendarr\Config\Cfg;

include '../vendor/autoload.php';

Reg::setConfig(Cfg::create('./calendarr_config_summer.ini'));
$calendarr = new Urgor\Calendarr\Calendar();
$calendarr->drawAndOutput(); // no cache
// $calendarr->fetchCache('../tmp', 'calendar_'.date('m-d').'_'); // use disk cache
