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
 * setDateTimeVars
 *
 * 日付時刻変数設定
 *
 * @param array &$vars カレンダー用共通変数
 * @param object &$nctm NetCommonsTimeオブジェクト
 * @return void
 */
	public function setDateTimeVars(&$vars, &$nctm) {
		//現在のユーザTZ「考慮済」年月日時分秒を取得
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
			if (isset($this->request->params['named']['year']) === false && // 年月の指定がない場合は当日
				isset($this->request->params['named']['month']) === false) {
				$vars['day'] = intval($userNowArray['day']);
			} else {
				$vars['day'] = 1;	//月末日は月によって替わるので、すべての月でかならず存在する日(つまり一日）にする。
			}
		}
	}

/**
 * setReturnVars
 *
 * 戻り先変数設定
 *
 * @param array &$vars カレンダー用共通変数
 * @return void
 */
	public function setReturnVars(&$vars) {
		if (isset($this->request->params['named']['back_year'])) {
			$vars['back_year'] = intval($this->request->params['named']['back_year']);
		} else { //省略時は、$vars['year']と一致させておく。
			$vars['back_year'] = $vars['year'];
		}

		//戻り月
		if (isset($this->request->params['named']['back_month'])) {
			$vars['back_month'] = intval($this->request->params['named']['back_month']);
		} else { //省略時は、$vars['month']と一致させておく。
			$vars['back_month'] = $vars['month'];
		}

		//戻りsytlと戻りsort
		if (isset($this->request->params['named']) && isset($this->request->params['named']['return_style'])) {
			$vars['return_style'] = $this->request->params['named']['return_style'];
		}	//無いケースもある

		if (isset($this->request->params['named']) && isset($this->request->params['named']['return_sort'])) {
			$vars['return_sort'] = $this->request->params['named']['return_sort'];
		}	//無いケースもある
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

		$nctm = new NetCommonsTime();

		//日付時刻変数を設定する
		$this->setDateTimeVars($vars, $nctm);

		//戻り先変数を設定する
		$this->setReturnVars($vars);

		//mInfo情報
		$vars['mInfo'] = CalendarTime::getMonthlyInfo($vars['year'], $vars['month']);	//月カレンダー情報
		//前月・当月・次月の祝日情報を取り出す。
		$vars['holidays'] = $this->Holiday->getHoliday(
			sprintf("%04d-%02d-%02d",
				$vars['mInfo']['yearOfPrevMonth'], $vars['mInfo']['prevMonth'], 1),
			sprintf("%04d-%02d-%02d",
				$vars['mInfo']['yearOfNextMonth'], $vars['mInfo']['nextMonth'], $vars['mInfo']['daysInNextMonth'])
		);

		//前月1日00:00:00以後
		$dtstart = CalendarTime::dt2CalDt(
			$nctm->toServerDatetime(sprintf(
				"%04d-%02d-%02d 00:00:00", $vars['mInfo']['yearOfPrevMonth'], $vars['mInfo']['prevMonth'], 1)));
		//次次月1日00:00:00含まずより前(＝次月末日23:59:59含みより前)
		list($yearOfNextNextMonth, $nextNextMonth) = CalendarTime::getNextMonth($vars['mInfo']['yearOfNextMonth'], $vars['mInfo']['nextMonth']);
		$dtend = CalendarTime::dt2CalDt(
			$nctm->toServerDatetime(sprintf(
				"%04d/%02d/%02d 00:00:00", $yearOfNextNextMonth, $nextNextMonth, 1 )));
		//前月・当月・次月の予定情報を取り出す。
		$planParams = array(
			//'room_id'の取捨選択は、View側でする。
			'language_id ' => Current::read('Language.id'),	//ここで言語を限定している。
			'dtstart' => $dtstart,
			'dtend' => $dtend,
		);
		$vars['plans'] = $this->CalendarEvent->getPlans($planParams);
		//CakeLog::debug("DBGDBG: vars_plans[" . print_r($vars['plans'], true) . "]");

		$vars['parentIdType'] = array(	//これも共通なので含めておく。
			'public' => Room::PUBLIC_PARENT_ID,	//公開
			'private' => Room::PRIVATE_PARENT_ID,	//プライベート
			'member' => Room::ROOM_PARENT_ID,	//全会員
		);
	}
}
