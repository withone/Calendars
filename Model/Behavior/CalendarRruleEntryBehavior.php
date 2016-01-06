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
 * @param array $dtstartendData dtstartendデータ
 * @return void
 * @throws InternalErrorException
 */
	public function insertRrule(Model &$model, $planParams, $rruleData, $dtstartendData) {
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

		if (!(isset($model->CalendarCompDtstartend) && is_callable($model->CalendarCompDtstartend->create))) {
			$model->loadModels(['CalendarCompDtstartend' => 'Calendars.CalendarCompDtstartend']);
		}
		$params = array(
			'conditions' => array('CalendarsCompDtstartend.id' => $dtstartendData['CalendarCompDtstartend']['id']),
			'recursive' => (-1),
			'fields' => array('CalendarsCompDtstartend.*'),
			'callbacks' => false
		);
		$rruleData = $model->CalendarCompDtstartend->find('all', $params);
		if (!is_array($startDtstartendData) || !isset($startDtstartendData['CalendarCompDtstartend'])) {
			$this->validationErrors = Hash::merge($this->validationErrors, $model->CalendarCompDtstartend->validationErrors);
			//throw new InternalErrorException(__d('Calendars', 'insertRrule find error.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		$conditions = array(
			'CalendarsCompDtstartend.calendar_comp_rrule_id' => $dtstartendData['CalendarCompDtstartend']['calendar_comp_rrule_id'],
			'CalendarsCompDtstartend.id <>' => $dtstartendData['CalendarCompDtstartend']['id'],
		);

		if (!$model->CalendarCompDtstartend->deleteAll($conditions, false)) {
			$this->validationErrors = Hash::merge($this->validationErrors, $model->CalendarCompDtstartend->validationErrors);
			//throw new InternalErrorException(__d('Calendars', 'insertRrule deleteAll error.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		//rruleのin/outは、$modelのインスタンス変数をつかっておこなう。
		$this->insertPriodEntry($model, $planParams, $rruleData, $startDtstartendData);
	}

/**
 * 周期性登録
 *
 * @param Model &$model 実際のモデル名
 * @param array $planParams planParams
 * @param array $rruleData rruleData
 * @param array $startDtstartendData dtstartendデータ
 * @return void
 */
	public function insertPriodEntry(Model &$model, $planParams, $rruleData, $startDtstartendData) {
		$model->rrule['INDEX'] = 1;
		switch ($model->rrule['FREQ']) {
			case 'YEARLY':
				$this->insertYearly($model, $planParams, $rruleData, $startDtstartendData, 1);
				break;
			case 'MONTHLY':
				if (isset($model->rrule['BYMONTHDAY'])) {	//指定月のx日、y日
					$this->insertMonthlyByMonthday($model, $planParams, $rruleData, $startDtstartendData, 1);
				} else {	//第ｘ週ｙ曜日
					$this->insertMonthlyByDay($model, $planParams, $rruleData, $startDtstartendData, 1);
				}
				break;
			case 'WEEKLY':
				$this->insertWeekly($model, $planParams, $rruleData, $startDtstartendData, 1);
				break;
			case 'DAILY':
				$this->insertDaily($model, $planParams, $rruleData, $startDtstartendData);
				break;
		}
		////return $startDtstartendData;
	}
}
