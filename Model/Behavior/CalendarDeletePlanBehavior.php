<?php
/**
 * CalendarDeletePlan Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('CalendarAppBehavior', 'Calendars.Model/Behavior');	//プラグインセパレータ(.)とパスセバレータ(/)混在に注意
App::uses('CalendarRruleHandleBehavior', 'Calendars.Model/Behavior');

/**
 * CalendarDeletePlanBehavior
 *
 * @property array $calendarWdayArray calendar weekday array カレンダー曜日配列
 * @property array $editRrules editRules　編集ルール配列
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Model\Behavior
 */
class CalendarDeletePlanBehavior extends CalendarAppBehavior {

/**
 * use behaviors
 *
 * @var array
 */
	//public $actsAs = array(
	//	'Calendars.CalendarLinkEntry',
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
 * 予定の削除
 *
 * @param Model &$model 実際のモデル名
 * @param int $eventId CalendarEvent.id
 * @param string $editRrule editRrule デフォルト値 self::CALENDAR_PLAN_EDIT_THIS
 * @return 削除成功時 string CalendarEvent.Id   削除失敗時 InternalErrorExceptionを投げる。
 * @throws InternalErrorException
 */
	public function deletePlan(Model &$model, $eventId, $editRrule = self::CALENDAR_PLAN_EDIT_THIS) {
		//CalendarEventの対象データ取得
		$results = $this->getCalendarEventAndRrule($model, $eventId, $editRrule);

		$eventData = $rruleData = array();
		if (!is_array($results) || !isset($results['CalendarEvent'])) {
			$model->validationErrors = Hash::merge($model->validationErrors, $model->CalendarEvent->validationErrors);
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		$eventData['CalendarEvent'] = $results['CalendarEvent'];
		if (!is_array($results) || !isset($results['CalendarRrule'])) {
			//getCalendarEventAndRrule()の中では、CalendarEvent->find('first')を発行しているだけなので、CalendarEventモデルでＯＫ
			$model->validationErrors = Hash::merge($model->validationErrors, $model->CalendarEvent->validationErrors);
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		$rruleData['CalendarRrule'] = $results['CalendarRrule'];

		if (!isset($model->CalendarEventContent)) {
			$model->loadModels([
				'CalendarEventContent' => 'Calendars.CalendarEventContent'
			]);
		}
		$condtionds = array(
			$model->CalendarEventContent->alias . '.calendar_event_id' => $eventData['CalendarEvent']['id'],
		);
		$model->CalendarEventContent->deleteAll($condtionds, false);	//対応するcalendar_event_contentsを消す

		if (!isset($model->CalendarEventShareUser)) {
			$model->loadModels([
				'CalendarEventShareUser' => 'Calendars.CalendarEventShareUser'
			]);
		}
		$condtionds = array(
			$model->CalendarEventShareUser->alias . '.calendar_event_id' => $eventData['CalendarEvent']['id'],
		);
		$model->CalendarEventShareUser->deleteAll($condtionds, false);	//対応するcalendar_event_share_usersを消す

		//予定データの削除処理
		if ($editRrule === self::CALENDAR_PLAN_EDIT_ALL) {
			$this->deleteCalendarPlanEditAll($model, $eventId, $editRrule, $rruleData, $eventData);
		} elseif ($editRrule === self::CALENDAR_PLAN_EDIT_AFTER) {
			$this->deleteCalendarPlanEditAfter($model, $eventId, $editRrule, $rruleData, $eventData);
		} else {
			$this->deleteCalendarPlanEditThis($model, $eventId, $editRrule, $rruleData, $eventData);
		}
		return $eventId;
	}

/**
 * 全てのCalenarEventデータを編集（削除）する場合の処理
 *
 * @param Model &$model 実際のモデル名
 * @param int $eventId CalendarEvent.id
 * @param string $editRrule editRrule デフォルト値 self::CALENDAR_PLAN_EDIT_THIS
 * @param array $rruleData rruleData
 * @param array $eventData ststartendData
 * @return void
 * @throws InternalErrorException
 */
	public function deleteCalendarPlanEditAll(Model &$model, $eventId, $editRrule, $rruleData, $eventData) {
		$conditions = array($model->CalendarEvent->alias . '.calendar_rrule_id' => $eventData['CalendarEvent']['calendar_rrule_id']);

		if (!$model->CalendarEvent->deleteAll($conditions, true)) { //第２引数のcascadeをtrueにすることで、cakePHPのbelongsToでカスケードしているCalendarEventShareUser, CalendarEventContentも消す
			//deleteAll失敗
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
	}

/**
 * 現在のCalenarEventデータ以降を編集（削除）する場合の処理
 *
 * @param Model &$model 実際のモデル名
 * @param int $eventId CalendarEvent.id
 * @param string $editRrule editRrule デフォルト値 self::CALENDAR_PLAN_EDIT_THIS
 * @param array $rruleData rruleData
 * @param array $eventData eventData
 * @return void
 * @throws InternalErrorException
 */
	public function deleteCalendarPlanEditAfter(Model &$model, $eventId, $editRrule, $rruleData, $eventData) {
		$conditions = array(
			'.calendar_rrule_id' => $eventData['CalendarEvent']['calendar_rrule_id'],
			'.dtstart >=' => $eventData['CalendarEvent']['dtstart'],
		);

		if (!$model->CalendarEvent->deleteAll($conditions, true)) {	//第２引数のcascadeをtrueにすることで、cakePHPのbelongsToでカスケードしているCalendarEventShareUserもCalendarEventContent消す
			//deleteAll失敗
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		$rruleHandler = new CalendarRruleHandleBehavior();
		$rruleArr = $rruleHandler->parseRrule($rruleData['CalendarRrule']['rrule']);
		$dtstart = $eventData['CalendarEvent']['dtstart'];
		$timestamp = mktime(0, 0, 0, substr($dtstart, 4, 2), substr($dtstart, 6, 2), substr($dtstart, 0, 4));
		$rruleArr['UNTIL'] = date('Ymd', $timestamp) . 'T' . substr($dtstart, 8);	//iCalendar仕様の日付形式(Tつなぎ)にする。
		$rruleData['CalendarRrule']['rrule'] = $rruleHandler->concatRrule($rruleArr);	//rrule配列をrrule文字列にする。

		//CalendarRruleの更新準備
		if (!isset($model->CalendarRrule)) {
			$model->loadModels([
				'CalendarRrule' => 'Calendars.CalendarRrule'
			]);
		}
		$model->CalendarRrule->set($rruleData);
		if (!$model->CalendarRrule->validates()) {	//rruleDataをチェック
			$model->validationErrors = Hash::merge($model->validationErrors, $model->CalendarRrule->validationErrors);
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		if (!$model->CalendarRrule->save($rruleData, false)) {	//CalendarRruleの更新. 保存のみ
			$model->validationErrors = Hash::merge($model->validationErrors, $model->CalendarRrule->validationErrors);
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
	}

/**
 * このCalenarEventデータのみを編集（削除）する場合の処理
 *
 * @param Model &$model 実際のモデル名
 * @param int $eventId CalendarEvent.id
 * @param string $editRrule editRrule デフォルト値 self::CALENDAR_PLAN_EDIT_THIS
 * @param arry $rruleData rruleData
 * @param array $eventData eventData
 * @return void
 * @throws InternalErrorException
 */
	public function deleteCalendarPlanEditThis(Model &$model, $eventId, $editRrule, $rruleData, $eventData) {
		if (!$model->CalendarEvent->delete($eventId, true)) {	//第２引数をtrueにして、関連するcalendar_event_share_usersとcalendar_event_contentsも消す。
			//delete失敗
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
		if (!isset($model->CalendarEvent)) {
			$model->loadModels([
				'CalendarEvent' => 'Calendars.CalendarEvent'
			]);
		}

		$params = array(
			'conditions' => array($model->CalendarEvent->alias . '.id' => $eventId),
			'recursive' => 0,		//belongTo, hasOneの１跨ぎの関係までとってくる。
			'fields' => array('CalendarEvent.*', 'CalendarRrule.*'),
			'callbacks' => false
		);
		return $model->CalendarEvent->find('first', $params);
	}
}
