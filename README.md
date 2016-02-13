## Synopsis

Create stadium like shape calendar png image. Or circle. Stroke past days, highlight current day or custom days. Style customizable amlost everything.

<img src="http://urgor.com.ua/tools/calendar.php" />

## Code Example
Simplest bootstrap (calendar_example.php) file:
```
include 'calendarr.inc';
\Calendarr\Reg::setConfig('calendar.ini');
\Calendarr\Reg::drawCalendar();
```
Or look into Reg and create your own custom bootstrap interface.

## Motivation

Everyone has their own representation (idea) of how to visualise the year. So this is **my** representation of calendar.

## Installation

Depends on GD and FreeType libs.
Checkout, put somwhere into www dir and request http://....calendar_example.php . It will generates png image with common headers (by default).

## Configuration

Example config file calendar.ini contain some styles for calendar. Read comment and create your own style.

You can add some GET parameters to scrtipt to change layout options (beside config), such as
* shape -- view shape of calendar (circle or ellipse)
* year -- shown year (e.g. 2010)
* radius -- main radius of calendar
* xSize -- canvas x size
* ySize -- canvas y size

## Contributors

Any feedback and pullrequests are appreciated.

## License

Use Calendarr at your own risk.
