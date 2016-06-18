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
		'Rooms.Room',
		'Rooms.RoomsLanguages', //pending
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
		$vars['today'] = $userNowArray;
		//print_r($vars['today']);
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
			// 年月の指定がない場合は当日
			if (isset($this->request->params['named']['year']) === false &&
				isset($this->request->params['named']['month']) === false) {
				$vars['day'] = intval($userNowArray['day']);
			} else {
				//月末日は月によって替わるので、すべての月でかならず存在する日(つまり一日）にする。
				$vars['day'] = 1;
			}
		}
		////$vars['dayOfTheWeek'] = date('w', strtotime($userNowYmdHis));	//date()は使わない
		$userTz = (new NetCommonsTime())->getUserTimezone();
		$date = new DateTime('now', (new DateTimeZone($userTz)));	//ユーザー系
		$date->setDate($userNowArray['year'], $userNowArray['month'], $userNowArray['day']);
		$date->setTime($userNowArray['hour'], $userNowArray['min'], $userNowArray['sec']);
		$vars['dayOfTheWeek'] = $date->format('w');
	}

/**
 * setReturnVars
 *
 * 戻り先変数設定
 *
 * @param array &$vars カレンダー用共通変数
 * @return void
 */
	/* 未使用のため削除
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
		if (isset($this->request->params['named']) &&
			isset($this->request->params['named']['return_style'])) {
			$vars['return_style'] = $this->request->params['named']['return_style'];
		}	//無いケースもある

		if (isset($this->request->params['named']) &&
			isset($this->request->params['named']['return_sort'])) {
			$vars['return_sort'] = $this->request->params['named']['return_sort'];
		}	//無いケースもある
	}
	*/

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
		//$vars['plans'] = $this->CalendarEvent->getPlans($planParams);

		if (isset($vars['sort'])) { //スケジュールでソートする場合
			if ($vars['sort'] === 'member') { //メンバー順
				//$order = array('TrackableCreator' . '.username');
				$order = array('TrackableCreator' . '.handlename');
			} else { //時間順
				$order = array('CalendarEvent' . '.dtstart');
			}
		} else {
			$order = array('CalendarEvent' . '.start_date');
		}
		$vars['plans'] = $this->CalendarEvent->getPlans($planParams, $order);

		//CakeLog::debug("DBGDBG: vars_plans[" . print_r($vars['plans'], true) . "]");

		$vars['parentIdType'] = array(	//これも共通なので含めておく。
			'public' => Room::PUBLIC_PARENT_ID,	//公開
			'private' => Room::PRIVATE_PARENT_ID,	//プライベート
			'member' => Room::ROOM_PARENT_ID,	//全会員
		);

		//room_idとspace_idの対応表を載せておく。
		$this->__setRoomInfos($vars);

		//公開対象一覧のoptions配列と自分自身のroom_idとルーム毎空間名配列を取得
		$this->__setExposeRoomOptionsEtc($vars);
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
				'Room.id' => $this->CalendarFrameSetting->getReadableRoomIds()
			),
			'order' => array(
				$this->Room->alias . '.id'
			)
		));
		$vars['roomSpaceMaps'] = Hash::combine($rooms, '{n}.Room.id', '{n}.Room.space_id');
		$roomsLanguages = $this->RoomsLanguages->find('all', array('recursive' => -1)); //pending
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
		$frameSetting = $this->CalendarFrameSetting->find('first', array(
			'recursive' => 1,	//hasManyでCalendarFrameSettingSelectRoomのデータも取り出す。
			'conditions' => array('frame_key' => Current::read('Frame.key')),
		));
		//公開対象一覧のoptions配列と自分自身のroom_idとルーム毎空間名配列を取得
		list($exposeRoomOptions, $myself, $spaceNameOfRooms) =
			$this->CalendarActionPlan->getExposeRoomOptions($frameSetting);
		$vars['exposeRoomOptions'] = $exposeRoomOptions;
		$vars['myself'] = $myself;
		$vars['spaceNameOfRooms'] = $spaceNameOfRooms;

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
		//print_r($vars);
		//if ( isset($this->request->params['named']['style']) && ($this->request->params['named']['style'] == 'weekly' ||
		//	$this->request->params['named']['style'] == 'largemonthly' ||
		//	$this->request->params['named']['style'] == 'daily' ||
		//	$this->request->params['named']['style'] == 'schedule')) {
			$currentPath = Router::url();
		//	print_r('WRITE');print_r($currentPath);
			// リダイレクトURLを記録
			$this->Session->write(CakeSession::read('Config.userAgent') . 'calendars',
			$currentPath . '?frame_id=' . Current::read('Frame.id'));
			$vars['returnUrl'] = $currentPath . '?frame_id=' . Current::read('Frame.id');
			//print_r($vars['returnUrl']);
			//print_r(CakeSession::read('Config.userAgent'));
			//$this->log('currentlog!!!', 'debug');
			//$this->log($currentPath, 'debug');
		//} else {
		//	$url = $this->Session->read(CakeSession::read('Config.userAgent'));
		//	if ($url != '' ) {
		//		$vars['returnUrl'] = $url . '?frame_id=' . Current::read('Frame.id');
		//		print_r('READ');print_r($vars['returnUrl']);
		//	}
		//}
	}

}
