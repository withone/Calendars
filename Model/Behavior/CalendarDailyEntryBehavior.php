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
 * @param array $eventData eventデータ(CalendarEventのモデルデータ)
 * @return array $result 結果
 */
	public function insertDaily(Model &$model, $planParams, $rruleData, $eventData) {
		//CakeLog::debug("DBGX: insertDaily() start");

		//CakeLog::debug("DBGDBG: model->rrule[INDEX] before INC value[" . $model->rrule['INDEX'] . "]");
		$model->rrule['INDEX']++;
		//CakeLog::debug("DBGDBG: model->rrule[INDEX] after INC value[" . $model->rrule['INDEX'] . "]");

		//インターバル日数を加算した開始日の計算
		////NC3ではすでにサーバー系日付時刻になおっているから、timezoneDateは呼ばない.
		$time = $eventData['CalendarEvent']['start_date'] . $eventData['CalendarEvent']['start_time']; //catしてYmdHisにする

		$timestamp = mktime(substr($time, 8, 2), substr($time, 10, 2), substr($time, 12, 2),
							substr($time, 4, 2), substr($time, 6, 2) + $model->rrule['INTERVAL'], substr($time, 0, 4));
		$startDate = date('Ymd', $timestamp);
		$startTime = date('His', $timestamp);

		//CakeLog::debug("DBGX: CalendarEvent[start_date]+[start_time][" . $eventData['CalendarEvent']['start_date'] . $eventData['CalendarEvent']['start_time'] . "] >> time[" . $time . "] >> timestamp[" . $timestamp . "] startDate[" . $startDate . "] startTime[" . $startTime . "]");

		//インターバル日数を加算した終了日の計算
		////NC3ではすでにサーバー系日付時刻になおっているから、timezoneDateは呼ばない.
		$time = $eventData['CalendarEvent']['end_date'] . $eventData['CalendarEvent']['end_time']; //catしてYmdHisにする
		$timestamp = mktime(substr($time, 8, 2), substr($time, 10, 2), substr($time, 12, 2),
							substr($time, 4, 2), substr($time, 6, 2) + $model->rrule['INTERVAL'], substr($time, 0, 4));
		$endDate = date('Ymd', $timestamp);
		$endTime = date('His', $timestamp);

		if (!CalendarSupport::isRepeatable($model->rrule, ($startDate . $startTime), $eventData['CalendarEvent']['timezone_offset'])) {
			return true;
		}

		//CakeLog::debug("DBGX: insert() startDateTime[" . $startDate . $startTime . "] endDateTime[" . $endDate . $endTime . "]");
		$rEventData = $this->insert($model, $planParams, $rruleData, $eventData, ($startDate . $startTime), ($endDate . $endTime));
		if ($rEventData['CalendarEvent']['id'] === null) {
			return false;
		}

		//CakeLog::debug("DBGDBG: insertDaily()を再帰CALLします。planParams[" . print_r($planParams, true) . "] rruleData[" . print_r($rruleData, true) . "] rEventData[" . print_r($rEventData, true) . "]");
		return $this->insertDaily($model, $planParams, $rruleData, $rEventData);
	}
}
