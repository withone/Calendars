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
 * @param Model &$model 実際のモデル名
 * @param array $planParams 予定パラメータ
 * @param array $rruleData rruleデータ
 * @param array $eventData eventデータ
 * @return void
 * @throws InternalErrorException
 */
	public function insertRrule(Model &$model, $planParams, $rruleData, $eventData) {
		if (isset($model->rrule)) {	//behaviorメソッドでrruleを渡すための工夫
			unset($model->rrule);
		}
		$model->rrule = $planParams['rrule'];	//引数ではなく、$modelのインスタンス変数としてセットする。

		if (!is_array($model->rrule)) {	//$rrulea文字列を解析し配列化する。
			if (!$model->Behaviors->hasMethod('parseRrule')) {
				$model->Behaviors->load('Calendars.CalendarRruleHandle');
			}
			$model->rrule = $model->parseRrule($model->rrule);
		}

		if (!(isset($model->CalendarEvent) && is_callable($model->CalendarEvent->create))) {
			$model->loadModels(['CalendarEvent' => 'Calendars.CalendarEvent']);
		}
		$params = array(
			'conditions' => array('CalendarsEvent.id' => $eventData['CalendarEvent']['id']),
			'recursive' => (-1),
			'fields' => array('CalendarsEvent.*'),
			'callbacks' => false
		);
		$rruleData = $model->CalendarEvent->find('all', $params);
		if (!is_array($startEventData) || !isset($startEventData['CalendarEvent'])) {
			$this->validationErrors = Hash::merge($this->validationErrors, $model->CalendarEvent->validationErrors);
			//throw new InternalErrorException(__d('Calendars', 'insertRrule find error.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		$conditions = array(
			'CalendarsEvent.calendar_rrule_id' => $eventData['CalendarEvent']['calendar_rrule_id'],
			'CalendarsEvent.id <>' => $eventData['CalendarEvent']['id'],
		);

		if (!$model->CalendarEvent->deleteAll($conditions, false)) {
			$this->validationErrors = Hash::merge($this->validationErrors, $model->CalendarEvent->validationErrors);
			//throw new InternalErrorException(__d('Calendars', 'insertRrule deleteAll error.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		//rruleのin/outは、$modelのインスタンス変数をつかっておこなう。
		$this->insertPriodEntry($model, $planParams, $rruleData, $startEventData);
	}

/**
 * 周期性登録
 *
 * @param Model &$model 実際のモデル名
 * @param array $planParams planParams
 * @param array $rruleData rruleData
 * @param array $startEventData eventデータ
 * @return void
 */
	public function insertPriodEntry(Model &$model, $planParams, $rruleData, $startEventData) {
		$model->rrule['INDEX'] = 1;
		switch ($model->rrule['FREQ']) {
			case 'YEARLY':
				$this->insertYearly($model, $planParams, $rruleData, $startEventData, 1);
				break;
			case 'MONTHLY':
				if (isset($model->rrule['BYMONTHDAY'])) {	//指定月のx日、y日
					$this->insertMonthlyByMonthday($model, $planParams, $rruleData, $startEventData, 1);
				} else {	//第ｘ週ｙ曜日
					$this->insertMonthlyByDay($model, $planParams, $rruleData, $startEventData, 1);
				}
				break;
			case 'WEEKLY':
				$this->insertWeekly($model, $planParams, $rruleData, $startEventData, 1);
				break;
			case 'DAILY':
				$this->insertDaily($model, $planParams, $rruleData, $startEventData);
				break;
		}
		////return $startEventData;
	}
}
