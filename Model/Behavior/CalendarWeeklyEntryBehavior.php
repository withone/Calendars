<?php
/**
 * CalendarWeeklyEntry Behavior
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
 * CalendarWeeklyEntryBehavior
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Model\Behavior
 */
class CalendarWeeklyEntryBehavior extends CalendarAppBehavior {

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
 * 週周期の登録
 *
 * @param Model &$model 実際のモデル名
 * @param array $planParams planParams
 * @param ssary $rruleData rruleData
 * @param array $eventData eventデータ(CalendarEventのモデルデータ)
 * @param int $first 最初のデータかどうか 1:最初である  0:最初ではない
 * @return array $result 結果
 */
	public function insertWeekly(Model &$model, $planParams, $rruleData, $eventData, $first = 0) {
		// 開始日と終了日の週の日曜日の日付時刻および現在週のセット
		$currentWeek = '';
		$this->setStartEndSundayDateAndTime($eventData, $currentWeek);

		if (!CalendarSupport::isRepeatable($model->rrule, ($eventData['CalendarEvent']['start_date'] . $eventData['CalendarEvent']['start_time']), $eventData['CalendarEvent']['timezone_offset'])) {
			return true;
		}

		foreach ($model->rrule['BYDAY'] as $val) {
			$index = array_search($val, self::$calendarWdayArray);
			if ($first && $currentWeek >= $index) {
				continue;
			}
			$result = $this->insertWeeklyInterval($model, $planParams, $rruleData, $eventData, $index);
			if ($result === false) {
				return $result;
			}
		}

		$startTime = $eventData['CalendarEvent']['start_date'] . $eventData['CalendarEvent']['start_time'];
		$endTime = $eventData['CalendarEvent']['end_date'] . $eventData['CalendarEvent']['end_time'];

		$eventData['CalendarEvent']['start_date'] = CalendarTime::timezoneDate($startTime, 1, 'Ymd');
		$eventData['CalendarEvent']['start_time'] = CalendarTime::timezoneDate($startTime, 1, 'His');
		$eventData['CalendarEvent']['end_date'] = CalendarTime::timezoneDate($endTime, 1, 'Ymd');
		$eventData['CalendarEvent']['end_time'] = CalendarTime::timezoneDate($endTime, 1, 'His');

		return $this->insertWeekly($model, $planParams, $rruleData, $eventData);
	}

/**
 * インターバル用週周期の登録
 *
 * @param Model &$model 実際のモデル名
 * @param array $planParams planParams
 * @param ssary $rruleData rruleData
 * @param array $eventData eventデータ(CalendarEventのモデルデータ)
 * @param int $interval インターバル
 * @return array $result 結果
 */
	public function insertWeeklyInterval(Model &$model, $planParams, $rruleData, $eventData, $interval) {
		$model->rrule['INDEX']++;

		//インターバル日数を加算した開始日の計算
		$time = $eventData['CalendarEvent']['start_date'] . $eventData['CalendarEvent']['start_time'];
		$timestamp = mktime(substr($time, 8, 2), substr($time, 10, 2), substr($time, 12, 2),
							substr($time, 4, 2), substr($time, 6, 2) + $interval, substr($time, 0, 4));
		$startDate = date('Ymd', $timestamp);
		$startTime = date('His', $timestamp);

		//インターバル日数を加算した終了日の計算
		$time = $eventData['CalendarEvent']['end_date'] . $eventData['CalendarEvent']['end_time'];
		$timestamp = mktime(substr($time, 8, 2), substr($time, 10, 2), substr($time, 12, 2),
							substr($time, 4, 2), substr($time, 6, 2) + $interval, substr($time, 0, 4));
		$endDate = date('Ymd', $timestamp);
		$endTime = date('His', $timestamp);

		if (!CalendarSupport::isRepeatable($model->rrule, ($startDate . $startTime), $eventData['CalendarEvent']['timezone_offset'])) {
			return true;
		}

		$rEventData = $this->insert($model, $planParams, $rruleData, $eventData, ($startDate . $startTime), ($endDate . $endTime));
		if ($rEventData['CalendarEvent']['id'] === null) {
			return false;
		} else {
			return true;
		}
	}

/**
 * 開始日と終了日の週の日曜日の日付時刻のセット
 *
 * @param array &$eventData eventデータ
 * @param string &$currentWeek currnetWeek文字列
 * @return void
 */
	public function setStartEndSundayDateAndTime(&$eventData, &$currentWeek) {
		//開始日の週の日曜日の日付時刻
		$time = CalendarTime::timezoneDate(($eventData['CalendarEvent']['start_date'] . $eventData['CalendarEvent']['start_time']), 0, 'YmdHis');
		$timestamp = mktime(substr($time, 8, 2), substr($time, 10, 2), substr($time, 12, 2),
							substr($time, 4, 2), substr($time, 6, 2) + ($first ? 0 : (7 * $model->rrule['INTERVAL'])), substr($time, 0, 4));
		$currentWeek = date('w', $timestamp);
		$sundayTimestamp = $timestamp - $currentWeek * 86400;	//開始日(または開始日＋インターバイル日数)の週の日曜日を求める。
		$eventData['CalendarEvent']['start_date'] = date('Ymd', $sundayTimestamp);
		$eventData['CalendarEvent']['start_time'] = date('His', $sundayTimestamp);

		//終了日の週の日曜日の日付時刻
		$time = CalendarTime::timezoneDate(($eventData['CalendarEvent']['end_date'] . $eventData['CalendarEvent']['end_time']), 0, 'YmdHis');
		$endTimestamp = mktime(substr($time, 8, 2), substr($time, 10, 2), substr($time, 12, 2),
							substr($time, 4, 2), substr($time, 6, 2) + ($first ? 0 : (7 * $model->rrule['INTERVAL'])), substr($time, 0, 4));
		$endSundayTimestamp = $endTimestamp - $currentWeek * 86400;	//終了日(または終了日＋インターバル日数)の週の日曜日を求める。
		$eventData['CalendarEvent']['end_date'] = date('Ymd', $endSundayTimestamp);
		$eventData['CalendarEvent']['end_time'] = date('His', $endSundayTimestamp);
	}
}
