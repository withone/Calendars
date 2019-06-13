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
 * @param Model $model 実際のモデル名
 * @param array $planParams planParams
 * @param ssary $rruleData rruleData
 * @param array $eventData eventデータ(CalendarEventのモデルデータ)
 * @param int $createdUserWhenUpd createdUserWhenUpd
 * @return array $result 結果
 */
	public function insertDaily(Model $model, $planParams, $rruleData, $eventData,
		$createdUserWhenUpd = null) {
		$model->rrule['INDEX']++;

		//ユーザタイムゾーンを取得しておく。
		$userTz = (new NetCommonsTime())->getUserTimezone();

		//インターバル日数を加算した開始日の計算
		//
		////NC3ではすでにサーバー系日付時刻になおっているから、timezoneDateは呼ばない.
		//catしてYmdHisにする
		$sTime = $eventData['CalendarEvent']['start_date'] . $eventData['CalendarEvent']['start_time'];

		//以下で使う時間系はカレンダー画面上（=ユーザー系）でのカレンダ
		//日付時刻をさしているので、ユーザー系に直す。
		//
		$userStartTime = (new NetCommonsTime())->toUserDatetime(CalendarTime::calDt2dt($sTime));
		$userStartTime = CalendarTime::dt2calDt($userStartTime);

		//ユーザー系開始日の同年同月の日＋インターバール(rrule[INTERVAL])
		//日数のタイムスタンプを取得
		$date = new DateTime('now', (new DateTimeZone($userTz)));	//ユーザー系DateTimeObj生成
		$date->setDate(substr($userStartTime, 0, 4), substr($userStartTime, 4, 2),
				substr($userStartTime, 6, 2) + $model->rrule['INTERVAL']);
		$date->setTime(substr($userStartTime, 8, 2),
			substr($userStartTime, 10, 2), substr($userStartTime, 12, 2));
		//サーバー系に直して、開始日のYmdとHisを取得
		$date->setTimeZone(new DateTimeZone('UTC'));	//サーバー系TZに直す
		$svrStartDate = $date->format('Ymd');
		$svrStartTime = $date->format('His');

		//CakeLog::debug("DBGX: CalendarEvent[start_date]+[start_time][" . $eventData['CalendarEvent']['start_date'] . $eventData['CalendarEvent']['start_time'] . "] >> time[" . $time . "] >> timestamp[" . $timestamp . "] svrStartDate[" . $svrStartDate . "] svrStartTime[" . $svrStartTime . "]");

		//インターバル日数を加算した終了日の計算
		//
		////NC3ではすでにサーバー系日付時刻になおっているから、timezoneDateは呼ばない.
		//catしてYmdHisにする
		$eTime = $eventData['CalendarEvent']['end_date'] . $eventData['CalendarEvent']['end_time'];

		//以下で使う時間系はカレンダー画面上（=ユーザー系）でのカレンダ
		//日付時刻をさしているので、ユーザー系に直す。
		//
		$userEndTime = (new NetCommonsTime())->toUserDatetime(CalendarTime::calDt2dt($eTime));
		$userEndTime = CalendarTime::dt2calDt($userEndTime);

		//ユーザー系終了日の同年同月の日＋インターバール(rrule[INTERVAL])日数
		//のタイムスタンプを取得
		$date = new DateTime('now', (new DateTimeZone($userTz)));	//ユーザー系DateTimeObj生成
		$date->setDate(substr($userEndTime, 0, 4),
			substr($userEndTime, 4, 2),
			substr($userEndTime, 6, 2) + $model->rrule['INTERVAL']);
		$date->setTime(substr($userEndTime, 8, 2),
			substr($userEndTime, 10, 2), substr($userEndTime, 12, 2));

		//サーバー系に直して、終了日のYmdとHisを取得
		$date->setTimeZone(new DateTimeZone('UTC'));	//サーバー系TZに直す
		$svrEndDate = $date->format('Ymd');
		$svrEndTime = $date->format('His');

		if (!CalendarSupport::isRepeatable($model->rrule, ($svrStartDate . $svrStartTime),
			$eventData['CalendarEvent']['timezone_offset'], $model->isOverMaxRruleIndex)) {
			return true;
		}

		//CakeLog::debug("DBGX: insert() svrStartDateTime[" . $svrStartDate . $svrStartTime . "] svrEndDateTime[" . $svrEndDate . $svrEndTime . "]");
		$rEventData = $this->insert($model, $planParams, $rruleData, $eventData,
			($svrStartDate . $svrStartTime), ($svrEndDate . $svrEndTime), $createdUserWhenUpd);
		if ($rEventData['CalendarEvent']['id'] === null) {
			return false;
		}

		//CakeLog::debug("DBGDBG: insertDaily()を再帰CALLします。planParams[" . print_r($planParams, true) . "] rruleData[" . print_r($rruleData, true) . "] rEventData[" . print_r($rEventData, true) . "]");
		return $this->insertDaily($model, $planParams, $rruleData, $rEventData, $createdUserWhenUpd);
	}
}
