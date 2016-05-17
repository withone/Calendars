<?php
/**
 * CalendarUpdatePlan Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('CalendarAppBehavior', 'Calendars.Model/Behavior');
//App::uses('CalendarRruleHandleBehavior', 'Calendars.Model/Behavior');
App::uses('CalendarRruleUtil', 'Calendars.Utility');

/**
 * CalendarUpdatePlanBehavior
 *
 * @property array $calendarWdayArray calendar weekday array カレンダー曜日配列
 * @property array $editRrules editRules　編集ルール配列
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Model\Behavior
 */
class CalendarUpdatePlanBehavior extends CalendarAppBehavior {

/**
 * use behaviors
 *
 * @var array
 */
	//public $actsAs = array(
	//	'Calendars.CalendarLinkEntry',
	//	'Calendars.CalendarInsertPlan',
	//	'Calendars.CalendarRruleEntry',
	//	'Calendars.CalendarRruleHandle',
	//);

/**
 * Default settings
 *
 * VeventTime(+VeventRRule)の値自動変更
 * registered_into to calendar_information
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2015, NetCommons Project
 */
	protected $_defaults = array(
		'calendarRruleModel' => 'Calendars.CalendarRrule',
		'fields' => array(
			'registered_into' => 'calendar_information',
			),
		);
		//上記のfields定義は、以下の意味です。
		//   The (event|todoplugin|journal) was registerd into the calendar information.
		// ＝イベント(またはToDoまたは日報)が予定表の情報に登録されました。

/**
 * 予定の変更
 *
 * @param Model &$model 実際のモデル名
 * @param int $eventId CalendarEvent.id
 * @param array $planParams  予定パラメータ
 * @param string $editRrule editRrule デフォルト値 self::CALENDAR_PLAN_EDIT_THIS
 * @return 変更成功時 int calendarEventId
 */
	public function updatePlan(Model &$model, $eventId, $planParams, $editRrule = self::CALENDAR_PLAN_EDIT_THIS) {
		$this->arrangeData($planParams);

		//CalendarEventの対象データ取得
		$this->loadEventAndRruleModels($model);

		$results = $this->getCalendarEventAndRrule($model, $eventId, $editRrule);
		if (empty($results)) {
			return $eventId;	//対象が無い場合、成功したとみなし、$eventIdを返す。
		}

		//対象となるデータを$eventData、$rruleDataそれぞれにセット
		$eventData = $rruleData = array();

		list($eventData, $rruleData) = $this->setEventDataAndRruleData($model, $results, $eventData, $rruleData);

		$rruleKey = $rruleData['CalendarRrule']['key'];
		if (!isset($planParams['timezone_offset'])) { //timezone_offsetがなければ、calendar_eventテーブルからセットする。
			$planParams['timezone_offset'] = $eventData['CalendarEvent']['timezone_offset'];
		}

		//Rruleデータの全更新、指定以降更新、追加処理
		if ($editRrule === self::CALENDAR_PLAN_EDIT_ALL) {
			$this->setRruleData($planParams, $rruleData, self::CALENDAR_UPDATE_MODE);
			$this->updateRruleDataAll($model, $planParams, $rruleData);
		} elseif ($editRrule === self::CALENDAR_PLAN_EDIT_AFTER) {
			$this->setRruleData($planParams, $rruleData, self::CALENDAR_UPDATE_MODE);
			list($rruleData, $eventData) = $this->updatePlanByAfter($model, $eventId, $rruleKey, $planParams, $rruleData, $eventData);
		} else {
			if (!$model->Behaviors->hasMethod('insertRruleData')) {
				$model->Behaviors->load('Calendars.CalendarInsertPlan');
			}
			$rruleData = $model->insertRruleData($planParams);	//rruleDataの１件登録.
																//(CalendarInsertPlanBehaviorのメソッドを利用).
																//rruleDataの中身をここで新rruleDataで上書きする.
		}

		$this->setEventData($planParams, $rruleData, $eventData);
		$eventData = $this->updateDtstartData($model, $planParams, $rruleData, $eventData);

		if ($editRrule !== self::CALENDAR_PLAN_EDIT_THIS) {
			//新しく作った、または更新したrruleDataのrruleを改めて以下で設定(更新)している。
			if (!$model->Behaviors->hasMethod('insertRrule')) {
				$model->Behaviors->load('Calendars.CalendarRruleEntry');
			}
			$model->insertRrule($planParams, $rruleData, $eventData);
		}

		return $eventId;
	}

/**
 * $planParamsデータを整える
 *
 * @param array &$planParams planParamsデータ
 * @return void
 * @throws InternalErrorException
 */
	public function arrangeData(&$planParams) {
		//if (!isset($planParams['timezone_offset'])) { //timezone_offsetがなければ、カレンダーのセッションから取得する。
		//	$planParams['timezone_offset'] = CakeSession::read('Calendars.timezone_offset');
		//}

		if (!isset($planParams['start_date']) && !isset($planParams['start_time'])) { //開始日付と開始時刻は必須
			//throw new InternalErrorException(__d('Calendars', 'No start_date or start_time'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		if (!isset($planParams['end_date']) && !isset($planParams['end_time'])) { //終了日付と終了時刻は必須
			//throw new InternalErrorException(__d('Calendars', 'No end_date or end_time.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		if (!isset($planParams['status'])) { //statusは必須
			//throw new InternalErrorException(__d('Calendars', 'status is required.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		if (!isset($planParams['language_id'])) { //language_idは必須
			//throw new InternalErrorException(__d('Calendars', 'language_id is required.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
	}

/**
 * CalendarEventの対象データ取得
 *
 * @param Model &$model 実際のモデル名
 * @param int $eventId CalendarEvent.id
 * @param string $editRrule editRrule デフォルト値 self::CALENDAR_PLAN_EDIT_THIS
 * @return 成功時 array 条件にマッチするCalendarEventDataとそのbelongsTo,hasOne関係のデータ（実際には、CalendarRruleData), 失敗時 空配列
 */
	public function getCalendarEventAndRrule(Model &$model, $eventId, $editRrule) {
		$params = array(
			'conditions' => array('CalendarEvent.id' => $eventId),
			'recursive' => 0,		//belongTo, hasOneの１跨ぎの関係までとってくる。
			'fields' => array('CalendarEvent.*', 'CalendarRrule.*'),
			'callbacks' => false
		);
		return $model->CalendarEvent->find('first', $params);
	}

/**
 * RruleDataへのデータをdateへセット
 *
 * @param array $rruleData rruleData
 * @param array &$data data
 * @return void
 */
	public function setRruleData2Data($rruleData, &$data) {
		//$data['CalendarRrule']['location'] = $rruleData['CalendarRrule']['location'];
		//$data['CalendarRrule']['contact'] = $rruleData['CalendarRrule']['contact'];
		//$data['CalendarRrule']['description'] = $rruleData['CalendarRrule']['description'];
		$data['CalendarRrule']['rrule'] = $rruleData['CalendarRrule']['rrule'];
		$data['CalendarRrule']['room_id'] = $rruleData['CalendarRrule']['room_id'];
		$data['CalendarRrule']['status'] = $rruleData['CalendarRrule']['status'];
		//$data['CalendarRrule']['language_id'] = $rruleData['CalendarRrule']['language_id'];
	}

/**
 * RruleDataのデータ更新
 *
 * @param Model &$model モデル 
 * @param array $planParams 予定パラメータ
 * @param array $rruleData rruleData
 * @return void
 * @throws InternalErrorException
 */
	public function updateRruleDataAll(Model &$model, $planParams, $rruleData) {
		if (!(isset($this->CalendarRrule))) {
			$model->loadModels([
				'CalendarRrule' => 'Calendars.CalendarRrule',
			]);
		}

		//updateAllだとmodifiedを更新してくれないので、find+saveで実現する。
		$conditions = array('CalendarRrule.key' => $rruleData['CalendarRrule']['key']);
		$params = array(
			'conditions' => $conditions,
			'recursive' => (-1),
			'fields' => array('CalendarRrule.*'),
			'callbacks' => false
		);
		$results = $model->Task->find('all', $params);
		if (is_array($results) && count($results) > 0) {
			foreach ($results as $data) {
				$this->setRruleData2Data($rruleData, $data);
				if (!$model->CalendarRrule->save($data)) {	//validateもここで走る
					$model->validationErrors = Hash::merge($model->validationErrors, $model->CalendarRrule->validationErrors);
					//throw new InternalErrorException(__d('Calendars', 'Task plugin save error.'));
					throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
				}
			}
		}
	}

/**
 * EventDataのデータ更新
 *
 * @param Model &$model モデル 
 * @param array $planParams 予定パラメータ
 * @param array $rruleData rruleデータ
 * @param array $eventData eventデータ
 * @return array $eventData 変更後の$eventDataを返す
 * @throws InternalErrorException
 */
	public function updateDtstartData(Model &$model, $planParams, $rruleData, $eventData) {
		if (!(isset($this->CalendarEvent) && is_callable($this->CalendarEvent->create))) {
			$model->loadModels([
				'CalendarEvent' => 'Calendars.CalendarEvent',
			]);
		}

		$this->CalendarEvent->set($eventData);
		$eventId = $eventData['CalendarEvent']['id'];	//update対象のststartendIdを退避

		if (!$this->CalendarEvent->validates()) {		//eventDataをチェック
			$model->validationErrors = Hash::merge($model->validationErrors, $this->CalendarEvent->validationErrors);
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		if (!$this->CalendarEvent->save($eventData, false)) {	//保存のみ	//aaaaa
			$model->validationErrors = Hash::merge($model->validationErrors, $this->CalendarEvent->validationErrors);
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		if ($eventId !== $this->CalendarEvent->id) {
			//insertではなくupdateでなくてはならないのに、insertになってしまった。(つまりid値が新しくなってしまった）
			//throw new InternalErrorException(__d('Calendars', 'insert happened error.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		$eventData['CalendarEvent']['id'] = $this->CalendarEvent->id;	//採番されたidをeventDataにセットしておく

		$this->updateShareUsers($model, $planParams['share_users'], $eventId); //カレンダー共有ユーザ更新

		return $eventData;
	}

/**
 * 指定eventデータ以降の予定の変更
 *
 * @param Model &$model 実際のモデル名
 * @param int $eventId CalendarEvent.id
 * @param string $rruleKey rruleKey
 * @param array $planParams  予定パラメータ
 * @param array $rruleData rruleData
 * @param array $eventData eventData
 * @return array array($rruleData, $eventData)を返す。
 * @throws InternalErrorException
 */
	public function updatePlanByAfter(Model &$model, $eventId, $rruleKey, $planParams, $rruleData, $eventData) {
		//まずは、指定日付以降で自分以外の同一rruleIdを親にもつeventDataをすべて消す。
		$conditions = array(
			'CalendarEvent.calendar_rrule_id' => $eventData['CalendarEvent']['calendar_rrule_id'],
			'CalendarEvent.dtstart >=' => $eventData['CalendarEvent']['dtstart'],
			'CalendarEvent.id <>' => $eventId,
		);
		if (!$model->CalendarEvent->deleteAll($conditions, true)) {	//第２引数のcascadeをtrueにして、このeventDataに依存しているCalendarEventShareUserデータもすべて消す。
			//deleteAll失敗
			//throw new InternalErrorException(__d('Calendars', 'delete all error.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		//消した後、自分以外の同一rruleIdを親にもつeventDataの件数を調べる。
		$params = array(
			'conditions' => array(
				'CalendarEvent.calendar_rrule_id' => $eventData['CalendarEvent']['calendar_rrule_id'],
				'CalendarEvent.id <>' => $eventId,
			),
		);
		$count = $model->CalendarEvent->find('count', $params);
		if (!is_int($count)) {	//整数以外が返ってきたらエラー
			//throw new InternalErrorException(__d('Calendars', 'find count error.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		if ($count === 0) {
			//今の親rruleDataは、自分(eventData)以外の子を持たなくなった。
			//（自分の新しい親rruleDataをこの後つくるので）現在の親rruleDataは消す。
			//
			$conditions = array(
			);
			if (!$model->CalendarRrule->delete($eventData['CalendarEvent']['calendar_rrule_id'], false)) {
				//delete失敗
				//throw new InternalErrorException(__d('Calendars', 'delete error.'));
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
		} else {
			//今の親rruleDataは、自分(eventData)以外の子（時間軸では自分より前の時間）を持っている。
			//なので、今の親rruleDataのrruleのUNTIL値を「自分の直前まで」に書き換えて、
			//自分を今の親rruleDataの管理下から切り離す。(自分の新しい親rruleDataはこのあと作る）
			//

			//親のrruleDataはすでに取得しているので、rrule文字列はすぐに取得できる。
			$rruleUtilObj = new CalendarRruleUtil();
			$rruleArr = $rruleUtilObj->parseRrule($rruleData['CalendarRrule']['rrule']);

			//以下２行は冗長とおもわれる。取り出して上書きしても順番ふくめ変わらないので外す。
			//$freq = $rruleArr['FREQ'];
			//$rruleArr['FREQ'] = $freq;
			$timestamp = mktime(0, 0, 0,
						substr($planParams['dtstart'], 4, 2),
						substr($planParams['dtstart'], 6, 2),
						substr($planParams['dtstart'], 0, 4));
			$rruleArr['UNTIL'] = date('Ymd', $timestamp) . 'T' . substr($planParams['dtstart'], 8);	//UNTILを自分の直前までにする。
			$rruleBeforeStr = $rruleUtilObj->concatRrule($model, $rruleArr);

			//今のrruleDataデータのrrule文字列を書き換える。
			$rruleDataBefore = $rruleData;
			$rruleDataBefore['CalendarRrule']['rrule'] = $rruleBeforeStr;
			$model->CalendarRrule->clear();
			if (!$model->CalendarRrule->save($rruleDataBefore, false)) {	//rruleDataNowのidは、現rruleDataのidであるので、更新となる。
				//save失敗
				//throw new InternalErrorException(__d('Calendars', 'save error.'));
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
		}

		//新しい親rruleDataを登録する。
		if (!$model->Behaviors->hasMethod('insertRruleData')) {
			$model->Behaviors->load('Calendars.CalendarInsertPlan');
		}
		$rruleData = $model->insertRruleData($planParams);	//rruleDataの１件登録.
															//(CalendarInsertPlanBehaviorのメソッドを利用)
															//rruleDataは新rruleDataで上書きしている。

		//新しく親(insertされた)CalendarRruleのidは $rruleData のidにセットされている。

		return array($rruleData, $eventData);
	}

/**
 * resutlsよりeventDataとrruleDataに値セット
 *
 * @param Model &$model モデル
 * @param array $results results
 * @param array $eventData eventData
 * @param array $rruleData rruleData
 * @return array array($eventData, $rruleData)を返す
 * @throws InternalErrorException
 */
	public function setEventDataAndRruleData(Model &$model, $results, $eventData, $rruleData) {
		if (!is_array($results) || !isset($results['CalendarEvent'])) {
			$model->validationErrors = Hash::merge($model->validationErrors, $model->CalendarEvent->validationErrors);
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		$eventData['CalendarEvent'] = $resutls['CalendarEvent'];
		if (!is_array($results) || !isset($results['CalendarRrule'])) {
			$model->validationErrors = Hash::merge($model->validationErrors, $model->CalendarEvent->validationErrors);
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		$rruleData['CalendarRrule'] = $resutls['CalendarRrule'];

		return array($eventData, $rruleData);
	}
}
