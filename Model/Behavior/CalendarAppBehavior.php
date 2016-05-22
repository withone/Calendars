<?php
/**
 * CalendarApp Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ModelBehavior', 'Model');
//App::uses('CalendarTime', 'Calendars.Utility');
App::uses('CalendarTime', 'Calendars.Utility');
App::uses('CalendarSupport', 'Calendars.Utility');
App::uses('WorkflowComponent', 'Workflow.Controller/Component');

/**
 * CalendarAppBehavior
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Model\Behavior
 */
class CalendarAppBehavior extends ModelBehavior {
	const CALENDAR_PLAN_EDIT_THIS = '0';	//この日の予定のみ変更(削除)
	const CALENDAR_PLAN_EDIT_AFTER = '1';	//この日以降の予定を変更(削除)
	const CALENDAR_PLAN_EDIT_ALL = '2';		//この日を含むすべての予定を変更(削除)

	const CALENDAR_PLUGIN_NAME = 'calendar';
	const TASK_PLUGIN_NAME = 'task';	//ＴＯＤＯプラグインに相当
	const RESERVATION_PLUGIN_NAME = 'reservation';

	const CALENDAR_LINK_UPDATE = 'update';
	const CALENDAR_LINK_CLEAR = 'clear';

	const CALENDAR_INSERT_MODE = 'insert';
	const CALENDAR_UPDATE_MODE = 'update';


	//以下は暫定定義
	const _ON = 1;
	const _OFF = 0;

