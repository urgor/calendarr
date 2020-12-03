<?php

namespace Urgor\Calendarr\Calendarr;

class Calendar
{

    private $monthPoints = [];

    public function __construct()
    {
    }

    public function draw()
    {
        $this->i = 0;

        $this->date = new \DateTime(Reg::$cfg['layout']['year'] . '-01-01');

        $daysInYear = '0' === $this->date->format('L') ? 364 : 365;

        list($this->fontWidth, $this->fontHeight) = Decorator::getFontDims('DOW');
        Reg::$x->deposeOfBegin(-$this->fontWidth / 2);
        Reg::$x->setCurrentAsBegin();
        Reg::$y->deposeOfBegin(-$this->fontHeight / 2);
        Reg::$y->setCurrentAsBegin();

        //////////////////////////////////////////////////////  //////////////////////////////////////////////////////////
        // каждый день рисуется отступая от центра окружности. Вычисляем его и устанавливаем.
        // Будемнумеровать дни 0=пн, 1=вт ... 5=сб, 6=вс  для удобства отступа от радиуса
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
        if (Reg::$cfg['style']['DOW']) {
            if ('ellipse' == Reg::$cfg['layout']['shape']) {
                $weekShiftForDowSpring = (24 - $this->degenerateInSpring) * 7;
                $weekShiftForDowFall = (24 - $this->degenerateInFall) * 7;
            } elseif ('circle' == Reg::$cfg['layout']['shape']) {
                $weekShiftForDow = (24 - ($this->degenerateInSpring + $this->degenerateInFall) - 1) * 7;
                $weekShiftForDowSpring = 0;
                $weekShiftForDowFall = 0;
            }
        }
        $this->alphaOfDay = 2 * pi() / ($daysInYear + 7 + $weekShiftForDow); // +7 чтоб начало года не пересекалось с концом года

        $this->alphaOfDaySpring = 2 * pi() / ($daysInYear + 7 + $weekShiftForDowSpring) * 2;
        $this->alphaOfDayFall = 2 * pi() / ($daysInYear + 7 + $weekShiftForDowFall) * 2;

        $this->alpha = pi();
        $this->firstWeekOfSide = false;
        for ($i = 0; $i <= $daysInYear; $i++) {
            $r = $this->dayOfWeek * ($this->fontWidth + Reg::$cfg['layout']['kerning']) + Reg::$cfg['layout']['radius'];

            Reg::$x->deposeOfBegin($r * cos($this->alpha));
            Reg::$y->deposeOfBegin($r * sin($this->alpha));
            Decorator::day($this->date);

            $this->dayOfWeek++;
            $this->date->modify('+1 day');

            if (Reg::$cfg['style']['DOW']) {
                if (1 == $this->date->format('N')) { // начало новой недели
                    $this->dayOfWeek = 0; // смещение дня недели на строке
                    $this->_makeWeekJob();
                }
                if (1 == $this->date->format('j')) { // первое число
                    if (1 != $this->date->format('N')) {
                        $this->_makeWeekJob();
                    }
                    $this->_drawDOW();
                    $this->_makeWeekJob();
                }
            } else {
                if (1 == $this->date->format('N')) { // начало новой недели
                    $this->dayOfWeek = 0; // смещение дня недели на строке
                    $this->_makeWeekJob();
                }
                if (1 == $this->date->format('j')) { // первое число
                    $this->_makeWeekJob();
                }
            }
        }

        $this->monthsCalculate();
    }

