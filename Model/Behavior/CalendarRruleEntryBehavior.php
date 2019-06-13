<?php
/**
 * CalendarRruleEntry Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('CalendarAppBehavior', 'Calendars.Model/Behavior');
App::uses('CalendarRruleUtil', 'Calendars.Utility');

/**
 * CalendarRruleEntryBehavior
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Model\Behavior
 */
class CalendarRruleEntryBehavior extends CalendarAppBehavior {

/**
 * use behaviors
 *
 * @var array
 */
	//public $actsAs = array(
	//	'Calendars.CalendarRruleHandle',
	//	'Calendars.CalendarYearlyEntry',
	//);

/**
 * Default settings
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2015, NetCommons Project
 */
	protected $_defaults = array(
	);

/**
 * Rruleテーブルへの登録
 *
 * @param Model $model 実際のモデル名
 * @param array $planParams 予定パラメータ
 * @param array $rruleData rruleデータ
 * @param array $eventData eventデータ
 * @param int $createdUserWhenUpd createdUserWhenUpd
 * @return void
 * @throws InternalErrorException
 */
	public function insertRrule(Model $model, $planParams, $rruleData, $eventData,
		$createdUserWhenUpd = null) {
		if (isset($model->rrule)) {	//behaviorメソッドでrruleを渡すための工夫
			unset($model->rrule);
		}

		//引数ではなく、$modelのインスタンス変数としてセットする。
		$model->rrule = $planParams['rrule'];

		if (!is_array($model->rrule)) {	//$rrulea文字列を解析し配列化する。
			$model->rrule = (new CalendarRruleUtil())->parseRrule($model->rrule);
		}

		//CakeLog::debug("DBG: In insertRrule() rrule array[" . print_r($model->rrule, true) . "]");

		if (!(isset($model->CalendarEvent) && is_callable($model->CalendarEvent->create))) {
			$model->loadModels(['CalendarEvent' => 'Calendars.CalendarEvent']);
		}
		$params = array(
			'conditions' => array('CalendarEvent.id' => $eventData['CalendarEvent']['id']),
			'recursive' => 0, //(-1),
			//'fields' => array('CalendarEvent.*'),
			'callbacks' => false
		);
		$eventData = $model->CalendarEvent->find('first', $params);
		if (!is_array($eventData) || !isset($eventData['CalendarEvent'])) {
			$model->validationErrors = array_merge(
				$model->validationErrors, $model->CalendarEvent->validationErrors);
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		//////////////////////////////
		//ここのロジック(同じrrule_idをもつ兄弟eventの自分以外の全削除）について
		//新規の時は、そもそも消す対象がない
		//更新の時は、自分以外を消す（物理削除or除外フラグon)のは、NC3カレンダでは
		//insertRrule()に来る前に済ませているので、やはり意味がない。
		//よって、ここのロジックはOffする。
		//$conditions = array(
		//	$model->CalendarEvent->alias .
		//		'.calendar_rrule_id' => $eventData['CalendarEvent']['calendar_rrule_id'],
		//	$model->CalendarEvent->alias . '.id <>' => $eventData['CalendarEvent']['id'],
		//);
		//
		//if (!$model->CalendarEvent->deleteAll($conditions, false)) {
		//	$model->validationErrors = Hash::merge(
		//		$model->validationErrors, $model->CalendarEvent->validationErrors);
		//	throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		//}

		/////////////////////////////
		//周期性による、eventの順次登録
		//rruleのin/outは、$modelのインスタンス変数をつかっておこなう。
		$this->insertPriodEntry($model, $planParams, $rruleData, $eventData, $createdUserWhenUpd);
	}

/**
 * 周期性登録
 *
 * @param Model $model 実際のモデル名
 * @param array $planParams planParams
 * @param array $rruleData rruleData
 * @param array $startEventData eventデータ
 * @param int $createdUserWhenUpd createdUserWhenUpd
 * @return void
 */
	public function insertPriodEntry(Model $model, $planParams, $rruleData, $startEventData,
		$createdUserWhenUpd) {
		//CakeLog::debug("DBG: In insertPriodEntry(). i set model->rrule[INDEX] to 1.");

		$model->rrule['INDEX'] = 1;

		switch ($model->rrule['FREQ']) {
			case 'YEARLY':
				if (!$model->Behaviors->hasMethod('insertYearly')) {
					$model->Behaviors->load('Calendars.CalendarYearlyEntry');
				}
				$model->insertYearly($planParams, $rruleData, $startEventData, 1, 0, $createdUserWhenUpd);
				break;
			case 'MONTHLY':
				$this->_insertMonthlyPriodEntry($model, $planParams, $rruleData, $startEventData,
					$createdUserWhenUpd);
				break;
			case 'WEEKLY':
				if (!$model->Behaviors->hasMethod('insertWeekly')) {
					$model->Behaviors->load('Calendars.CalendarWeeklyEntry');
				}
				$model->insertWeekly($planParams, $rruleData, $startEventData, 1, $createdUserWhenUpd);
				break;
			case 'DAILY':
				if (!$model->Behaviors->hasMethod('insertDaily')) {
					$model->Behaviors->load('Calendars.CalendarDailyEntry');
				}

				//CakeLog::debug("DBGDBG: In insertPriodEntry() DAILY case. before insertDaily[" . print_r($planParams, true) . "] rruleData[" . print_r($rruleData, true) . "] startEventData[" . print_r($startEventData) . "]");

				$model->insertDaily($planParams, $rruleData, $startEventData, $createdUserWhenUpd);
				break;
		}
	}

/**
 * _insertMonthlyPriodEntry
 *
 * 月用周期性登録
 *
 * @param Model $model 実際のモデル名
 * @param array &$planParams planParams
 * @param array &$rruleData rruleData
 * @param array &$startEventData eventデータ
 * @param int &$createdUserWhenUpd createdUserWhenUpd
 * @return void
 */
	protected function _insertMonthlyPriodEntry(Model $model,
		&$planParams, &$rruleData, &$startEventData, &$createdUserWhenUpd) {
		if (!$model->Behaviors->hasMethod('insertMonthlyByMonthday')) {
			$model->Behaviors->load('Calendars.CalendarMonthlyEntry');
		}
		if (isset($model->rrule['BYMONTHDAY'])) {	//指定月のx日、y日
			$model->insertMonthlyByMonthday($planParams, $rruleData, $startEventData, 1, 1,
				$createdUserWhenUpd);
		} else {	//第ｘ週ｙ曜日
			$model->insertMonthlyByDay($planParams, $rruleData, $startEventData, 1, $createdUserWhenUpd);
		}
	}
}
