<?php

include_once 'calendarr.inc';

\Calendarr\Reg::setConfig('calendar.ini');

// \Calendarr\Reg::fetchCache('../tmp', 'calendar_'.date('m-d').'_'); // use disk cache

\Calendarr\Reg::drawCalendar(); // no cache