    private function _makeWeekJob()
    {
        if ('circle' == Reg::$cfg['layout']['shape']) {
            $this->alpha -= $this->alphaOfDay * 7; // сдвигаем угол сразу на неделю
        } elseif ('ellipse' == Reg::$cfg['layout']['shape']) {
            if (!$this->firstWeekOfSide && in_array($this->date->format('n'),
                    array(1, 2, 12))) { // зима; инккрементим Y
                Reg::$y->deposeOfBegin($this->fontHeight + Reg::$cfg['layout']['spacing']);
                Reg::$y->setCurrentAsBegin();
                $this->alpha = pi(); // сдвигаем угол сразу на неделю
            } elseif (!$this->firstWeekOfSide && in_array($this->date->format('n'),
                    array(6, 7, 8))) { // лето; декрементим Y
                Reg::$y->deposeOfBegin(-($this->fontHeight + Reg::$cfg['layout']['spacing'] + (($this->fontHeight + Reg::$cfg['layout']['spacing']) / 13))); // ($this->fontHeight+Reg::$cfg['layout']['spacing'])/13) -- для компенсации одной недели, Чтоб новый год не накладывался
                Reg::$y->setCurrentAsBegin();
                $this->alpha = 0; // сдвигаем угол сразу на неделю
            } else { // осень, весна ; изменяем угол
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
     * Отрисовка дней недели между каждым месяцем
     */
    private function _drawDOW()
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
            Decorator::DOW(Reg::$cfg['lang']['DOW'][$dow]);
        }
    }

    private function _mark()
    {
        imageellipse(Reg::$img, Reg::$x->get(), Reg::$y->get(), 5, 5, Decorator::DOW());
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
        Decorator::months(Reg::$cfg['lang']['months'][0], $angle, $A);
        // feb
        $B = $this->monthPoints[0];
        $A = $this->monthPoints[1];
        $a = abs(abs($A['x']) - abs($B['x']));
        $b = abs(abs($A['y']) - abs($B['y']));
        $c = sqrt(pow($a, 2) + pow($b, 2));
        $angle = acos($a / $c) * 180 / pi();
        Reg::$x->set($B['x']);
        Reg::$y->set($B['y']);
        Decorator::months(Reg::$cfg['lang']['months'][1], $angle, $A);
        // mar
        $B = $this->monthPoints[1];
        $A = $this->monthPoints[2];
        $a = abs(abs($A['x']) - abs($B['x']));
        $b = abs(abs($A['y']) - abs($B['y']));
        $c = sqrt(pow($a, 2) + pow($b, 2));
        $angle = acos($a / $c) * 180 / pi();
        Reg::$x->set($B['x']);
        Reg::$y->set($B['y']);
        Decorator::months(Reg::$cfg['lang']['months'][2], $angle, $A);
        // apr
        $B = $this->monthPoints[2];
        $A = $this->monthPoints[3];
        $a = abs(abs($A['x']) - abs($B['x']));
        $b = abs(abs($A['y']) - abs($B['y']));
        $c = sqrt(pow($a, 2) + pow($b, 2));
        $angle = -acos($a / $c) * 180 / pi();
        Reg::$x->set($B['x']);
        Reg::$y->set($B['y']);
        Decorator::months(Reg::$cfg['lang']['months'][3], $angle, $A);
        // may
        $B = $this->monthPoints[3];
        $A = $this->monthPoints[4];
        $a = abs(abs($A['x']) - abs($B['x']));
        $b = abs(abs($A['y']) - abs($B['y']));
        $c = sqrt(pow($a, 2) + pow($b, 2));
        $angle = -acos($a / $c) * 180 / pi();
        Reg::$x->set($B['x']);
        Reg::$y->set($B['y']);
        Decorator::months(Reg::$cfg['lang']['months'][4], $angle, $A);
        // jun
        $B = $this->monthPoints[4];
        $A = $this->monthPoints[5];
        $a = abs(abs($A['y']) - abs($B['y']));
        $b = abs(abs($A['x']) - abs($B['x']));
        $c = sqrt(pow($a, 2) + pow($b, 2));
        $angle = 90 + acos($a / $c) * 180 / pi();
        Reg::$x->set($A['x']);
        Reg::$y->set($A['y']);
        Decorator::months(Reg::$cfg['lang']['months'][5], $angle, $B);
        // jul
        $B = $this->monthPoints[5];
        $A = $this->monthPoints[6];
        $a = abs(abs($A['x']) - abs($B['x']));
        $b = abs(abs($A['y']) - abs($B['y']));
        $c = sqrt(pow($a, 2) + pow($b, 2));
        $angle = acos($a / $c) * 180 / pi();
        Reg::$x->set($A['x']);
        Reg::$y->set($A['y']);
        Decorator::months(Reg::$cfg['lang']['months'][6], $angle, $B);
        // aug
        $B = $this->monthPoints[6];
        $A = $this->monthPoints[7];
        $a = abs(abs($A['x']) - abs($B['x']));
        $b = abs(abs($A['y']) - abs($B['y']));
        $c = sqrt(pow($a, 2) + pow($b, 2));
        $angle = acos($a / $c) * 180 / pi();
        Reg::$x->set($A['x']);
        Reg::$y->set($A['y']);
        Decorator::months(Reg::$cfg['lang']['months'][7], $angle, $B);
        // sep
        $B = $this->monthPoints[7];
        $A = $this->monthPoints[8];
        $a = abs(abs($A['x']) - abs($B['x']));
        $b = abs(abs($A['y']) - abs($B['y']));
        $c = sqrt(pow($a, 2) + pow($b, 2));
        $angle = acos($a / $c) * 180 / pi();
        Reg::$x->set($A['x']);
        Reg::$y->set($A['y']);
        Decorator::months(Reg::$cfg['lang']['months'][8], $angle, $B);
        // oct
        $B = $this->monthPoints[8];
        $A = $this->monthPoints[9];
        $a = abs(abs($A['x']) - abs($B['x']));
        $b = abs(abs($A['y']) - abs($B['y']));
        $c = sqrt(pow($a, 2) + pow($b, 2));
        $angle = -acos($a / $c) * 180 / pi();
        Reg::$x->set($A['x']);
        Reg::$y->set($A['y']);
        Decorator::months(Reg::$cfg['lang']['months'][9], $angle, $B);
        // nov
        $B = $this->monthPoints[9];
        $A = $this->monthPoints[10];
        $a = abs(abs($A['x']) - abs($B['x']));
        $b = abs(abs($A['y']) - abs($B['y']));
        $c = sqrt(pow($a, 2) + pow($b, 2));
        $angle = -acos($a / $c) * 180 / pi();
        Reg::$x->set($A['x']);
        Reg::$y->set($A['y']);
        Decorator::months(Reg::$cfg['lang']['months'][10], $angle, $B);
        // dec
        $B = $this->monthPoints[10];
        $A = $this->monthPoints[11];
        $a = abs(abs($A['y']) - abs($B['y']));
        $b = abs(abs($A['x']) - abs($B['x']));
        $c = sqrt(pow($a, 2) + pow($b, 2));
        $angle = 90 + acos($a / $c) * 180 / pi();
        Reg::$x->set($B['x']);
        Reg::$y->set($B['y']);
        Decorator::months(Reg::$cfg['lang']['months'][11], $angle, $A);
    }
}
