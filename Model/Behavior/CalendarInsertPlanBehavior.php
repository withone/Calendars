<?php
/**
 * CalendarInsertPlan Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('CalendarAppBehavior', 'Calendars.Model/Behavior');	//プラグインセパレータ(.)とパスセバレータ(/)混在に注意

/**
 * CalendarInsertPlanBehavior
 *
 * @property array $calendarWdayArray calendar weekday array カレンダー曜日配列
 * @property array $editRrules editRules　編集ルール配列
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Model\Behavior
 */
class CalendarInsertPlanBehavior extends CalendarAppBehavior {

/**
 * use behaviors
 *
 * @var array
 */
	//public $actsAs = array(
	//	'Calendars.CalendarLinkEntry',
	//	'Calendars.CalendarShareUserEntry',
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
 * 予定の追加
 *
 * @param Model &$model 実際のモデル名
 * @param array $planParams  予定パラメータ
 * @return int 追加成功時 $eventId(calendarEvent.id)を返す。追加失敗時 InternalErrorExceptionを投げる。
 * @throws InternalErrorException
 */
	public function insertPlan(Model &$model, $planParams) {
		$this->log("DBG: insertPlan start planParams[" . serialize($planParams) . "]", LOG_DEBUG);

		$this->arrangeData($planParams);

		$rruleData = $this->insertRruleData($model, $planParams); //rruleDataの１件登録

		$eventData = $this->insertEventData($model, $planParams, $rruleData);	//eventDataの１件登録
		if (!isset($eventData['CalendarEvent']['id'])) {
			//throw new InternalErrorException(__d('Calendars', 'get insertEventData.id error.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		$eventId = $eventData['CalendarEvent']['id'];

		if (!$model->Behaviors->hasMethod('insertShareUsers')) {
			$model->Behaviors->load('Calendars.CalendarShareUserEntry');
		}
		$model->insertShareUsers($model, $planParams['share_users'], $eventId);	//カレンダー共有ユーザ登録

		//関連コンテンツの登録
		if ($eventData['CalendarEventContent']['linked_model'] !== '') {
			if (!(isset($this->CalendarEventContent))) {
				$model->loadModels(['CalendarEventContent' => 'Calendar.CalendarEventContent']);
			}
			$this->CalendarEventContent->saveLinkedData($eventData);
		}

		if ($rruleData['CalendarRrule']['rrule'] !== '') {	//Rruleの登録
			if (!$model->Behaviors->hasMethod('insertRrule')) {
				$model->Behaviors->load('Calendars.CalendarRruleEntry');
			}
			$model->insertRrule($model, $planParams, $rruleData, $eventData);
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
		if (!isset($planParams['timezone_offset'])) { //timezone_offsetがなければ、カレンダーのセッションから取得する。
			$planParams['timezone_offset'] = CakeSession::read('Calendars.timezone_offset');
		}

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

		$this->arrangeShareUsers($planParams);
	}

/**
 * RruleDataへのデータ登録
 *
 * @param Model &$model モデル 
 * @param array $planParams 予定パラメータ
 * @return array $rruleDataを返す
 * @throws InternalErrorException
 */
	public function insertRruleData(Model &$model, $planParams) {
		if (!(isset($this->CalendarRrule) && is_callable($this->CalendarRrule->create))) {
			$model->loadModels([
				'CalendarRrule' => 'Calendars.CalendarRrule',
			]);
		}
		$rruleData = $model->CalendarRrule->create();	//rruleData保存のためにモデルをリセット(insert用)

		$this->setRruleData($planParams, $rruleData);		//rruleDataにplanParamデータを詰め、それをモデルにセット
		$model->CalendarRrule->set($rruleData);

		if (!$model->CalendarRrule->validates()) {		//rruleDataをチェック
			$this->validationErrors = Hash::merge($this->validationErrors, $model->CalendarRrule->validationErrors);
			//throw new InternalErrorException(__d('Calendars', 'Rrule data check error.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		if (!$model->CalendarRrule->save($rruleData, false)) {	//保存のみ
			$this->validationErrors = Hash::merge($this->validationErrors, $model->CalendarRrule->validationErrors);
			//throw new InternalErrorException(__d('Calendars', 'Rrule data save error.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		$rruleData['CalendarRrule']['id'] = $model->CalendarRrule->id;	//採番されたidをrruleDataにセットしておく

		return $rruleData;
	}

/**
 * EventDataへのデータ登録
 *
 * @param Model &$model モデル 
 * @param array $planParams 予定パラメータ
 * @param array $rruleData rruleデータ
 * @return array $eventDataを返す
 * @throws InternalErrorException
 */
	public function insertEventData(Model &$model, $planParams, $rruleData) {
		if (!(isset($this->CalendarEvent) && is_callable($this->CalendarEvent->create))) {
			$model->loadModels([
				'CalendarEvent' => 'Calendars.CalendarEvent',
			]);
		}
		$eventData = $model->CalendarEvent->create();	//eventData保存のためにモデルをリセット(insert用)

		$this->setEventData($planParams, $rruleData, $eventData);	//eventDataにplanParamデータを詰め、それをモデルにセット
		$model->CalendarEvent->set($eventData);

		if (!$model->CalendarEvent->validates()) {		//eventDataをチェック
			$this->validationErrors = Hash::merge($this->validationErrors, $model->CalendarEvent->validationErrors);
			//throw new InternalErrorException(__d('Calendars', 'Event data for insert check error.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		if (!$model->CalendarEvent->save($eventData, false)) {	//保存のみ
			$this->validationErrors = Hash::merge($this->validationErrors, $model->CalendarEvent->validationErrors);
			//throw new InternalErrorException(__d('Calendars', 'Event data for insert save error.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		$eventData['CalendarEvent']['id'] = $model->CalendarEvent->id;	//採番されたidをeventDataにセットしておく
		return $eventData;
	}

/**
 * shareUser変数を整える
 *
 * @param array &$planParams planParamsパラメータ
 * @return void
 * @throws InternalErrorException
 */
	public function arrangeShareUsers(&$planParams) {
		if (!isset($planParams['share_users'])) {
			$planParams['share_users'] = null;
			return;
		}
		if (!is_null($planParams['share_users']) && !is_string($planParams['share_users']) && !is_array($planParams['share_users'])) {
			//throw new InternalErrorException(__d('Calendars', 'share_users must be null or string or array.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		$planParams['share_users'] = is_string($planParams['share_users']) ? array($planParams['share_users']) : $planParams['share_users'];
	}
}
