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
App::uses('Space', 'Rooms.Model');

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
		'Rooms.Room',
		'Rooms.RoomsLanguages', //pending
	);

/**
 * _getQueryParam
 *
 * カレンダーURLクエリーパラメータ取り出し
 *
 * @param string $paramName 取り出したいパラメータ名
 * @return bool|mixed
 */
	protected function _getQueryParam($paramName) {
		// ContainerがMainでないときは無視する
		if (isset($this->request->params['requested'])) {
			return false;
		}

		$queryFrameId = isset($this->request->query['frame_id'])
			? $this->request->query['frame_id']
			: null;
		if (! $queryFrameId || Current::read('Frame.id') == $queryFrameId) {
			return isset($this->request->query[$paramName])
				? $this->request->query[$paramName]
				: null;
		}
		return false;
	}
/**
 * _setCalendarCommonCurrent
 *
 * カレンダー設定情報設定
 *
 * @param array &$vars カレンダー共通情報
 * @return void
 */
	protected function _setCalendarCommonCurrent(&$vars) {
		$vars['frame_key'] = Current::read('Frame.key');
		$data = $this->CalendarFrameSetting->getFrameSetting();
		Current::$current['CalendarFrameSetting'] = $data['CalendarFrameSetting'];
	}

/**
 * _setDateTimeVars
 *
 * 日付時刻変数設定
 *
 * @param array &$vars カレンダー用共通変数
 * @param object &$nctm NetCommonsTimeオブジェクト
 * @return void
 */
	protected function _setDateTimeVars(&$vars, &$nctm) {
		//現在のユーザTZ「考慮済」年月日時分秒を取得
		$userNowYmdHis = $nctm->toUserDatetime('now');
		$userNowArray = CalendarTime::transFromYmdHisToArray($userNowYmdHis);
		$vars['today'] = $userNowArray;
		//print_r($vars['today']);
		// デフォルト設定
		// 現在年月日
		$vars['year'] = intval($userNowArray['year']);
		$vars['month'] = intval($userNowArray['month']);

		$qYear = $this->_getQueryParam('year');
		$qMonth = $this->_getQueryParam('month');
		$qDay = $this->_getQueryParam('day');
		if ($qYear) {
			if ($qYear < CalendarsComponent::CALENDAR_RRULE_TERM_UNTIL_YEAR_MIN) {
				$qYear = CalendarsComponent::CALENDAR_RRULE_TERM_UNTIL_YEAR_MIN;
			} elseif ($qYear > CalendarsComponent::CALENDAR_RRULE_TERM_UNTIL_YEAR_MAX) {
				$qYear = CalendarsComponent::CALENDAR_RRULE_TERM_UNTIL_YEAR_MAX;
			}
			$vars['year'] = intval($qYear);
		}
		if ($qMonth) {
			$vars['month'] = intval($qMonth);
		}
		if ($qDay) {
			$vars['day'] = intval($qDay);
		} else { //省略時は、現在の日を設置
			// 年月の指定がない場合は当日
			if ($qYear == false && $qMonth == false) {
				$vars['day'] = intval($userNowArray['day']);
			} else {
				//月末日は月によって替わるので、すべての月でかならず存在する日(つまり一日）にする。
				$vars['day'] = 1;
			}
		}
		$specDate = new DateTime(sprintf('%d-%d-%d', $vars['year'], $vars['month'], $vars['day']));
		$vars['week'] = $specDate->format('w');
		////$vars['dayOfTheWeek'] = date('w', strtotime($userNowYmdHis));	//date()は使わない
		$userTz = (new NetCommonsTime())->getUserTimezone();
		$date = new DateTime('now', (new DateTimeZone($userTz)));	//ユーザー系
		$date->setDate($userNowArray['year'], $userNowArray['month'], $userNowArray['day']);
		$date->setTime($userNowArray['hour'], $userNowArray['min'], $userNowArray['sec']);
		$vars['dayOfTheWeek'] = $date->format('w');
	}

