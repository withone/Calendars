<?php
/**
 * CalendarMonthlyEntry Behavior
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
 * CalendarMonthlyEntryBehavior
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Model\Behavior
 */
class CalendarMonthlyEntryBehavior extends CalendarAppBehavior {

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
 * 月周期の登録（月単位－各月の指定日）
 *
 * @param Model &$model 実際のモデル名
 * @param array $planParams planParams
 * @param array $rruleData rruleData
 * @param array $dtstartendData dtstartendデータ(CalendarCompDtstartendのモデルデータ)
 * @param int $bymonthday bymonthday
 * @param int $first 最初のデータかどうか 1:最初である  0:最初ではない. 初期値は0
 * @return mixed boolean true:登録せず終了 false:失敗、array 登録成功: array(登録した開始年月日時分秒, 登録した終了年月日時分秒)
 */
	public function insertMonthlyByMonthday(Model &$model, $planParams, $rruleData, $dtstartendData, $bymonthday, $first = 0) {
		$model->rrule['INDEX']++;

		//開始日付時刻の処理
		$sTime = CalendarTime::timezoneDate($dtstartendData['CalendarCompRtstartend']['start_date'] . $dtstartendData['CalendarCompRtstartend']['start_time'], 0, 'YmdHis');
		$startTimestamp = mktime(0, 0, 0, substr($sTime, 4, 2), substr($sTime, 6, 2), substr($sTime, 0, 4));

		//インターバル月数の計算
		$currentDay = intval(substr($sTime, 6, 2));
		if ($first && $currentDay < $model->rrule['BYMONTHDAY'][0]) {
			$interval = 0;
		} else {
			$interval = $model->rrule['INTERVAL'];
		}

		//指定月の１日（ついたち）の日付時刻の計算(指定月はインターバル月数を考慮して計算）
		$firstTimestamp = mktime(0, 0, 0, substr($sTime, 4, 2) + $interval, 1, substr($sTime, 0, 4));

		if ($model->rrule['BYMONTHDAY'][0] > date('t', $firstTimestamp)) {
			$intervalDay = $model->rrule['BYMONTHDAY'][0] - date('t', $firstTimestamp);
			//毎月x日のxが、指定月の日数(y)より大きき時、その差分日数(x-)をインターバイル日数とする。
		} else {
			$intervalDay = 0;
			//毎月x日のxが、指定月の日数(y)以下の時、インターバイル日数は０（なし）とする。
		}

		//終了日付時刻の処理
		$eTime = CalendarTime::timezoneDate($dtstartendData['CalendarCompRtstartend']['end_date'] . $dtstartendData['CalendarCompRtstartend']['end_time'], 0, 'YmdHis');
		$endTimestamp = mktime(0, 0, 0, substr($eTime, 4, 2), substr($eTime, 6, 2), substr($eTime, 0, 4));

		//開始日と終了日の差分日数の計算
		$diffNum = ($endTimestamp - $startTimestamp) / 86400;

		//インターバル月数とインターバル日数を考慮した開始・終了日付時刻の実計算
		$startTimestamp = mktime(substr($sTime, 8, 2), substr($sTime, 10, 2), substr($sTime, 12, 2),
							substr($sTime, 4, 2) + $interval, $model->rrule['BYMONTHDAY'][0] - $intervalDay, substr($sTime, 0, 4));
		$startDate = date('Ymd', $startTimestamp);
		$startTime = date('His', $startTimestamp);

		$endTimestamp = mktime(substr($eTime, 8, 2), substr($eTime, 10, 2), substr($eTime, 12, 2),
							substr($sTime, 4, 2) + $interval, $model->rrule['BYMONTHDAY'][0] - $intervalDay + $diffNum, substr($sTime, 0, 4));
		$endDate = date('Ymd', $endTimestamp);
		$endTime = date('His', $endTimestamp);

		if (!CalendarSupport::isRepeatable($model->rrule, ($startDate . $startTime), $dtstartendData['CalendarCompRtstartend']['timezone_offset'])) {
			//繰返しがとまったので、callから復帰する。
			return true;
		}

		$rDtstartendData = $this->insert($model, $planParams, $rruleData, $dtstartendData, ($startDate . $startTime), ($endDate . $endTime));
		if ($rDtstartendData['CalendarCompDtstartend']['id'] === null) {
			return false;
		}

		return $this->insertMonthlyByMonthday($model, $planParams, $rruleData, $rDtstartendData, $bymonthday, $first);
	}

/**
 * 月周期の登録（月単位－第Ｎ週Ｍ曜日)
 *
 * @param Model &$model 実際のモデル名
 * @param array $planParams planParams
 * @param array $rruleData rruleData
 * @param array $dtstartendData dtstartendデータ(CalendarCompDtstartendのモデルデータ)
 * @param int $first 最初のデータかどうか 1:最初である  0:最初ではない
 * @return mixed boolean true:登録せず終了 false:失敗、array 登録成功: array(登録した開始年月日時分秒, 登録した終了年月日時分秒)
 */
	public function insertMonthlyByDay(Model &$model, $planParams, $rruleData, $dtstartendData, $first = 0) {
		$model->rrule['INDEX']++;

		$this->setStimeEtimeAndByday($model->rrule, $dtstartendData, $first, $sTime, $eTime, $byday);

		//call復帰条件のチェック
		if ($first && $sTime >= $byday) {
			//開始日(対象日？）が繰返しENDのb(第x週第y曜日の実日）を超したら、行き過ぎなので、INDEXをデクリメントして、自分を再帰callする。
			$model->rrule['INDEX']--;
			return $this->insertMonthlyByDay($model, $planParams, $rruleData, $dtstartendData, $first);
		}

		$startDate = $startTime = $endDate = $endTime = '';
		$this->setStartDateTiemAndEndDateTime($sTime, $eTime, $startDate, $startTime, $endDate, $endTime);

		if (!CalendarSupport::isRepeatable($model->rrule, ($startDate . $startTime), $dtstartendData['CalendarCompRtstartend']['timezone_offset'])) {
			//繰り返しがとまったので、復帰いたします。
			return true;
		}

		$rDtstartendData = $this->insert($model, $planParams, $rruleData, $dtstartendData, ($startDate . $startTime), ($endDate . $endTime));
		if ($rDtstartendData['CalendarCompDtstartend']['id'] === null) {
			return false;
		}

		return $this->insertMonthlyByDay($model, $planParams, $rruleData, $rDtstartendData, $first);
	}

/**
 * sTime,eTimeおよびbydayの設定
 *
 * @param array $rrule rrule配列
 * @param array $dtstartendData dtstartendData
 * @param int $first first 1:最初のデータ 0:最初のデータでない
 * @param string &$sTime sTime
 * @param string &$eTime eTime
 * @param string &$byday byday
 * @return void
 */
	public function setStimeEtimeAndByday($rrule, $dtstartendData, $first, &$sTime, &$eTime, &$byday) {
		//BYDAYは'2MO','3SA'といった形式である
		//よって、wdayNumにはSUなら0, SAなら6とった値になる。
		$wdayNum = array_search(substr($rrule['BYDAY'][0], -2), self::$calendarWdayArray);
		//よって、weekには、、最後２文字を取り除いた、第x月曜、第y土曜のx、yが取り出せる。
		$week = intval(substr($rrule['BYDAY'][0], 0, -2));

		$sTime = CalendarTime::timezoneDate(($dtstartendData['CalendarCompDtstartend']['start_date'] . $dtstartendData['CalendarCompDtstartend']['start_time']), 0, 'YmdHis');
		$eTime = CalendarTime::timezoneDate(($dtstartendData['CalendarCompDtstartend']['end_date'] . $dtstartendData['CalendarCompDtstartend']['end_time']), 0, 'YmdHis');

		//開始日の同年インターバル月数考慮月1日のtimestamp
		$timestamp = mktime(0, 0, 0, substr($sTime, 4, 2) + ($first ? 0 : $rrule['INTERVAL']), 1, substr($sTime, 0, 4));

		$byday = CalendarSupport::getByday($timestamp, $week, $wdayNum);
	}
}
