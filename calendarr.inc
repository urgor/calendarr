<?php
namespace Calendarr;

abstract class Pixel {

	const X = 'x';
	const Y = 'y';

	protected $_current_value, $_begin_value;

	/**
	 * Class factory
	 * Фабрика класса. Вернёт экземпляр X или Y
	 * @param str $type Pixel::X | Pixel::Y   Класс возвращаемого объекта
	 * @param float $value Начальная координата
	 * @abstract
	 * @
	 * @return \PixelX
	 */
	public static function create($type, $value) {
		return self::X === $type ? new PixelX($value) : new PixelY($value);
	}

	public function __construct($value) {
		$this->_current_value = $value;
		$this->_begin_value = $value;
	}

	/**
	 * Переместит текущую координату в начальную
	 * @return float
	 */
	public function resetToBegin() {
		return $this->_current_value = $this->_begin_value;
	}

	/**
	 * для простого получения текущего значения координаты
	 * @return float
	 */
	public function __invoke() {
		return $this->_current_value;
	}

	/**
	 * Ставит текущую координату  каа начальную
	 * @return float
	 */
	public function setCurrentAsBegin() {
		return $this->_begin_value = $this->_current_value;
	}

	/**
	 * Установить начальное и текущее значение
	 * @param float $value
	 * @return float
	 */
	public function set($value) {
		return $this->_begin_value = $this->_current_value = $value;
	}

	/**
	 * Вернёт текущее значение координаты
	 * @return float
	 */
	public function get() {
		return $this->_current_value;
	}

}

class PixelX extends Pixel {

	public function __construct($value = 0) {
		parent::__construct($value);
	}

	/**
	 * Расч`т смещения координаты без запоминания
	 * @param int $value Смещение
	 */
	public function add($value) {
		return $this->_current_value + $value;
	}

	/**
	 * Сдвинет относительно текущей точки
	 * @param float $value
	 * @return float
	 */
	public function depose($value) {
		return $this->_current_value = $this->_current_value + $value;
	}

	/**
	 * Сдвинет относительно начальной точки
	 * @param float $value
	 * @return float
	 */
	public function deposeOfBegin($value) {
		return $this->_current_value = $this->_begin_value + $value;
	}

}

class PixelY extends Pixel {

	public function __construct($value = 0) {
		parent::__construct(Reg::$cfg['layout']['ySize'] - $value);
	}

	/**
	 * Расч`т смещения координаты без запоминания
	 * @param int $value Смещение
	 */
	public function add($value) {
		return $this->_current_value - $value;
	}

	/**
	 * Сдвинет относительно текущей точки
	 * @param float $value
	 * @return float
	 */
	public function depose($value) {
		return $this->_current_value = $this->_current_value - $value;
	}

	/**
	 * Сдвинет относительно начальной точки
	 * @param float $value
	 * @return float
	 */
	public function deposeOfBegin($value) {
		return $this->_current_value = $this->_begin_value - $value;
	}

}

class Reg {

	public static $cfg, $img, $x, $y;

	public static function setConfig($configFile) {
		self::$cfg = Config::create($configFile);
	}

	public static function setY($y) {
		self::$y = $y;
	}

	public static function setX($x) {
		self::$x = $x;
	}

	public function fetchCache($dir, $prefix) {
		$key = self::$cfg->getKey();
		if (file_exists($dir.'/'.$prefix.$key.'.png')) {
			header("Content-Type: image/png");
			readfile($dir.'/'.$prefix.$key.'.png');
		} else {
			self::drawCalendar();
			imagepng(self::$img, $dir.'/'.$prefix.$key.'.png', 5);
		}
	}

	public function drawCalendar() {
		Reg::$img = imagecreatetruecolor(Reg::$cfg['layout']['xSize'], Reg::$cfg['layout']['ySize']);
		if (!is_resource(Reg::$img)) throw new Exception('Can not create image ressource', 1);

		Reg::setX(Pixel::create(Pixel::X, Reg::$cfg['layout']['xSize']/2));
		Reg::setY(Pixel::create(Pixel::Y, Reg::$cfg['layout']['ySize']/2));

		Decorator::init();
		(new Calendar)->draw();
		Decorator::afterDraw();
  		header("Content-Type: image/png");
		imagepng(Reg::$img, null, 5);
	}
}

class Calendar {

	private $monthPoints = [];

	public function __construct() {
	}

