<?php

Calendarr\Reg::setConfig('calendar_example.ini');

// Calendarr\Reg::fetchCache('../tmp', 'calendar_'.date('m-d').'_'); // use disk cache

Calendarr\Reg::drawCalendar(); // no cache