/**
 * _setCalendarCommonVars
 *
 * カレンダー用共通変数設定
 *
 * @param array &$vars カレンダー用共通変数
 * @return void
 */
	protected function _setCalendarCommonVars(&$vars) {
		$this->_setCalendarCommonCurrent($vars);
		$vars['CalendarFrameSetting'] = Current::read('CalendarFrameSetting');

		$nctm = new NetCommonsTime();

		//日付時刻変数を設定する
		$this->_setDateTimeVars($vars, $nctm);

		//戻り先変数を設定する
		//$this->setReturnVars($vars); ※未使用
		//$this->storeRedirectPath($vars);

		//mInfo情報
		//月カレンダー情報
		////$vars['mInfo'] = CalendarTime::getMonthlyInfo($vars['year'], $vars['month']);
		//PHPカレンダー関数を使用しないgetMonthlyInfoAlt()に変更
		$vars['mInfo'] = (new CalendarTime())->getMonthlyInfoAlt($vars['year'], $vars['month']);
		//前月・当月・次月の祝日情報を取り出す。
		$vars['holidays'] = $this->Holiday->getHoliday(
			sprintf("%04d-%02d-%02d",
				$vars['mInfo']['yearOfPrevMonth'],
				$vars['mInfo']['prevMonth'],
				1),
			sprintf("%04d-%02d-%02d",
				$vars['mInfo']['yearOfNextMonth'],
				$vars['mInfo']['nextMonth'],
				$vars['mInfo']['daysInNextMonth'])
		);

		//前月1日00:00:00以後
		$dtstart = CalendarTime::dt2CalDt(
			$nctm->toServerDatetime(sprintf(
				"%04d-%02d-%02d 00:00:00",
				$vars['mInfo']['yearOfPrevMonth'],
				$vars['mInfo']['prevMonth'],
				1
			)));
		//次次月1日00:00:00含まずより前(＝次月末日23:59:59含みより前)
		list($yearOfNextNextMonth, $nextNextMonth) = CalendarTime::getNextMonth(
			$vars['mInfo']['yearOfNextMonth'],
			$vars['mInfo']['nextMonth']);
		$dtend = CalendarTime::dt2CalDt(
			$nctm->toServerDatetime(sprintf(
				"%04d/%02d/%02d 00:00:00",
				$yearOfNextNextMonth,
				$nextNextMonth,
				1 )));
		//前月・当月・次月の予定情報を取り出す。
		$planParams = array(
			//'room_id'の取捨選択は、View側でする。
			'language_id ' => Current::read('Language.id'),	//ここで言語を限定している。
			'dtstart' => $dtstart,
			'dtend' => $dtend,
		);

		if (isset($vars['sort'])) { //スケジュールでソートする場合
			if ($vars['sort'] === 'member') { //メンバー順
				//$order = array('TrackableCreator' . '.username');
				//$order = array('TrackableCreator' . '.handlename');
				$order = array(
						'TrackableCreator' . '.handlename',
						'CalendarEvent' . '.dtstart',
						);
			} else { //時間順
				$order = array('CalendarEvent' . '.dtstart');
			}
		} else {
			//$order = array('CalendarEvent' . '.start_date');
			$order = array('CalendarEvent' . '.dtstart');
		}

		$vars['parentIdType'] = array(	//これも共通なので含めておく。
			'public' => Space::getRoomIdRoot(Space::PUBLIC_SPACE_ID),	//公開
			'private' => Space::getRoomIdRoot(Space::PRIVATE_SPACE_ID),	//プライベート
			'member' => Space::getRoomIdRoot(Space::COMMUNITY_SPACE_ID),	//全会員
		);

		//room_idとspace_idの対応表を載せておく。
		$this->__setRoomInfos($vars);

		//公開対象一覧のoptions配列と自分自身のroom_idとルーム毎空間名配列を取得
		$this->__setExposeRoomOptionsEtc($vars);

		$vars['plans'] = $this->CalendarEvent->getPlans($vars, $planParams, $order);

		//CakeLog::debug("DBGDBG: vars_plans[" . print_r($vars['plans'], true) . "]");
	}

/**
 * __setRoomInfos
 *
 * ルーム関連変数の取得とセット
 *
 * @param array &$vars カレンダー用共通変数
 * @return void
 */
	private function __setRoomInfos(&$vars) {
		//room_idとspace_idの対応表を載せておく。
		$rooms = $this->Room->find('all', array(
			'recursive' => -1,
			'conditions' => array(
				'Room.id' => $this->CalendarEvent->getReadableRoomIds()
			),
			'order' => array(
				$this->Room->alias . '.id'
			)
		));
		$vars['roomSpaceMaps'] = [];
		foreach ($rooms as $room) {
			$vars['roomSpaceMaps'][$room['Room']['id']] = $room['Room']['space_id'];
		}
		$roomsLanguages = $this->RoomsLanguages->find('all', array(
			'conditions' => array(
				'room_id' => $this->CalendarEvent->getReadableRoomIds(),
				'language_id' => Current::read('Language.id'),
			),
			'recursive' => -1,
		));
		$vars['roomsLanguages'] = $roomsLanguages;
	}

/**
 * __setExposeRoomOptionsEtc
 *
 * 公開対象一覧のoptions配列と自分自身のroom_idとルーム毎空間名配列を取得
 *
 * @param array &$vars カレンダー用共通変数
 * @return void
 */
	private function __setExposeRoomOptionsEtc(&$vars) {
		//表示方法設定情報を取り出し、
		//公開対象一覧のoptions配列と自分自身のroom_idとルーム毎空間名配列を取得。
		//spaceNameOfRoomsは、ViewのCalendarCommon->getPlanMarkClassName()の中で
		//どの画面でも利用するので、共通処理としておく。
		//
		$frameSetting = $this->CalendarFrameSetting->getFrameSetting();
		//公開対象一覧のoptions配列と自分自身のroom_idとルーム毎空間名配列を取得
		list($exposeRoomOptions, $myself, $spaceNameOfRooms, $allRoomNames) =
			$this->CalendarActionPlan->getExposeRoomOptions($frameSetting);
		$vars['exposeRoomOptions'] = $exposeRoomOptions;
		$vars['myself'] = $myself;
		$vars['spaceNameOfRooms'] = $spaceNameOfRooms;
		$vars['allRoomNames'] = $allRoomNames;
	}

/**
 * storeRedirectPath
 *
 * リダイレクトURLの保存
 *
 * @param array &$vars カレンダー用共通変数
 * @return void
 */
	protected function _storeRedirectPath(&$vars) {
		// 戻り先を保存する必要があるのは
		// カレンダーコントローラーだけです
		if ($this->name != 'Calendars') {
			return;
		}
		// style指定がないときはデフォルト表示にしているときのはずです
		// あるときは特殊画面から移動してます
		$style = $this->_getQueryParam('style');
		if ($style) {
			$currentPath = $this->request->here(false);
		} else {
			$currentPath = NetCommonsUrl::backToPageUrl();
		}
		// リダイレクトURLを記録
		$frameId = Current::read('Frame.id');
		$this->Session->write(CakeSession::read('Config.userAgent') . 'calendars.' . $frameId,
			$currentPath);
		$vars['returnUrl'] = $currentPath;
	}

}
