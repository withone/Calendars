<?php
/**
 * CalendarsApp Controller
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('AppController', 'Controller');

/**
 * CalendarsAppController
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Controller
 */
class CalendarsAppController extends AppController {

/**
 * use component
 *
 * @var array
 */
	public $components = array(
		'Pages.PageLayout',
		'Security',
	);

/**
 * use models
 *
 * @var array
 */
	public $uses = array(
		'Calendars.CalendarRrule',
		'Calendars.CalendarEvent',
		'Calendars.CalendarFrameSetting',
		'Calendars.CalendarEventShareUser',
		'Calendars.CalendarEventSelectRoom',
	);

/**
 * setCalendarCommonCurrent
 *
 * カレンダー設定情報設定
 *
 * @param array &$vars カレンダー共通情報
 * @return void
 */
	public function setCalendarCommonCurrent(&$vars) {
		$vars['frame_key'] = Current::read('Frame.key');
		$options = array(
			'conditions' => array(
				$this->CalendarFrameSetting->alias . '.frame_key' => $vars['frame_key'],
			),
			'recursive' => (-1),
		);
		$data = $this->CalendarFrameSetting->find('first', $options);
		Current::$current['CalendarFrameSetting'] = $data['CalendarFrameSetting'];
	}

/**
 * setCalendarCommonVars
 *
 * カレンダー用共通変数設定
 *
 * @param array &$vars カレンダー用共通変数
 * @return void
 */
	public function setCalendarCommonVars(&$vars) {
		$this->setCalendarCommonCurrent($vars);
		$vars['CalendarFrameSetting'] = Current::read('CalendarFrameSetting');

		//現在のユーザTZ「考慮済」年月日時分秒を取得
		$nctm = new NetCommonsTime();
		$userNowYmdHis = $nctm->toUserDatetime('now');
		$userNowArray = CalendarTime::transFromYmdHisToArray($userNowYmdHis);

		if (isset($this->request->params['named']['year'])) {
			$vars['year'] = intval($this->request->params['named']['year']);
		} else { //省略時は、現在の年を設置
			$vars['year'] = intval($userNowArray['year']);
		}

		if (isset($this->request->params['named']['month'])) {
			$vars['month'] = intval($this->request->params['named']['month']);
		} else { //省略時は、現在の月を設置
			$vars['month'] = intval($userNowArray['month']);
		}

		if (isset($this->request->params['named']['day'])) {
			$vars['day'] = intval($this->request->params['named']['day']);
		} else { //省略時は、現在の日を設置
			//$vars['day'] = intval($userNowArray['day']);
			$vars['day'] = 1;	//月末日は月によって替わるので、すべての月でかならず存在する日(つまり一日）にする。
		}

		if (isset($this->request->params['named']['back_year'])) {
			$vars['back_year'] = intval($this->request->params['named']['back_year']);
		} else { //省略時は、$vars['year']と一致させておく。
			$vars['back_year'] = $vars['year'];
		}

		if (isset($this->request->params['named']['back_month'])) {
			$vars['back_month'] = intval($this->request->params['named']['back_month']);
		} else { //省略時は、$vars['month']と一致させておく。
			$vars['back_month'] = $vars['month'];
		}

		$vars['mInfo'] = CalendarTime::getMonthlyInfo($vars['year'], $vars['month']);	//月カレンダー情報
		$vars['holidays'] = $this->Holiday->getHoliday(
			sprintf("%04d-%02d-%02d",
				$vars['mInfo']['yearOfPrevMonth'], $vars['mInfo']['prevMonth'], 1),
			sprintf("%04d-%02d-%02d",
				$vars['mInfo']['yearOfNextMonth'], $vars['mInfo']['nextMonth'], $vars['mInfo']['daysInNextMonth'])
		);
	}
}
