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
		'calendarCompRruleModel' => 'Calendars.CalendarCompRrule',
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
 * @param int $dtstartendId CalendarCompDtstarend.id
 * @param string $editRrule editRrule デフォルト値 self::CALENDAR_PLAN_EDIT_THIS
 * @return 削除成功時 string CalendarCompDtstartend.Id   削除失敗時 InternalErrorExceptionを投げる。
 * @throws InternalErrorException
 */
	public function deletePlan(Model &$model, $dtstartendId, $editRrule = self::CALENDAR_PLAN_EDIT_THIS) {
		//CalendarCompDtstartendの対象データ取得
		$results = $this->getCalendarCompDtstartendAndRrule($model, $dtstartendId, $editRrule);

		$dtstartendData = $rruleData = array();
		if (!is_array($results) || !isset($results['CalendarCompDtstartend'])) {
			$this->validationErrors = Hash::merge($this->validationErrors, $model->CalendarCompDtstartend->validationErrors);
			//throw new InternalErrorException(__d('Calendars', 'find result at dtstartend error.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		$dtstartendData['CalendarCompDtstartend'] = $resutls['CalendarCompDtstartend'];
		if (!is_array($results) || !isset($results['CalendarCompRrule'])) {
			//getCalendarCompDtstartendAndRrule()の中では、CalendarCompDtstartend->find('first')を発行しているだけなので、DtstartendモデルでＯＫ
			$this->validationErrors = Hash::merge($this->validationErrors, $model->CalendarCompDtstartend->validationErrors);
			//throw new InternalErrorException(__d('Calendars', 'find result at rrule error.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		$rruleData['CalendarCompRrule'] = $resutls['CalendarCompRrule'];

		if ($dtstartendData['CalendarCompDtstartend']['link_plugin'] !== '') {	//Task、施設予約のLinkデータのクリア
			if ($model->Behaviors->hasMethod('updateLink')) {
				$model->Behaviors->load('Calendars.CalendarLinkEntry');
			}
			$model->updateLink($model, $rruleData, $dtstartendData, self::CALENDAR_LINK_CLEAR);
		}

		//予定データの削除処理
		if ($editRrule === self::CALENDAR_PLAN_EDIT_ALL) {
			$this->deleteCalendarPlanEditAll($model, $dtstartendId, $editRrule, $rruleData, $dtstartendData);
		} elseif ($editRrule === self::CALENDAR_PLAN_EDIT_AFTER) {
			$this->deleteCalendarPlanEditAfter($model, $dtstartendId, $editRrule, $rruleData, $dtstartendData);
		} else {
			$this->deleteCalendarPlanEditThis($model, $dtstartendId, $editRrule, $rruleData, $dtstartendData);
		}
		return $dtstartendId;
	}

/**
 * 全てのCalenarCompDtstartendデータを編集（削除）する場合の処理
 *
 * @param Model &$model 実際のモデル名
 * @param int $dtstartendId CalendarCompDtstarend.id
 * @param string $editRrule editRrule デフォルト値 self::CALENDAR_PLAN_EDIT_THIS
 * @param array $rruleData rruleData
 * @param array $dtstartendData ststartendData
 * @return void
 * @throws InternalErrorException
 */
	public function deleteCalendarPlanEditAll(Model &$model, $dtstartendId, $editRrule, $rruleData, $dtstartendData) {
		$conditions = array('CalendarsCompDtstartend.calendar_comp_rrule_id' => $dtstartendData['CalendarCompDtstartend']['calendar_comp_rrule_id']);
		$params = array(
			'conditions' => $conditions,
			'recursive' => (-1),
			'fields' => array('CalendarCompDtstartend.*'),
			'callbacks' => false
		);
		$results = $model->CalendarCompDtstartend->find('all', $params);
		foreach ($results as $result) {
			if ($result['CalendarCompDtstartend']['link_plugin'] !== '') {	//Task、施設予約のLinkデータのクリア
				$model->updateLink($model, $rruleData, $result, self::CALENDAR_LINK_CLEAR);
			}
		}

		if (!$model->CalendarCompDtstartend->deleteAll($conditions, true)) { //第２引数のcascadeをtrueにすることで、cakePHPのbelongsToでカスケードしているCaelndarCompDtstartendShareUserも消す
			//deleteAll失敗
			//throw new InternalErrorException(__d('Calendars', 'delete all error.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
	}

/**
 * 現在のCalenarCompDtstartendデータ以降を編集（削除）する場合の処理
 *
 * @param Model &$model 実際のモデル名
 * @param int $dtstartendId CalendarCompDtstarend.id
 * @param string $editRrule editRrule デフォルト値 self::CALENDAR_PLAN_EDIT_THIS
 * @param array $rruleData rruleData
 * @param array $dtstartendData dtstartendData
 * @return void
 * @throws InternalErrorException
 */
	public function deleteCalendarPlanEditAfter(Model &$model, $dtstartendId, $editRrule, $rruleData, $dtstartendData) {
		$conditions = array(
			'CalendarsCompDtstartend.calendar_comp_rrule_id' => $dtstartendData['CalendarCompDtstartend']['calendar_comp_rrule_id'],
			'CalendarsCompDtstartend.dtstart >=' => $dtstartendData['CalendarCompDtstartend']['dtstart'],
		);
		$params = array(
			'conditions' => $conditions,
			'recursive' => (-1),
			'fields' => array('CalendarCompDtstartend.*'),
			'callbacks' => false
		);
		$results = $model->CalendarCompDtstartend->find('all', $params);
		foreach ($results as $result) {
			if ($result['CalendarCompDtstartend']['link_plugin'] !== '') {	//Task、施設予約のLinkデータのクリア
				$model->updateLink($model, $rruleData, $result, self::CALENDAR_LINK_CLEAR);
			}
		}

		if (!$model->CalendarCompDtstartend->deleteAll($conditions, true)) {	//第２引数のcascadeをtrueにすることで、cakePHPのbelongsToでカスケードしているCaelndarCompDtstartendShareUserも消す
			//deleteAll失敗
			//throw new InternalErrorException(__d('Calendars', 'delete all error.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		$rruleHandler = new CalendarRruleHandleBehavior();
		$rruleArr = $rruleHandler->parseRrule($rruleData['CalendarCompRrule']['rrule']);
		$dtstart = $dtstartendData['CalendarCompDtstartend']['dtstart'];
		$timestamp = mktime(0, 0, 0, substr($dtstart, 4, 2), substr($dtstart, 6, 2), substr($dtstart, 0, 4));
		$rruleArr['UNTIL'] = date('Ymd', $timestamp) . 'T' . substr($dtstart, 8);	//iCalendar仕様の日付形式(Tつなぎ)にする。
		$rruleData['CalendarCompRrule']['rrule'] = $rruleHandler->concatRrule($rruleArr);	//rrule配列をrrule文字列にする。

		//CalendarCompRruleの更新準備
		if (!isset($model->CalendarCompRrule)) {
			$model->loadModels([
				'CalendarCompRrule' => 'Calendars.CalendarCompRrule'
			]);
		}
		$model->CalendarCompRrule->set($rruleData);
		if (!$model->CalendarCompRrule->validates()) {	//rruleDataをチェック
			$this->validationErrors = Hash::merge($this->validationErrors, $model->CalendarCompRrule->validationErrors);
				//throw new InternalErrorException(__d('Calendars', 'Rrule data check error.'));
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		if (!$model->CalendarCompRrule->save($rruleData, false)) {	//CalendarCompRruleの更新. 保存のみ
			$this->validationErrors = Hash::merge($this->validationErrors, $model->CalendarCompRrule->validationErrors);
			//throw new InternalErrorException(__d('Calendars', 'Rrule data save error.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
	}

/**
 * このCalenarCompDtstartendデータのみを編集（削除）する場合の処理
 *
 * @param Model &$model 実際のモデル名
 * @param int $dtstartendId CalendarCompDtstarend.id
 * @param string $editRrule editRrule デフォルト値 self::CALENDAR_PLAN_EDIT_THIS
 * @param arry $rruleData rruleData
 * @param array $dtstartendData ststarendData
 * @return void
 * @throws InternalErrorException
 */
	public function deleteCalendarPlanEditThis(Model &$model, $dtstartendId, $editRrule, $rruleData, $dtstartendData) {
		if (!$model->CalendarCompDtstartend->delete($dtstartendId, false)) {
			//delete失敗
			//throw new InternalErrorException(__d('Calendars', 'delete error.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
	}

/**
 * CalendarCompDtstartendの対象データ取得
 *
 * @param Model &$model 実際のモデル名
 * @param int $dtstartendId CalendarCompDtstarend.id
 * @param string $editRrule editRrule デフォルト値 self::CALENDAR_PLAN_EDIT_THIS
 * @return 成功時 array 条件にマッチするCalendarCompDtstartendDataとそのbelongsTo,hasOne関係のデータ（実際には、CalendarCompRruleData), 失敗時 空配列
 */
	public function getCalendarCompDtstartendAndRrule(Model &$model, $dtstartendId, $editRrule) {
		if (!isset($model->CalendarCompDtstartend)) {
			$model->loadModels([
				'CalendarCompDtstartend' => 'Calendars.CalendarCompDtstartend'
			]);
		}

		$params = array(
			'conditions' => array('CalendarsCompDtstartend.id' => $dtstartendId),
			'recursive' => 0,		//belongTo, hasOneの１跨ぎの関係までとってくる。
			'fields' => array('CalendarCompDtstartend.*', 'CalendarCompRrule.*'),
			'callbacks' => false
		);
		return $model->CalendarCompDtstartend->find('first', $params);
	}
}
