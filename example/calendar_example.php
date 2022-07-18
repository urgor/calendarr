<?php
include '../vendor/autoload.php';

\Urgor\Calendarr\Reg::setConfig(\Urgor\Calendarr\Config\Cfg::create('calendar_example.ini'));
$calendar = new Urgor\Calendarr\Calendar();
$calendar->drawAndOutput(); // no cache