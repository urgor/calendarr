<?php

namespace Urgor\Calendarr;

use Urgor\Calendarr\ImageTtf\Decorator;

class Drawer
{
    private $monthPoints = [];

    /**
     * @var \DateTime current date and time
     */
    private $dateNow;

    public function init()
    {
        imagesavealpha(Reg::$img, true);
        imagefill(Reg::$img, 0, 0, Decorator::getColor(Reg::$cfg['layout']['background']));

        $this->dateNow = new \DateTime();
        $this->dateNow->modify('today'); // need for comparing

        $box = Reg::$cfg->getStyle('year')->getFontDims(null, null, Reg::$cfg['layout']['year']);
        Reg::$cfg->getStyle('year')->imagettftext(Reg::$cfg['layout']['year'], null, null, Reg::$x->add(-$box[0] / 2), Reg::$y->add($box[1]));

        return $this;
    }

    public function draw()
    {
        $this->date = new \DateTime(Reg::$cfg['layout']['year'] . '-01-01');
        $daysInYear = '0' === $this->date->format('L') ? 364 : 365;
        list($this->fontWidth, $this->fontHeight) = Reg::$cfg->getStyle('DOW')->getFontDims();
        Reg::$x->deposeOfBegin(-$this->fontWidth / 2);
        Reg::$x->setCurrentAsBegin();
        Reg::$y->deposeOfBegin(-$this->fontHeight / 2);
        Reg::$y->setCurrentAsBegin();

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Every day draws by offset from circle`s center. Calculate it and set.
        // Day numeration from 0=mo, ... 6=su for convenience of shifting from radius
        $this->dayOfWeek = (int)$this->date->format('N') - 1;

        $weekShiftForDow = 0;
        $ddate = new \DateTime(Reg::$cfg['layout']['year'] . '-01-01');
        $days = 1 == $ddate->format('L') ? 366 : 365;
        $this->degenerateInSpring = 0;
        $this->degenerateInFall = 0;
        for ($j = 1; $j <= $days; $j++) {
            if ('1_1' == $ddate->format('j_N')) {
                if (in_array($ddate->format('n'), array(3, 4, 5))) {
                    $this->degenerateInSpring++;
                } elseif (in_array($ddate->format('n'), array(9, 10, 11))) {
                    $this->degenerateInFall++;
                }
            }
            $ddate->modify('+1 day');
        }
        unset($ddate);
        if ('ellipse' == Reg::$cfg['layout']['shape']) {
            $weekShiftForDowSpring = (24 - $this->degenerateInSpring) * 7;
            $weekShiftForDowFall = (24 - $this->degenerateInFall) * 7;
        } elseif ('circle' == Reg::$cfg['layout']['shape']) {
            $weekShiftForDow = (24 - ($this->degenerateInSpring + $this->degenerateInFall) - 1) * 7;
            $weekShiftForDowSpring = 0;
            $weekShiftForDowFall = 0;
        }

        $this->alphaOfDay = 2 * pi() / ($daysInYear + 7 + $weekShiftForDow); // +7 to prevent year begin overlapping with the end

        $this->alphaOfDaySpring = 2 * pi() / ($daysInYear + 7 + $weekShiftForDowSpring) * 2;
        $this->alphaOfDayFall = 2 * pi() / ($daysInYear + 7 + $weekShiftForDowFall) * 2;

        $this->alpha = pi();
        $this->firstWeekOfSide = false;
        for ($i = 0; $i <= $daysInYear; $i++) {
            $r = $this->dayOfWeek * ($this->fontWidth + Reg::$cfg['layout']['kerning']) + Reg::$cfg['layout']['radius'];

            Reg::$x->deposeOfBegin($r * cos($this->alpha));
            Reg::$y->deposeOfBegin($r * sin($this->alpha));
            $this->day($this->date);

            $this->dayOfWeek++;
            $this->date->modify('+1 day');

            if (1 == $this->date->format('N')) { // begin of new week
                $this->dayOfWeek = 0; // day shift on the line
                $this->makeWeekJob();
            }
            if (1 == $this->date->format('j')) { // first day of the month
                if (1 != $this->date->format('N')) {
                    $this->makeWeekJob();
                }
                $this->drawDOWNames();
                $this->makeWeekJob();
            }
        }

        $this->monthsCalculate();
    }

    private function makeWeekJob()
    {
        if ('circle' == Reg::$cfg['layout']['shape']) {
            $this->alpha -= $this->alphaOfDay * 7; // shift angle for one week
        } elseif ('ellipse' == Reg::$cfg['layout']['shape']) {
            if (!$this->firstWeekOfSide && in_array($this->date->format('n'), [1, 2, 12])) { // winter; increment Y
                Reg::$y->deposeOfBegin($this->fontHeight + Reg::$cfg['layout']['spacing']);
                Reg::$y->setCurrentAsBegin();
                $this->alpha = pi(); // shift angle for one week
            } elseif (!$this->firstWeekOfSide && in_array($this->date->format('n'), [6, 7, 8])) { // summer; decrement Y
                // compensate one week height to prevent new year overlapping
                Reg::$y->deposeOfBegin(-($this->fontHeight + Reg::$cfg['layout']['spacing'] + (($this->fontHeight + Reg::$cfg['layout']['spacing']) / 13)));
                Reg::$y->setCurrentAsBegin();
                $this->alpha = 0; // shift angle for one week
            } else { // fall and spring ; change angle
                $this->alpha -= $this->alphaOfDay * 1.33 * 7; // сдвигаем угол сразу на неделю
                if (in_array($this->date->format('n'), array(1, 2, 12, 6, 7, 8))) {
                    $this->firstWeekOfSide = false;
                } else {
                    $this->firstWeekOfSide = true;
                }
            }
        }
    }

    /**
     * Draw day of weeks between each month
     */
    private function drawDOWNames()
    {
        for ($dow = 0; $dow < 7; $dow++) {
            if (0 == $dow) {
                $dowR = Reg::$cfg['layout']['radius'] - ($this->fontWidth + Reg::$cfg['layout']['kerning']);
                Reg::$x->deposeOfBegin($dowR * cos($this->alpha));
                Reg::$y->deposeOfBegin($dowR * sin($this->alpha));
                // $this->_mark();
                $this->monthPoints[] = ['x' => Reg::$x->depose($this->fontWidth * 1.5), 'y' => Reg::$y->get()];
            }
            $dowR = $dow * ($this->fontWidth + Reg::$cfg['layout']['kerning']) + Reg::$cfg['layout']['radius'];
            Reg::$x->deposeOfBegin($dowR * cos($this->alpha));
            Reg::$y->deposeOfBegin($dowR * sin($this->alpha));
            Reg::$cfg->getStyle('DOW')->imagettftext(Reg::$cfg['lang']['DOW'][$dow]);
        }
    }

    private function monthsCalculate()
    {
        // jan
        $B = $this->monthPoints[11];
        $A = $this->monthPoints[0];
        $a = abs(abs($A['x']) - abs($B['x']));
        $b = abs(abs($A['y']) - abs($B['y']));
        $c = sqrt(pow($a, 2) + pow($b, 2));
        $angle = acos($a / $c) * 180 / pi();
        Reg::$x->set($B['x']);
        Reg::$y->set($B['y']);
        self::months(Reg::$cfg['lang']['months'][0], $angle, $A);
        // feb
        $B = $this->monthPoints[0];
        $A = $this->monthPoints[1];
        $a = abs(abs($A['x']) - abs($B['x']));
        $b = abs(abs($A['y']) - abs($B['y']));
        $c = sqrt(pow($a, 2) + pow($b, 2));
        $angle = acos($a / $c) * 180 / pi();
        Reg::$x->set($B['x']);
        Reg::$y->set($B['y']);
        self::months(Reg::$cfg['lang']['months'][1], $angle, $A);
        // mar
        $B = $this->monthPoints[1];
        $A = $this->monthPoints[2];
        $a = abs(abs($A['x']) - abs($B['x']));
        $b = abs(abs($A['y']) - abs($B['y']));
        $c = sqrt(pow($a, 2) + pow($b, 2));
        $angle = acos($a / $c) * 180 / pi();
        Reg::$x->set($B['x']);
        Reg::$y->set($B['y']);
        self::months(Reg::$cfg['lang']['months'][2], $angle, $A);
        // apr
        $B = $this->monthPoints[2];
        $A = $this->monthPoints[3];
        $a = abs(abs($A['x']) - abs($B['x']));
        $b = abs(abs($A['y']) - abs($B['y']));
        $c = sqrt(pow($a, 2) + pow($b, 2));
        $angle = -acos($a / $c) * 180 / pi();
        Reg::$x->set($B['x']);
        Reg::$y->set($B['y']);
        self::months(Reg::$cfg['lang']['months'][3], $angle, $A);
        // may
        $B = $this->monthPoints[3];
        $A = $this->monthPoints[4];
        $a = abs(abs($A['x']) - abs($B['x']));
        $b = abs(abs($A['y']) - abs($B['y']));
        $c = sqrt(pow($a, 2) + pow($b, 2));
        $angle = -acos($a / $c) * 180 / pi();
        Reg::$x->set($B['x']);
        Reg::$y->set($B['y']);
        self::months(Reg::$cfg['lang']['months'][4], $angle, $A);
        // jun
        $B = $this->monthPoints[4];
        $A = $this->monthPoints[5];
        $a = abs(abs($A['y']) - abs($B['y']));
        $b = abs(abs($A['x']) - abs($B['x']));
        $c = sqrt(pow($a, 2) + pow($b, 2));
        $angle = 90 + acos($a / $c) * 180 / pi();
        Reg::$x->set($A['x']);
        Reg::$y->set($A['y']);
        self::months(Reg::$cfg['lang']['months'][5], $angle, $B);
        // jul
        $B = $this->monthPoints[5];
        $A = $this->monthPoints[6];
        $a = abs(abs($A['x']) - abs($B['x']));
        $b = abs(abs($A['y']) - abs($B['y']));
        $c = sqrt(pow($a, 2) + pow($b, 2));
        $angle = acos($a / $c) * 180 / pi();
        Reg::$x->set($A['x']);
        Reg::$y->set($A['y']);
        self::months(Reg::$cfg['lang']['months'][6], $angle, $B);
        // aug
        $B = $this->monthPoints[6];
        $A = $this->monthPoints[7];
        $a = abs(abs($A['x']) - abs($B['x']));
        $b = abs(abs($A['y']) - abs($B['y']));
        $c = sqrt(pow($a, 2) + pow($b, 2));
        $angle = acos($a / $c) * 180 / pi();
        Reg::$x->set($A['x']);
        Reg::$y->set($A['y']);
        self::months(Reg::$cfg['lang']['months'][7], $angle, $B);
        // sep
        $B = $this->monthPoints[7];
        $A = $this->monthPoints[8];
        $a = abs(abs($A['x']) - abs($B['x']));
        $b = abs(abs($A['y']) - abs($B['y']));
        $c = sqrt(pow($a, 2) + pow($b, 2));
        $angle = acos($a / $c) * 180 / pi();
        Reg::$x->set($A['x']);
        Reg::$y->set($A['y']);
        self::months(Reg::$cfg['lang']['months'][8], $angle, $B);
        // oct
        $B = $this->monthPoints[8];
        $A = $this->monthPoints[9];
        $a = abs(abs($A['x']) - abs($B['x']));
        $b = abs(abs($A['y']) - abs($B['y']));
        $c = sqrt(pow($a, 2) + pow($b, 2));
        $angle = -acos($a / $c) * 180 / pi();
        Reg::$x->set($A['x']);
        Reg::$y->set($A['y']);
        self::months(Reg::$cfg['lang']['months'][9], $angle, $B);
        // nov
        $B = $this->monthPoints[9];
        $A = $this->monthPoints[10];
        $a = abs(abs($A['x']) - abs($B['x']));
        $b = abs(abs($A['y']) - abs($B['y']));
        $c = sqrt(pow($a, 2) + pow($b, 2));
        $angle = -acos($a / $c) * 180 / pi();
        Reg::$x->set($A['x']);
        Reg::$y->set($A['y']);
        self::months(Reg::$cfg['lang']['months'][10], $angle, $B);
        // dec
        $B = $this->monthPoints[10];
        $A = $this->monthPoints[11];
        $a = abs(abs($A['y']) - abs($B['y']));
        $b = abs(abs($A['x']) - abs($B['x']));
        $c = sqrt(pow($a, 2) + pow($b, 2));
        $angle = 90 + acos($a / $c) * 180 / pi();
        Reg::$x->set($B['x']);
        Reg::$y->set($B['y']);
        self::months(Reg::$cfg['lang']['months'][11], $angle, $A);
    }

    /**
     * Define style and print the day.
     * @param \DateTime $day
     * @return void
     * @throws \Exception
     */
    private function day(\DateTime $day)
    {
        if ('01' == $day->format('d')) {
            $style = Reg::$cfg->getStyle('default')->extendedBy('kalends');
        } elseif (in_array($day->format('N'), ['6', '7'])) {
            $style = Reg::$cfg->getStyle('default')->extendedBy('weekend');
        } else {
            $style = Reg::$cfg->getStyle('default');
        }

        foreach (['d.m.Y', 'd.m'] as $format) {
            $dayStr = $day->format($format);
            if (array_key_exists($dayStr, Reg::$cfg['days_mark_as'])) {
                $style = $style->extendedBy(Reg::$cfg['days_mark_as'][$dayStr]);
            }
        }

        if ($day->getTimestamp() < $this->dateNow->getTimestamp()) {
            $style = $style->extendedBy('past');
        } elseif ($day->format('Y-m-d') === date('Y-m-d')) {
            $style = $style->extendedBy('current_day');
        }

        $style->imagettftext($day->format('d'), null, null, Reg::$x->get(), Reg::$y->get());
    }

    /**
     * Moth names aligned
     *
     * @param string $text Month name
     * @param float $angle Angle
     * @param array $B "Right" point (destination point)
     */
    private static function months($text, $angle, $B)
    {
        $A = ['x' => Reg::$x->get(), 'y' => Reg::$y->get()];
        $a = abs(abs($A['y']) - abs($B['y']));
        $b = abs(abs($A['x']) - abs($B['x']));
        $c = sqrt(pow($a, 2) + pow($b, 2));

        $box = Reg::$cfg->getStyle('month')->getFontDims(null, null, $text);
        $ct = ($c - $box[1]) / 2;
        $a = $ct * sin($angle * pi() / 180);
        $b = $ct * cos($angle * pi() / 180);

        Reg::$cfg->getStyle('month')->imagettftext($text, null, $angle, Reg::$x->deposeOfBegin($b), Reg::$y->deposeOfBegin($a));
    }
}
