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
 * @param array $dtstartendData dtstartendデータ(CalendarCompDtstartendのモデルデータ)
 * @param string $startTime startTime 開始日付時刻文字列
 * @param string $endTime endTime 開始日付時刻文字列
 * @return array $rDtstartendData
 * @throws InternalErrorException
 */
	public function insert(Model &$model, $planParams, $rruleData, $dtstartendData, $startTime, $endTime) {
		$this->loadDtstartendAndRruleModels($model);
		$params = array(
			'conditions' => array('CalendarsCompRrule.id' => $dtstartendData['CalendarCompDtstartend']['calendar_comp_rrule_id']),
			'recursive' => (-1),
			'fields' => array('CalendarsCompRrule.*'),
			'callbacks' => false
		);
		if (empty($this->rruleData)) {
			//CalendarCompRruleのデータがないので初回アクセス
			//
			$rruleData = $model->CalendarCompRrule->find('first', $params);
			if (!is_array($rruleData) || !isset($rruleData['CalendarCompRrule'])) {
				$this->validationErrors = Hash::merge($this->validationErrors, $model->CalendarCompRrule->validationErrors);
				//throw new InternalErrorException(__d('Calendars', 'insert find error.'));
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
			$this->rruleData = $rruleData;	//CalendarCompRruleのデータを記録し、２度目以降に備える。
		}

		$insertStartTime = CalendarTime::timezoneDate($startTime, 1, 'YmdHis');
		$insertEndTime = CalendarTime::timezoneDate($endTime, 1, 'YmdHis');

		$rDtstartendData = $this->setRdtstartendData($dtstartendData, $insertStartTime, $insertEndTime); //dtstartendDataをもとにrDtstartendDataをつくり、モデルにセット
		$model->CalendarCompDtstartend->set($rDtstartendData);

		if (!$model->CalendarCompDtstartend->validates()) {	//rDtstartendDataをチェック
			$this->validationErrors = Hash::merge($this->validationErrors, $model->CalendarCompDtstartend->validationErrors);
			//throw new InternalErrorException(__d('Calendars', 'rDtstartend data check error.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		if (!$model->CalendarCompDtstartend->save($rDtstartendData, false)) { //保存のみ
			$this->validationErrors = Hash::merge($this->validationErrors, $model->CalendarCompDtstartend->validationErrors);
			//throw new InternalErrorException(__d('Calendars', 'rDtstartend data save error.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		$rDtstartendData['CalendarCompDtstartend']['id'] = $model->CalendarCompDtstartend->id; //採番されたidをdtstartendDataにセ>

		if ($rDtstartendData['CalendarCompDtstartend']['link_plugin'] !== '') {	//Task、施設予約のLinkデータの更新
			if (!$model->Behaviors->hasMethod('updateLink')) {
				$model->Behaviors->load('Calendars.CalendarLinkEntry');
			}
			$this->updateLink($model, $planParams, $rruleData, $rDtstartendData);
		}

		return $rDtstartendData;
	}

/**
 * rDtstartendDataへのデータ設定
 *
 * @param array $dtstartendData 元になるdtstartendData配列
 * @param string $insertStartTime insertStartTime 登録用開始日付時刻文字列
 * @param string $insertEndTime insertEndTime 登録用終了日付時刻文字列
 * @return array 実際に登録する$rDtstartendData配列を返す
 */
	public function setRdtstartendData($dtstartendData, $insertStartTime, $insertEndTime) {
		$rDtstartendData = $dtstartendData;
		$rDtstartendData['CalendarCompDtstartend']['id'] = null;		//新規登録用にidにnullセット

		$rDtstartendData['CalendarCompDtstartend']['start_date'] = substr($insertStartTime, 0, 8);
		$rDtstartendData['CalendarCompDtstartend']['start_time'] = substr($insertStartTime, 8);
		$rDtstartendData['CalendarCompDtstartend']['dtstart'] = $insertStartTime;
		$rDtstartendData['CalendarCompDtstartend']['end_date'] = substr($insertEndTime, 0, 8);
		$rDtstartendData['CalendarCompDtstartend']['end_time'] = substr($insertEndTime, 8);
		$rDtstartendData['CalendarCompDtstartend']['dtend'] = $insertEndTime;

		if (isset($dtstartendData['CalendarCompDtstartend']['created_user'])) {
			$rDtstartendData['CalendarCompDtstartend']['created_user'] = $dtstartendData['CalendarCompDtstartend']['created_user'];
		}

		if (isset($dtstartendData['CalendarCompDtstartend']['created'])) {
			$rDtstartendData['CalendarCompDtstartend']['created'] = $dtstartendData['CalendarCompDtstartend']['created'];
		}

		if (isset($dtstartendData['CalendarCompDtstartend']['modified_user'])) {
			$rDtstartendData['CalendarCompDtstartend']['modified_user'] = $dtstartendData['CalendarCompDtstartend']['modified_user'];
		}

		if (isset($dtstartendData['CalendarCompDtstartend']['modified'])) {
			$rDtstartendData['CalendarCompDtstartend']['modified'] = $dtstartendData['CalendarCompDtstartend']['modified'];
		}

		return $rDtstartendData;
	}

/**
 * startDate,startTime,endDate,endTime生成
 *
 * @param string $sTime sTime文字列(年月日時分秒）
 * @param string $eTime eTime文字列(年月日時分秒）
 * @param string &$startDate 生成したstartDate文字列
 * @param string &$startTime 生成したstartTime文字列
 * @param string &$endDate 生成したendDate伯父列
 * @param string &$endTime 生成したendTime文字列
 * @return void
 */
	public function setStartDateTiemAndEndDateTime($sTime, $eTime, &$startDate, &$startTime, &$endDate, &$endTime) {
		$startTimestamp = mktime(0, 0, 0, substr($sTime, 4, 2), substr($sTime, 6, 2), substr($sTime, 0, 4));
		$endTimestamp = mktime(0, 0, 0, substr($eTime, 4, 2), substr($eTime, 6, 2), substr($eTime, 0, 4));

		$diffNum = ($endTimestamp - $startTimestamp) / 86400;

		$timestamp = mktime(substr($sTime, 8, 2), substr($sTime, 10, 2), substr($sTime, 12, 2),
							substr($byday, 4, 2), substr($byday, 6, 2), substr($byday, 0, 4));
		$startDate = date('Ymd', $timestamp);
		$startTime = date('His', $timestamp);

		$timestamp = mktime(substr($eTime, 8, 2), substr($eTime, 10, 2), substr($eTime, 12, 2),
							substr($byday, 4, 2), substr($byday, 6, 2) + $diffNum, substr($byday, 0, 4));
		$endDate = date('Ymd', $timestamp);
		$endTime = date('His', $timestamp);
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
			'location' => '',
			'contact' => '',
			'description' => '',
			'rrule' => '',
			'room_id' => 1, //Current::read('Room.id'),		//ATODE
			'icalendar_comp_name' => self::CALENDAR_PLUGIN_NAME,
			'status' => WorkflowComponent::STATUS_IN_DRAFT,
			'language_id' => 1, //Current::read('Language.id'),	//ATODE
		);

		/*
		if (isset($planParams['location'])) {
			$params['location'] = $planParams['location'];
		}

		if (isset($planParams['contact'])) {
			$params['contact'] = $planParams['contact'];
		}

		if (isset($planParams['description'])) {
			$params['description'] = $planParams['description'];
		}

		if (isset($planParams['rrule'])) {
			$params['rrule'] = $planParams['rrule'];
		}

		if (isset($planParams['room_id'])) {
			$params['room_id'] = $planParams['room_id'];
		}

		if (isset($planParams['icalendar_comp_name'])) {
			$params['icalendar_comp_name'] = $planParams['icalendar_comp_name'];
		}

		if (isset($planParams['status'])) {
			$params['status'] = $planParams['status'];
		}

		if (isset($planParams['language_id'])) {
			$params['language_id'] = $planParams['language_id'];
		}
		*/

		foreach ($planParams as $key => $val) {
			if (isset($params[$key])) {
				$params[$key] = $val;
			}
		}

		//レコード $rrule_data  の初期化と'CalendarCompRrule'キーセットはおわっているので省略
		//$rruleData = array();
		//$rruleData['CalendarCompRrule'] = array();

		//rruleDataに詰める。
		//$rruleData['CalendarCompRrule']['id'] = null;		//create()の後なので、不要。
		$rruleData['CalendarCompRrule']['block_id'] = 1; //ATODE  Current::read('Block.id');	//Block.idを取得
		//keyは、Workflowが自動セット
		$rruleData['CalendarCompRrule']['name'] = '';		//名前はデフォルトなし
		$rruleData['CalendarCompRrule']['location'] = $params['location'];
		$rruleData['CalendarCompRrule']['contact'] = $params['contact'];
		$rruleData['CalendarCompRrule']['description'] = $params['description'];
		$rruleData['CalendarCompRrule']['rrule'] = $params['rrule'];
		if ($mode === self::CALENDAR_INSERT_MODE) {
			$rruleData['CalendarCompRrule']['icalendar_comp_name'] = $params['icalendar_comp_name'];
		}
		$rruleData['CalendarCompRrule']['room_id'] = $params['room_id'];
		$rruleData['CalendarCompRrule']['status'] = $params['status'];
		$rruleData['CalendarCompRrule']['language_id'] = $params['language_id'];
	}

/**
 * dtstartendDataへのデータ設定
 *
 * @param array $planParams 予定パラメータ
 * @param array $rruleData rruleDataパラメータ
 * @param array &$dtstartendData dtstartendデータ
 * @return void
 */
	public function setDtstartendData($planParams, $rruleData, &$dtstartendData) {
		$params = array(
			'calendar_comp_rrule_id' => $rruleData['CalendarCompRrule']['id'],	//外部キーをセット
			'room_id' => $rruleData['CalendarCompRrule']['room_id'],
			'language_id' => $rruleData['CalendarCompRrule']['language_id'],
			'target_user' => CakeSession::read('Calendars.target_user'),	//カレンダーの対象ユーザをSessionから取り出しセット
			'title' => '',
			'title_icon' => '',
			'is_allday' => self::_OFF,
			'start_date' => $planParams['start_date'],
			'start_time' => $planParams['start_time'],
			'dtstart' => $planParams['start_date'] . $planParams['start_time'],
			'end_date' => $planParams['end_date'],
			'end_time' => $planParams['end_time'],
			'dtend' => $planParams['end_date'] . $planParams['end_time'],
			'timezone_offset' => CakeSession::read('Calendars.timezone_offset'),
			'link_plugin' => '',
			'link_key' => '',
			'link_plugin_controller_action_name' => ''
		);
		if (isset($planParams['title'])) {
			$params['title'] = $planParams['title'];
		}
		if (isset($planParams['title_icon'])) {
			$params['title_icon'] = $planParams['title_icon'];
		}
		if (isset($planParams['is_allday'])) {
			$params['is_allday'] = $planParams['is_allday'];
		}
		if (isset($planParams['timezone_offset'])) {
			$params['timezone_offset'] = $planParams['timezone_offset'];
		}
		if (isset($planParams['link_plugin'])) {
			$params['link_plugin'] = $planParams['link_plugin'];
		}
		if (isset($planParams['link_key'])) {
			$params['link_key'] = $planParams['link_key'];
		}
		if (isset($planParams['link_plugin_controller_action_name'])) {
			$params['link_plugin_controller_action_name'] = $planParams['link_plugin_controller_action_name'];
		}

		//レコード $dtstartend_data  の初期化と'CalendarCompDtstartend'キーセットはおわっているので省略
		//$dtstartendData = array();
		//$dtstartendData['CalendarCompDtstartend'] = array();

		//dtstarendを詰める。
		//$dtstartendData['CalendarCompDtstartend']['id'] = null;		//create()の後なので、不要。
		$dtstartendData['CalendarCompDtstartend']['calendar_comp_rrule_id'] = $params['calendar_comp_rrule_id'];
		$dtstartendData['CalendarCompDtstartend']['room_id'] = $params['room_id'];
		$dtstartendData['CalendarCompDtstartend']['language_id'] = $params['language_id'];
		$dtstartendData['CalendarCompDtstartend']['target_user'] = $params['target_user'];
		$dtstartendData['CalendarCompDtstartend']['title'] = $params['title'];
		$dtstartendData['CalendarCompDtstartend']['title_icon'] = $params['title_icon'];
		$dtstartendData['CalendarCompDtstartend']['is_allday'] = $params['is_allday'];
		$dtstartendData['CalendarCompDtstartend']['start_date'] = $params['start_date'];
		$dtstartendData['CalendarCompDtstartend']['start_time'] = $params['start_time'];
		$dtstartendData['CalendarCompDtstartend']['dtstart'] = $params['dtstart'];
		$dtstartendData['CalendarCompDtstartend']['end_date'] = $params['end_date'];
		$dtstartendData['CalendarCompDtstartend']['end_time'] = $params['end_time'];
		$dtstartendData['CalendarCompDtstartend']['dtend'] = $params['dtend'];
		$dtstartendData['CalendarCompDtstartend']['timezone_offset'] = $params['timezone_offset'];
		$dtstartendData['CalendarCompDtstartend']['link_plugin'] = $params['link_plugin'];
		$dtstartendData['CalendarCompDtstartend']['link_key'] = $params['link_key'];
		$dtstartendData['CalendarCompDtstartend']['link_plugin_controller_action_name'] = $params['link_plugin_controller_action_name'];
	}

/**
 * dtstartendとrruleの両モデルをロードする。
 *
 * @param Model &$model モデル
 * @return void
 */
	public function loadDtstartendAndRruleModels(Model &$model) {
		if (!isset($model->CalendarCompDtstartend)) {
			$model->loadModels([
				'CalendarCompDtstartend' => 'Calendars.CalendarCompDtstartend'
			]);
		}
		if (!isset($model->CalendarCompRrule)) {
			$model->loadModels([
				'CalendarCompRrule' => 'Calendars.CalendarCompRrule'
			]);
		}
	}
}