	const ROOM_ZERO = 0;

/**
 * calendarWdayArray
 *
 * @var array
 */
	public static $calendarWdayArray = array('SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA');

/**
 * edit_rrrule_list
 *
 * @var array
 */
	public static $editRrules = array(
		self::CALENDAR_PLAN_EDIT_THIS,
		self::CALENDAR_PLAN_EDIT_AFTER,
		self::CALENDAR_PLAN_EDIT_ALL
	);

/**
 * 登録処理
 *
 * @param Model &$model 実際のモデル名
 * @param array $planParams planParams
 * @param array $rruleData rruleData
 * @param array $eventData eventデータ(CalendarEventのモデルデータ)
 * @param string $startTime startTime 開始日付時刻文字列
 * @param string $endTime endTime 開始日付時刻文字列
 * @return array $rEventData
 * @throws InternalErrorException
 */
	public function insert(Model &$model, $planParams, $rruleData, $eventData, $startTime, $endTime) {
		$this->loadEventAndRruleModels($model);
		$params = array(
			'conditions' => array('CalendarRrule.id' => $eventData['CalendarEvent']['calendar_rrule_id']),
			'recursive' => (-1),
			'fields' => array('CalendarRrule.*'),
			'callbacks' => false
		);
		if (empty($this->rruleData)) {
			//CalendarRruleのデータがないので初回アクセス
			//
			$rruleData = $model->CalendarRrule->find('first', $params);
			if (!is_array($rruleData) || !isset($rruleData['CalendarRrule'])) {
				$model->validationErrors = Hash::merge($model->validationErrors, $model->CalendarRrule->validationErrors);
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
			$this->rruleData = $rruleData;	//CalendarRruleのデータを記録し、２度目以降に備える。
		}

		//NC3では内部はサーバー系日付時刻になっているのでtimezoneDateはつかわない.
		//また、引数$starTime, $endTimeはすでに、YmdHis形式で渡されることになっているので、
		//$insertStartTime, $insertEndTimeにそのまま代入する。
		$insertStartTime = $startTime;
		$insertEndTime = $endTime;

		$rEventData = $this->setReventData($eventData, $insertStartTime, $insertEndTime); //eventDataをもとにrEventDataをつくり、モデルにセット

		$model->CalendarEvent->set($rEventData);

		if (!$model->CalendarEvent->validates()) {	//rEventDataをチェック
			$model->validationErrors = Hash::merge($model->validationErrors, $model->CalendarEvent->validationErrors);
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		if (!$model->CalendarEvent->save($rEventData, false)) { //保存のみ
			$model->validationErrors = Hash::merge($model->validationErrors, $model->CalendarEvent->validationErrors);
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		$rEventData['CalendarEvent']['id'] = $model->CalendarEvent->id; //採番されたidをeventDataにセ>

		if (isset($rEventData['CalendarEventContent']) && $rEventData['CalendarEventContent']['linked_model'] !== '') { //関連コンテンツの登録
			if (!(isset($this->CalendarEventContent))) {
				$model->loadModels(['CalendarEventContent' => 'Calendar.CalendarEventContent']);
			}
			$this->CalendarEventContent->saveLinkedData($rEventData);
		}

		return $rEventData;
	}

/**
 * rEventDataへのデータ設定
 *
 * @param array $eventData 元になるeventData配列
 * @param string $insertStartTime insertStartTime 登録用開始日付時刻文字列
 * @param string $insertEndTime insertEndTime 登録用終了日付時刻文字列
 * @return array 実際に登録する$rEventData配列を返す
 */
	public function setReventData($eventData, $insertStartTime, $insertEndTime) {
		$rEventData = $eventData;

		$rEventData['CalendarEvent']['id'] = null;		//新規登録用にidにnullセット

		$rEventData['CalendarEvent']['start_date'] = substr($insertStartTime, 0, 8);
		$rEventData['CalendarEvent']['start_time'] = substr($insertStartTime, 8);
		$rEventData['CalendarEvent']['dtstart'] = $insertStartTime;
		$rEventData['CalendarEvent']['end_date'] = substr($insertEndTime, 0, 8);
		$rEventData['CalendarEvent']['end_time'] = substr($insertEndTime, 8);
		$rEventData['CalendarEvent']['dtend'] = $insertEndTime;

		if (isset($eventData['CalendarEvent']['created_user'])) {
			$rEventData['CalendarEvent']['created_user'] = $eventData['CalendarEvent']['created_user'];
		}

		if (isset($eventData['CalendarEvent']['created'])) {
			$rEventData['CalendarEvent']['created'] = $eventData['CalendarEvent']['created'];
		}

		if (isset($eventData['CalendarEvent']['modified_user'])) {
			$rEventData['CalendarEvent']['modified_user'] = $eventData['CalendarEvent']['modified_user'];
		}

		if (isset($eventData['CalendarEvent']['modified'])) {
			$rEventData['CalendarEvent']['modified'] = $eventData['CalendarEvent']['modified'];
		}

		return $rEventData;
	}

/**
 * startDate,startTime,endDate,endTime生成
 *
 * @param string $sTime サーバー系sTime文字列(YmdHis)
 * @param string $eTime サーバー系eTime文字列(YmdHis)
 * @param string $byday byday サーバー系byday日文字列(YmdHis)
 * @param string $userTz userTz ユーザー系タイムゾーンID (Asia/Tokyoなど)
 * @param string &$startDate 生成したサーバー系startDate文字列
 * @param string &$startTime 生成したサーバー系startTime文字列
 * @param string &$endDate 生成したサーバー系endDate文字列
 * @param string &$endTime 生成したサーバー系endTime文字列
 * @return void
 */
	public function setStartDateTiemAndEndDateTime($sTime, $eTime, $byday, $userTz, &$startDate, &$startTime, &$endDate, &$endTime) {
		//INPUT引数のsTime, eTime, bydayはサーバー系なので、
		//まずは、それぞれをTZのユーザー系のYmdHisに変換する。
		$userStartTime = (new NetCommonsTime())->toUserDatetime(CalendarTime::calDt2dt($sTime));
		$userStartTime = CalendarTime::dt2calDt($userStartTime);
		$userEndTime = (new NetCommonsTime())->toUserDatetime(CalendarTime::calDt2dt($eTime));
		$userEndTime = CalendarTime::dt2calDt($userEndTime);
		$userByday = (new NetCommonsTime())->toUserDatetime(CalendarTime::calDt2dt($byday));
		$userByday = CalendarTime::dt2calDt($userByday);

		//カレンダー上（＝ユーザー系）の開始日の00:00:00のtimestamp取得
		$date = new DateTime('now', (new DateTimeZone($userTz)));	//ユーザーTZ系のDateTimeObj生成
		$date->setDate(substr($userStartTime, 0, 4),
			substr($userStartTime, 4, 2), substr($userStartTime, 6, 2));
		$date->setTime(0, 0, 0);
		$startTimestamp = $date->getTimestamp();

		//カレンダー上（＝ユーザー系）の終了日の00:00:00のtimestamp取得
		$date->setDate(substr($userEndTime, 0, 4),
			substr($userEndTime, 4, 2), substr($userEndTime, 6, 2));
		$date->setTime(0, 0, 0);
		$endTimestamp = $date->getTimestamp();

		//開始日と終了日の差分の日数(a)を算出
		$diffNum = ($endTimestamp - $startTimestamp) / 86400;

		//日付がbyday日で時刻が開始日時刻のタイムスタンプの、「サーバー系」のYmdとHisを取得する
		//
		$sdate = new DateTime('now', (new DateTimeZone($userTz)));	//ユーザーTZ系のDateTimeObj生成
		$sdate->setDate(substr($userByday, 0, 4),
			substr($userByday, 4, 2), substr($userByday, 6, 2));
		$sdate->setTime(substr($userStartTime, 8, 2),
			substr($userStartTime, 10, 2), substr($userStartTime, 12, 2));
		$sdate->setTimezone(new DateTimeZone('UTC'));	//サーバーTZに切り替える
		$startDate = $sdate->format('Ymd');	//サーバー系の開始日付時刻のYmd
		$startTime = $sdate->format('His');	//サーバー系の開始日付時刻のHis

		//月がbyday月、日がbyday日+差分日数(a)で時刻が終了日時刻のタイムスタンプの、「サーバー系」のYmdとHisを取得する
		//
		$edate = new DateTime('now', (new DateTimeZone($userTz)));	//ユーザーTZ系のDateTimeObj生成
		$edate->setDate(substr($userByday, 0, 4),
			substr($userByday, 4, 2), substr($userByday, 6, 2) + $diffNum);
		$edate->setTime(substr($userEndTime, 8, 2),
			substr($userEndTime, 10, 2), substr($userEndTime, 12, 2));
		$edate->setTimezone(new DateTimeZone('UTC'));	//サーバーTZに切り替える
		$endDate = $edate->format('Ymd');	//サーバー系の終了日付時刻のYmd
		$endTime = $edate->format('His');	//サーバー系の終了日付時刻のHis
	}

/**
 * RruleDataへのデータ設定
 *
 * @param array $planParams 予定パラメータ
 * @param array &$rruleData rruleデータ
 * @param string $mode mode insert時:self::CALENDAR_INSERT_MODE(デフォルト値) update時:self::CALENDAR_UPDATE_MODE
 * @return void
 */
	public function setRruleData($planParams, &$rruleData, $mode = self::CALENDAR_INSERT_MODE) {
		$params = array(
			'calendar_id' => 1,	//暫定
			'name' => '',
			'rrule' => '',
			'icalendar_uid' => CalendarSupport::generateIcalUid($planParams['start_date'], $planParams['start_time']),
			'icalendar_comp_name' => self::CALENDAR_PLUGIN_NAME,
			'room_id' => Current::read('Room.id'),
			//'language_id' => Current::read('Language.id'),
			//'status' => WorkflowComponent::STATUS_IN_DRAFT,
		);

		foreach ($planParams as $key => $val) {
			if (isset($params[$key])) {
				$params[$key] = $val;
			}
		}

		//レコード $rrule_data  の初期化と'CalendarRrule'キーセットはおわっているので省略
		//rruleDataに詰める。

		//idはcreate()の後なので、不要。
		$rruleData['CalendarRrule']['calendar_id'] = $params['calendar_id'];
		//keyは、Workflowが自動セット
		$rruleData['CalendarRrule']['name'] = $params['name'];
		$rruleData['CalendarRrule']['rrule'] = $params['rrule'];
		if ($mode === self::CALENDAR_INSERT_MODE) {
			$rruleData['CalendarRrule']['icalendar_uid'] = $params['icalendar_uid'];
			$rruleData['CalendarRrule']['icalendar_comp_name'] = $params['icalendar_comp_name'];
		}
		$rruleData['CalendarRrule']['room_id'] = $params['room_id'];
		////$rruleData['CalendarRrule']['language_id'] = $params['language_id'];
		////$rruleData['CalendarRrule']['status'] = $params['status'];
		//is_active,is_latestは、Workflowが自動セット ->　is_xxxは項目削除した。
		//create_user, created, modified_user, modifiedは、Trackableが自動セット
	}

/**
 * setPlanParams2Params
 *
 * planParamsからparamsへの設定
 *
 * @param array &$planParams 予定パラメータ
 * @param array &$params paramsパラメータ
 * @return void
 */
	public function setPlanParams2Params(&$planParams, &$params) {
		$keys = array(
			'title',
			'title_icon',
			'location',
			'contact',
			'description',
			'is_allday',
			'timezone_offset',
			'linked_model',
			'linked_content_key',
		);
		foreach ($keys as $key) {
			if (isset($planParams[$key])) {
				$params[$key] = $planParams[$key];
			}
		}
	}

/**
 * eventDataへのデータ設定
 *
 * @param array $planParams 予定パラメータ
 * @param array $rruleData rruleDataパラメータ
 * @param array &$eventData eventデータ
 * @return void
 */
	public function setEventData($planParams, $rruleData, &$eventData) {
		//初期化
		$params = array(
			'calendar_rrule_id' => $rruleData['CalendarRrule']['id'],	//外部キーをセット
			//keyは、Workflowが自動セット
			'room_id' => $rruleData['CalendarRrule']['room_id'],
			'language_id' => Current::read('Language.id'),
			'target_user' => Current::read('User.id'),
			'title' => '',
			'title_icon' => '',
			'location' => '',
			'contact' => '',
			'description' => '',
			'is_allday' => self::_OFF,
			'start_date' => $planParams['start_date'],
			'start_time' => $planParams['start_time'],
			'dtstart' => $planParams['start_date'] . $planParams['start_time'],
			'end_date' => $planParams['end_date'],
			'end_time' => $planParams['end_time'],
			'dtend' => $planParams['end_date'] . $planParams['end_time'],
			'timezone_offset' => $planParams['timezone_offset'],
			'status' => $planParams['status'],

			'linked_model' => '',
			'linked_content_key' => '',
		);

		$this->setPlanParams2Params($planParams, $params);

		//レコード $event_data  の初期化と'CalendarEvent'キーセットはおわっているので省略
		//$eventData = array();
		//$eventData['CalendarEvent'] = array();

		//eventを詰める。
		//$eventData['CalendarEvent']['id'] = null;		//create()の後なので、不要。
		$eventData['CalendarEvent']['calendar_rrule_id'] = $params['calendar_rrule_id'];
		$eventData['CalendarEvent']['room_id'] = $params['room_id'];
		$eventData['CalendarEvent']['language_id'] = $params['language_id'];
		$eventData['CalendarEvent']['target_user'] = $params['target_user'];
		$eventData['CalendarEvent']['title'] = $params['title'];
		$eventData['CalendarEvent']['title_icon'] = $params['title_icon'];
		$eventData['CalendarEvent']['is_allday'] = $params['is_allday'];
		$eventData['CalendarEvent']['start_date'] = $params['start_date'];
		$eventData['CalendarEvent']['start_time'] = $params['start_time'];
		$eventData['CalendarEvent']['dtstart'] = $params['dtstart'];
		$eventData['CalendarEvent']['end_date'] = $params['end_date'];
		$eventData['CalendarEvent']['end_time'] = $params['end_time'];
		$eventData['CalendarEvent']['dtend'] = $params['dtend'];
		$eventData['CalendarEvent']['timezone_offset'] = $params['timezone_offset'];
		$eventData['CalendarEvent']['status'] = $params['status'];

		$eventData['CalendarEvent']['location'] = $params['location'];
		$eventData['CalendarEvent']['contact'] = $params['contact'];
		$eventData['CalendarEvent']['description'] = $params['description'];

		//保存するモデルをここで替える
		$eventData['CalendarEventContent']['linked_model'] = $params['linked_model'];
		$eventData['CalendarEventContent']['linked_content_key'] = $params['linked_content_key'];
	}

/**
 * eventとrruleの両モデルをロードする。
 *
 * @param Model &$model モデル
 * @return void
 */
	public function loadEventAndRruleModels(Model &$model) {
		if (!isset($model->CalendarEvent)) {
			$model->loadModels([
				'CalendarEvent' => 'Calendars.CalendarEvent'
			]);
		}
		if (!isset($model->CalendarRrule)) {
			$model->loadModels([
				'CalendarRrule' => 'Calendars.CalendarRrule'
			]);
		}
	}
}