	public function draw() {
		$this->i = 0;

		$this->date = new \DateTime(Reg::$cfg['layout']['year'] . '-01-01');

		$daysInYear = '0' === $this->date->format('L') ? 364 : 365;

		list($this->fontWidth, $this->fontHeight) = Decorator::getFontDims('DOW');
		Reg::$x->deposeOfBegin(-$this->fontWidth/2); Reg::$x->setCurrentAsBegin();
		Reg::$y->deposeOfBegin(-$this->fontHeight/2); Reg::$y->setCurrentAsBegin();

		//////////////////////////////////////////////////////  //////////////////////////////////////////////////////////
		// каждый день рисуется отступая от центра окружности. Вычисляем его и устанавливаем.
		// Будемнумеровать дни 0=пн, 1=вт ... 5=сб, 6=вс  для удобства отступа от радиуса
		$this->dayOfWeek = (int) $this->date->format('N') - 1;

		$weekShiftForDow = 0;
		$ddate = new \DateTime(Reg::$cfg['layout']['year'].'-01-01');
		$days = 1 == $ddate->format('L') ? 366 : 365;
		$this->degenerateInSpring = 0;
		$this->degenerateInFall = 0;
		for ($j = 1; $j <= $days; $j++) {
			if ('1_1' == $ddate->format('j_N'))
				if(in_array($ddate->format('n'), array(3,4,5))) {
					$this->degenerateInSpring++;
				} elseif (in_array($ddate->format('n'), array(9,10,11))) {
					$this->degenerateInFall++;
				}
			$ddate->modify('+1 day');
		}
		unset($ddate);
		if (Reg::$cfg['style']['DOW']) {
			if ('ellipse' == Reg::$cfg['layout']['shape']) {
				$weekShiftForDowSpring = (24 - $this->degenerateInSpring) * 7 ;
				$weekShiftForDowFall = (24 - $this->degenerateInFall) * 7 ;
			}
			elseif ('circle' == Reg::$cfg['layout']['shape']) {
				$weekShiftForDow = (24 - ($this->degenerateInSpring+$this->degenerateInFall) - 1) * 7 ;
				$weekShiftForDowSpring = 0;
				$weekShiftForDowFall = 0;
			}
		}
		$this->alphaOfDay = 2 * pi() / ($daysInYear + 7 + $weekShiftForDow); // +7 чтоб начало года не пересекалось с концом года

		$this->alphaOfDaySpring = 2 * pi() / ($daysInYear + 7 + $weekShiftForDowSpring) * 2;
		$this->alphaOfDayFall	= 2 * pi() / ($daysInYear + 7 + $weekShiftForDowFall) * 2;

		$this->alpha = pi();
		$this->firstWeekOfSide = false;
		for ($i = 0; $i <= $daysInYear; $i++) {
			$r = $this->dayOfWeek * ($this->fontWidth + Reg::$cfg['layout']['kerning']) + Reg::$cfg['layout']['radius'];

			Reg::$x->deposeOfBegin($r * cos($this->alpha));
			Reg::$y->deposeOfBegin($r * sin($this->alpha));
			Decorator::day($this->date);

			$this->dayOfWeek++;
			$this->date->modify('+1 day');

			if(Reg::$cfg['style']['DOW']) {
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

	private function _makeWeekJob() {
		if ('circle' == Reg::$cfg['layout']['shape']) {
			$this->alpha -= $this->alphaOfDay * 7; // сдвигаем угол сразу на неделю
		} elseif ('ellipse' == Reg::$cfg['layout']['shape']) {
			if (!$this->firstWeekOfSide && in_array($this->date->format('n'), array(1, 2, 12))) { // зима; инккрементим Y
				Reg::$y->deposeOfBegin($this->fontHeight + Reg::$cfg['layout']['spacing']);
				Reg::$y->setCurrentAsBegin();
				$this->alpha = pi(); // сдвигаем угол сразу на неделю
			} elseif (!$this->firstWeekOfSide && in_array($this->date->format('n'), array(6, 7, 8))) { // лето; декрементим Y
				Reg::$y->deposeOfBegin(-($this->fontHeight + Reg::$cfg['layout']['spacing'] + (($this->fontHeight + Reg::$cfg['layout']['spacing']) / 13))); // ($this->fontHeight+Reg::$cfg['layout']['spacing'])/13) -- для компенсации одной недели, Чтоб новый год не накладывался
				Reg::$y->setCurrentAsBegin();
				$this->alpha = 0; // сдвигаем угол сразу на неделю
			} else { // осень, весна ; изменяем угол
				$this->alpha -= $this->alphaOfDay *1.33 * 7; // сдвигаем угол сразу на неделю
				if(in_array($this->date->format('n'), array(1, 2, 12, 6, 7, 8))) {
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
	private function _drawDOW() {
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

	private function _mark(){
		imageellipse ( Reg::$img, Reg::$x->get() , Reg::$y->get() , 5, 5, Decorator::DOW());
	}

	private function monthsCalculate() {
		// jan
		$B = $this->monthPoints[11];
		$A = $this->monthPoints[0];
		$a = abs(abs($A['x']) - abs($B['x']));
		$b = abs(abs($A['y']) - abs($B['y']));
		$c = sqrt(pow($a, 2) + pow($b, 2));
		$angle = acos( $a / $c ) * 180 / pi();
		Reg::$x->set($B['x']);
		Reg::$y->set($B['y']);
		Decorator::months(Reg::$cfg['lang']['months'][0], $angle, $A);
		// feb
		$B = $this->monthPoints[0];
		$A = $this->monthPoints[1];
		$a = abs(abs($A['x']) - abs($B['x']));
		$b = abs(abs($A['y']) - abs($B['y']));
		$c = sqrt(pow($a, 2) + pow($b, 2));
		$angle = acos( $a / $c ) * 180 / pi();
		Reg::$x->set($B['x']);
		Reg::$y->set($B['y']);
		Decorator::months(Reg::$cfg['lang']['months'][1], $angle, $A);
		// mar
		$B = $this->monthPoints[1];
		$A = $this->monthPoints[2];
		$a = abs(abs($A['x']) - abs($B['x']));
		$b = abs(abs($A['y']) - abs($B['y']));
		$c = sqrt(pow($a, 2) + pow($b, 2));
		$angle = acos( $a / $c ) * 180 / pi();
		Reg::$x->set($B['x']);
		Reg::$y->set($B['y']);
		Decorator::months(Reg::$cfg['lang']['months'][2], $angle, $A);
		// apr
		$B = $this->monthPoints[2];
		$A = $this->monthPoints[3];
		$a = abs(abs($A['x']) - abs($B['x']));
		$b = abs(abs($A['y']) - abs($B['y']));
		$c = sqrt(pow($a, 2) + pow($b, 2));
		$angle = - acos( $a / $c ) * 180 / pi();
		Reg::$x->set($B['x']);
		Reg::$y->set($B['y']);
		Decorator::months(Reg::$cfg['lang']['months'][3], $angle, $A);
		// may
		$B = $this->monthPoints[3];
		$A = $this->monthPoints[4];
		$a = abs(abs($A['x']) - abs($B['x']));
		$b = abs(abs($A['y']) - abs($B['y']));
		$c = sqrt(pow($a, 2) + pow($b, 2));
		$angle = - acos( $a / $c ) * 180 / pi();
		Reg::$x->set($B['x']);
		Reg::$y->set($B['y']);
		Decorator::months(Reg::$cfg['lang']['months'][4], $angle, $A);
		// jun
		$B = $this->monthPoints[4];
		$A = $this->monthPoints[5];
		$a = abs(abs($A['y']) - abs($B['y']));
		$b = abs(abs($A['x']) - abs($B['x']));
		$c = sqrt(pow($a, 2) + pow($b, 2));
		$angle = 90 + acos( $a / $c ) * 180 / pi();
		Reg::$x->set($A['x']);
		Reg::$y->set($A['y']);
		Decorator::months(Reg::$cfg['lang']['months'][5], $angle, $B);
		// jul
		$B = $this->monthPoints[5];
		$A = $this->monthPoints[6];
		$a = abs(abs($A['x']) - abs($B['x']));
		$b = abs(abs($A['y']) - abs($B['y']));
		$c = sqrt(pow($a, 2) + pow($b, 2));
		$angle = acos( $a / $c ) * 180 / pi();
		Reg::$x->set($A['x']);
		Reg::$y->set($A['y']);
		Decorator::months(Reg::$cfg['lang']['months'][6], $angle, $B);
		// aug
		$B = $this->monthPoints[6];
		$A = $this->monthPoints[7];
		$a = abs(abs($A['x']) - abs($B['x']));
		$b = abs(abs($A['y']) - abs($B['y']));
		$c = sqrt(pow($a, 2) + pow($b, 2));
		$angle = acos( $a / $c ) * 180 / pi();
		Reg::$x->set($A['x']);
		Reg::$y->set($A['y']);
		Decorator::months(Reg::$cfg['lang']['months'][7], $angle, $B);
		// sep
		$B = $this->monthPoints[7];
		$A = $this->monthPoints[8];
		$a = abs(abs($A['x']) - abs($B['x']));
		$b = abs(abs($A['y']) - abs($B['y']));
		$c = sqrt(pow($a, 2) + pow($b, 2));
		$angle = acos( $a / $c ) * 180 / pi();
		Reg::$x->set($A['x']);
		Reg::$y->set($A['y']);
		Decorator::months(Reg::$cfg['lang']['months'][8], $angle, $B);
		// oct
		$B = $this->monthPoints[8];
		$A = $this->monthPoints[9];
		$a = abs(abs($A['x']) - abs($B['x']));
		$b = abs(abs($A['y']) - abs($B['y']));
		$c = sqrt(pow($a, 2) + pow($b, 2));
		$angle = - acos( $a / $c ) * 180 / pi();
		Reg::$x->set($A['x']);
		Reg::$y->set($A['y']);
		Decorator::months(Reg::$cfg['lang']['months'][9], $angle, $B);
		// nov
		$B = $this->monthPoints[9];
		$A = $this->monthPoints[10];
		$a = abs(abs($A['x']) - abs($B['x']));
		$b = abs(abs($A['y']) - abs($B['y']));
		$c = sqrt(pow($a, 2) + pow($b, 2));
		$angle = - acos( $a / $c ) * 180 / pi();
		Reg::$x->set($A['x']);
		Reg::$y->set($A['y']);
		Decorator::months(Reg::$cfg['lang']['months'][10], $angle, $B);
		// dec
		$B = $this->monthPoints[10];
		$A = $this->monthPoints[11];
		$a = abs(abs($A['y']) - abs($B['y']));
		$b = abs(abs($A['x']) - abs($B['x']));
		$c = sqrt(pow($a, 2) + pow($b, 2));
		$angle = 90 + acos( $a / $c ) * 180 / pi();
		Reg::$x->set($B['x']);
		Reg::$y->set($B['y']);
		Decorator::months(Reg::$cfg['lang']['months'][11], $angle, $A);
	}

}

////////////////////////////////////////////////////////////////////////////////
///  CONFIGZ
///

class Config implements \ArrayAccess {

	protected $data;

	protected function __construct() {}

	public static function create($configFile) {
		$info = pathinfo($configFile);
		if (!in_array(strtolower($info['extension']), ['ini'])) throw new Exception("Unknow config type");
		$class = '\Calendarr\Config'.ucfirst(strtolower($info['extension']));
		$conf = new $class($configFile);
		$conf->prepare();
		$conf->prepareStyle();
		return $conf;
	}

	/**
	 * Create key for caching
	 * @return str String of md5
	 */
	public function getKey() {
		return md5(json_encode($this->data));
	}

	protected function prepare() {
		// default
		$this->data['layout']['year'] = (int)date('Y');

		//special mark days
		$daysMarkAs = [];
		foreach ($this->data['days_mark_as'] as $colorType => $value) {
			$daysMarkAs = array_merge($daysMarkAs, array_fill_keys(array_map('trim', explode(',', $value)), $colorType));
		}
		$this->data['days_mark_as'] = $daysMarkAs;

		// handle $_REQUEST
		foreach ([
			'shape' => ['circle', 'ellipse'],
			'year' => function ($val) {return filter_var($val, FILTER_SANITIZE_NUMBER_INT, [
				'options' => [
					'default' => (int)date('Y'),
					'min_range' => 0,
				]
			]);},
			'radius' => function($val) {return $val > 0 ? (int) $val : $this->data['layout']['radius'];},
			'xSize'  => function($val) {return $val > 0 ? (int) $val : $this->data['layout']['xSize'];},
			'ySize'  => function($val) {return $val > 0 ? (int) $val : $this->data['layout']['ySize'];},
		] as $param => $filter) {
			if (array_key_exists($param, $_REQUEST)) {
				if (is_callable($filter)) $this->data['layout'][$param] = $filter($_REQUEST[$param]);
				if (is_array($filter)) {
					if (in_array($_REQUEST[$param], $filter)) $this->data['layout'][$param] = $_REQUEST[$param];
				}
			}
		}

		// lang
		$this->data['lang']['DOW'] = explode(',', $this->data['lang']['DOW']);
		$this->data['lang']['months'] = explode(',', $this->data['lang']['months']);
	}

	public function offsetExists ($offset) {
		return isset($this->data[$offset]);
	}

	public function offsetGet ($offset) {
		return isset($this->data[$offset]) ? $this->data[$offset] : null;
	}

	public function offsetSet ($offset, $value ) {
		throw new Exception("Read only mode");
	}

	public function offsetUnset ($offset) {
		throw new Exception("Read only mode");
	}
}

class ConfigIni extends Config {

	public function __construct($configFile) {
		$this->data = parse_ini_file ($configFile, true);
	}

	protected function prepareStyle() {
		foreach ($this->data as $key => $value) if ($pos = strpos($key, '_style')) {
			foreach(['align_x', 'align_y'] as $name) {
				if (isset($value[$name])) $value[$name] = filter_var($value[$name], FILTER_VALIDATE_BOOLEAN);
				else $value[$name] = false;
			}
			$this->data['style'][substr($key, 0, $pos)] = $value;
			unset($this->data[$key]);
		}
	}
}

////////////////////////////////////////////////////////////////////////////////
/// DECORATOR
///

class Decorator {

	private static $config;
	private static $image;
	private static $colorMap = [];
	private static $dateNow;
	private static $currentDayBox = false;

	public static function init() {
		imagesavealpha(Reg::$img, true);
		imagefill(Reg::$img, 0, 0, self::getColor(Reg::$cfg['layout']['background']));

		self::$dateNow = new \DateTime();
		self::$dateNow->modify('today'); // там надо для сравнения
		// year
		if (Reg::$cfg['style']['year']) Decorator::year(Reg::$cfg['layout']['year']);
	}

	private static function coor_rand($fuziness) {
		return mt_rand(0, $fuziness) - $fuziness / 2;
	}

	public static function day(\DateTime $day) {
		$styleName = 'weekday';
		if (in_array($day->format('N'), ['6', '7'])) $styleName = 'weekend';
		if ('01' == $day->format('d')) $styleName = 'kalends';

		foreach (['d.m.Y', 'd.m'] as $format) {
			$dayStr = $day->format($format);
			if (array_key_exists($dayStr, Reg::$cfg['days_mark_as'])) $styleName = Reg::$cfg['days_mark_as'][$dayStr];
		}

		$style = Reg::$cfg['style'][$styleName];
		$box = imagettfbbox ($style['size'], 0, $style['font'], $day->format('d'));

		if ($day->format('Y-m-d') === date('Y-m-d')) {
			if (isset(Reg::$cfg['current_day_backgroud_gstyle'])) {
				imagefilledrectangle (Reg::$img,
					Reg::$x->add($box[0]), Reg::$y->add($box[1]),
					Reg::$x->add($box[4]), Reg::$y->add(-$box[5]),
					self::getColor(Reg::$cfg['current_day_backgroud_gstyle']['color']));
			}
		}

		$box = imagettftext(Reg::$img, $style['size'], 0, Reg::$x->get(), Reg::$y->get(), self::getColor($style['color']), $style['font'], $day->format('d'));

		if (isset(Reg::$cfg['stroke_past_gstyle']) && $day->getTimestamp() < self::$dateNow->getTimestamp()) {
			$style = Reg::$cfg['stroke_past_gstyle'];
			imagesetthickness(Reg::$img, $style['thick']);
			switch ($style['type']) {
				case 'double_strict':
					imageline(Reg::$img, $box[6], $box[7], $box[2], $box[3], Decorator::getColor($style['color']));
				case 'strict':
					imageline(Reg::$img, $box[4], $box[5], $box[0], $box[1], Decorator::getColor($style['color']));
					break;
				case 'double_rough':
					imageline(Reg::$img, $box[6], $box[7], $box[2] + self::coor_rand(4), $box[3] + self::coor_rand(4), Decorator::getColor($style['color']));
				case 'rough':
					imageline(Reg::$img, $box[4], $box[5], $box[0] + self::coor_rand(4), $box[1] + self::coor_rand(4), Decorator::getColor($style['color']));
			}
		}
		// левый нижний угол 0;1, правый нижний 2;3, верхний правый 4;5, верхний левый 6;7
		if ($day->format('Y-m-d') === date('Y-m-d')) self::$currentDayBox = [$box[6], $box[7], $box[2], $box[3]];
	}

	/**
	 * Немного рисования после отрисовки дней
	 */
	public static function afterDraw() {
		if (self::$currentDayBox && isset(Reg::$cfg['current_day_frame_gstyle'])) {
			imagesetthickness(Reg::$img, Reg::$cfg['current_day_frame_gstyle']['thick']);
			$color = Decorator::getColor(Reg::$cfg['current_day_frame_gstyle']['color']);
			imageline(Reg::$img, self::$currentDayBox[0], self::$currentDayBox[1], self::$currentDayBox[2], self::$currentDayBox[1] , $color);
			imageline(Reg::$img, self::$currentDayBox[2], self::$currentDayBox[1], self::$currentDayBox[2], self::$currentDayBox[3] , $color);
			imageline(Reg::$img, self::$currentDayBox[2], self::$currentDayBox[3], self::$currentDayBox[0], self::$currentDayBox[3] , $color);
			imageline(Reg::$img, self::$currentDayBox[0], self::$currentDayBox[3], self::$currentDayBox[0], self::$currentDayBox[1] , $color);
		}
	}

	/**
	 * Надписи месяцев с центрированием
	 *
	 * @param  string $text  Название месяца
	 * @param  float $angle Угол наклона
	 * @param  array $B     "Правая" точка (точка назначения)
	 */
	public static function months($text, $angle, $B) {
		if (!isset(Reg::$cfg['style']['month'])) return;

		$A = ['x' => Reg::$x->get(), 'y' => Reg::$y->get()];
		$a = abs(abs($A['y']) - abs($B['y']));
		$b = abs(abs($A['x']) - abs($B['x']));
		$c = sqrt(pow($a, 2) + pow($b, 2));

		$style = Reg::$cfg['style']['month'];
		$box = imagettfbbox ($style['size'], 0, $style['font'], $text);

		$ct = ($c - ($box[2] - $box[0])) / 2;
		$a = $ct * sin($angle * pi() / 180);
		$b = $ct * cos($angle * pi() / 180);

		imagettftext(Reg::$img, $style['size'], $angle, Reg::$x->deposeOfBegin($b), Reg::$y->deposeOfBegin($a), self::getColor($style['color']), $style['font'], $text);
	}

	public static function __callstatic($name, $args) {
		if (!array_key_exists($name, Reg::$cfg['style'])) throw new Exception('Style '.$name.'is not defined', 1);
		$style = Reg::$cfg['style'][$name];
		$text = reset($args);
		$x = Reg::$x->get();
		$y = Reg::$y->get();
		if ($style['align_x'] || $style['align_x']) {
			$box = imagettfbbox ($style['size'], 0, $style['font'], $text);
			if ($style['align_x']) $x = Reg::$x->add(-abs($box[4] - $box[0])/2);
			if ($style['align_y']) $y = Reg::$y->add(abs($box[5] - $box[1])/2);
		}

		imagettftext(Reg::$img, $style['size'], 0, $x, $y, self::getColor($style['color']), $style['font'], $text);
	}

	/**
	 * Get width and height of font size
	 * @param  str $style Style name
	 * @return array [width, height]
	 */
	public static function getFontDims($style) {
		if (!array_key_exists($style, Reg::$cfg['style'])) throw new Exception('Style '.$style.'is not defined', 1);

		$box = imagettfbbox (Reg::$cfg['style'][$style]['size'], 0, Reg::$cfg['style'][$style]['font'], "moTUWEThfrsasu"); // get size
		return [abs($box[4] - $box[0])/14, abs($box[5] - $box[1])];
	}

	/**
	 * Make   color resource from hex string
	 */
	private static function getColor($color) {
		if (!isset(self::$colorMap[$color])) {
			$len = strlen($color);
			self::$colorMap[$color] = imagecolorallocatealpha(Reg::$img,
				hexdec(substr($color, 0, 2)),
				6 == $len ? hexdec(substr($color, 2, 2)) : hexdec(substr($color, 0, 2)),
				6 == $len ? hexdec(substr($color, 4, 2)) : hexdec(substr($color, 0, 2)),
				8 == $len ? floor(hexdec(substr($color, 6, 2))/2) : 0
			);
		}

		return self::$colorMap[$color];
	}

}
