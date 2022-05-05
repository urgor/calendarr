## Synopsis

Create stadium like shape calendar png image. Or circle. Cross out past days, highlight current day or custom days. Style customizable amlost everything.

<img src="http://urgor.com.ua/tools/calendar.php" />

## Code Example
Simplest bootstrap (calendar_example.php) file:
```
use \Urgor\Calendarr\Reg;
use \Urgor\Calendarr\Config\AbstractCfg;

include '../vendor/autoload.php';

Reg::setConfig(AbstractCfg::create('calendar_example.ini'));
$calendarr = new Urgor\Calendarr\Calendar();
$calendarr->drawAndOutput(); // no cache
// or use string below for cached one
// $calendarr->fetchCache('../tmp', 'calendar_'.date('m-d').'_'); // use disk cache

```
Or look into Reg and create your own custom bootstrap interface.

## Motivation

Everyone has their own representation (idea) of how to visualise the year. So this is **my** representation of calendar.

## Installation

Depends on GD and FreeType libs.
Checkout, put somwhere into www dir and request http://....calendar_example.php . It will generates png image with common headers (by default).

## Configuration

Example config file calendar.ini contain some styles for calendar. Read comment and create your own style.

Possible style keywords with their values are:
* color = ff0000 ; css hex color representation (in rgb or rgba format)
* font = UbuntuMono-B.ttf ; just font file name
* size = 12 ; font size in px
* hatch_color = 000000cc ; color
* hatch_thick = 3 ; crosses out the text, in px
* hatch_type = double_rough ; crosses out past days { strict | double_strict | rough | double_rough }
* frame_color = 00cc00
* frame_thick = 2
* background_color = ff0000
* background_growth = 2 ; enlarge background frame on this px amount


## Contributors

Any feedback and pullrequests are appreciated.

## License

Use Calendarr at your own risk.
