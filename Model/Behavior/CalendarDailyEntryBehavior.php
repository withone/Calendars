<?php
/**
 * CalendarDailyEntry Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('CalendarAppBehavior', 'Calendars.Model/Behavior');
App::uses('CalendarSupport', 'Calendars.Utility');
App::uses('CalendarTime', 'Calendars.Utility');

/**
 * CalendarDailyEntryBehavior
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Model\Behavior
 */
class CalendarDailyEntryBehavior extends CalendarAppBehavior {

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
 * 日単位の周期性登録(１日ごと、２日ごと、、、６日ごと）
 *
 * @param Model &$model 実際のモデル名
 * @param array $planParams planParams
 * @param ssary $rruleData rruleData
 * @param array $dtstartendData dtstartendデータ(CalendarCompDtstartendのモデルデータ)
 * @return array $result 結果
 */
	public function insertDaily(Model &$model, $planParams, $rruleData, $dtstartendData) {
		$model->rrule['INDEX']++;

		//インターバル日数を加算した開始日の計算
		$time = CalendarTime::timezoneDate(($dtstartendData['CalendarCompDtstartend']['start_date'] . $dtstartendData['CalendarCompDtstartend']['start_time']), 0, 'YmdHis');
		$timestamp = mktime(substr($time, 8, 2), substr($time, 10, 2), substr($time, 12, 2),
							substr($time, 4, 2), substr($time, 6, 2) + $model->rrule['INTERVAL'], substr($time, 0, 4));
		$startDate = date('Ymd', $timestamp);
		$startTime = date('His', $timestamp);

		//インターバル日数を加算した終了日の計算
		$time = CalendarTime::timezoneDate(($dtstartendData['CalendarCompDtstartend']['end_date'] . $dtstartendData['CalendarCompDtstartend']['end_time']), 0, 'YmdHis');
		$timestamp = mktime(substr($time, 8, 2), substr($time, 10, 2), substr($time, 12, 2),
							substr($time, 4, 2), substr($time, 6, 2) + $model->rrule['INTERVAL'], substr($time, 0, 4));
		$endDate = date('Ymd', $timestamp);
		$endTime = date('His', $timestamp);

		if (!CalendarSupport::isRepeatable($model->rrule, ($dtstartendData['CalendarCompDtstartend']['start_date'] . $dtstartendData['CalendarCompDtstartend']['start_time']), $dtstartendData['CalendarCompDtstartend']['timezone_offset'])) {
			return true;
		}

		$rDtstartendData = $this->insert($model, $planParams, $rruleData, $dtstartendData, ($startDate . $startTime), ($endDate . $endTime));
		if ($rDtstartendData['CalendarCompDtstartend']['id'] === null) {
			return false;
		}
		return $this->insertDaily($model, $planParams, $rruleData, $rDtstartendData);
	}
}
