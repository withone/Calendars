<?php
/**
 * CalendarYearlyEntry Behavior
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
 * CalendarYearlyEntryBehavior
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Model\Behavior
 */
class CalendarYearlyEntryBehavior extends CalendarAppBehavior {

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
 * 年周期の登録
 *
 * @param Model &$model 実際のモデル名
 * @param array $planParams planParams
 * @param ssary $rruleData rruleData
 * @param array $eventData eventデータ(CalendarEventのモデルデータ)
 * @param int $first 最初のデータかどうか 1:最初である  0:最初ではない
 * @param int $bymonthday bymonthday
 * @return array $result 結果
 */
	public function insertYearly(Model &$model, $planParams, $rruleData, $eventData, $first = 0, $bymonthday = 0) {
		list($startDate, $startTime, $endDate, $endTime, $diffNum) = $this->setStartEndDateAndTime($model, $eventData, $first);
		if (!CalendarSupport::isRepeatable($model->rrule, ($startDate . $startTime), $eventData['CalendarEvent']['timezone_offset'])) {

			//insertYearly()は再帰callされるが、ここ(isRepeatable()===falseになった時、復帰する。
			return true;
		}

		if ($first && empty($model->rrule['BYDAY'])) {
			$bymonthday = intval(substr($startDate, 6, 2));	//最初のデータで、かつ、日の間隔ルールがないなら、開始日付時刻の日をbymonthdayとする。
		}

		$startDateTime = $endDateTime = '';
		$dtArray = array($startDate, $startTime, $endDate, $endTime);
		list($eventData, $startDateTime, $endDateTime) = $this->setStartAndEndDateTimeEtc($model, $dtArray, $first, $bymonthday, $diffNum, $eventData);

		//
		//eventDataの開始・終了の日付と時刻を更新するしてから、insertYearly()を再帰callする。
		//
		$eventData['CalendarEvent']['start_date'] = CalendarTime::timezoneDate($startDateTime, 1, 'Ymd');
		$eventData['CalendarEvent']['start_time'] = CalendarTime::timezoneDate($startDateTime, 1, 'His');
		$eventData['CalendarEvent']['end_date'] = CalendarTime::timezoneDate($endDateTime, 1, 'Ymd');
		$eventData['CalendarEvent']['end_time'] = CalendarTime::timezoneDate($endDateTime, 1, 'His');
		if (!empty($model->rrule['BYDAY']) && count($model->rrule['BYDAY']) > 0) {
			return $this->insertYearly($model, $planParams, $rruleData, $eventData);	//insertYearly()の再帰call ケース1
		} else {
			return $this->insertYearly($model, $planParams, $rruleData, $eventData, 0, $bymonthday); //insertYearly()の再帰call ケース2
		}
	}

/**
 * 開始と終了の日付、時刻の生成
 *
 * @param Model &$model モデル
 * @param array $eventData event配列データ
 * @param int $first 最初のデータかどうか. 1:最初 0:最初ではない
 * @return array array($startDate, $startTime, $endDate, $endTime, $diffNum)を返す
 */
	public function setStartEndDateAndTime(Model &$model, $eventData, $first) {
		$sTime = CalendarTime::timezoneDate($eventData['CalendarEvent']['start_date'] . $eventData['CalendarEvent']['start_time'], 0, 'YmdHis');
		$eTime = CalendarTime::timezoneDate($eventData['CalendarEvent']['end_date'] . $eventData['CalendarEvent']['end_time'], 0, 'YmdHis');

		//開始日付(00:00:00)と終了日付(00:00:00)の累積秒および、その「差分日数」を計算する。
		$startTimestamp = mktime(0, 0, 0, substr($sTime, 4, 2), substr($sTime, 6, 2), substr($sTime, 0, 4));
		$endTimestamp = mktime(0, 0, 0, substr($eTime, 4, 2), substr($eTime, 6, 2), substr($eTime, 0, 4));
		$diffNum = ($endTimestamp - $startTimestamp) / 86400;

		if ($first) {
			$startTimestamp = mktime(substr($sTime, 8, 2), substr($sTime, 10, 2), substr($sTime, 12, 2),
								substr($sTime, 4, 2), substr($sTime, 6, 2), substr($sTime, 0, 4));
			$endTimestamp = mktime(substr($eTime, 8, 2), substr($eTime, 10, 2), substr($eTime, 12, 2),
								substr($eTime, 4, 2), substr($eTime, 6, 2), substr($eTime, 0, 4));
		} else {
			$startTimestamp = mktime(substr($sTime, 8, 2), substr($sTime, 10, 2), substr($sTime, 12, 2),
								1, 1, substr($sTime, 0, 4) + $model->rrule['INTERVAL']);
			$endTimestamp = mktime(substr($eTime, 8, 2), substr($eTime, 10, 2), substr($eTime, 12, 2),
								1, 1 + $diffNum, substr($eTime, 0, 4) + $model->rrule['INTERVAL']);
		}

		$startDate = date('Ymd', $startTimestamp);
		$startTime = date('His', $startTimestamp);

		$endDate = date('Ymd', $endTimestamp);
		$endTime = date('His', $endTimestamp);

		return array($startDate, $startTime, $endDate, $endTime, $diffNum);
	}

/**
 * 開始・終了の日付と時刻等をセットする。
 *
 * @param Model &$model モデル
 * @param array $dtArray startDate,startTime,endDate,endTimeをカプセル化した配列
 * @param int $first first 最初かどうか。1:最初 0:最初でない
 * @param int $bymonthday bymonthday 毎月ｘ日のx日のこと
 * @param int $diffNum diffNum開始日と終了日の差分日数
 * @param array $eventData eventData配列データ
 * @return array array($eventData, $startDateTime, $endDateTime)を返す。
 */
	public function setStartAndEndDateTimeEtc(Model &$model, $dtArray, $first, $bymonthday, $diffNum, $eventData) {
		list($startDate, $startTime, $endDate, $endTime) = $dtArray;

		$result = true;
		$currentMonth = intval(substr($startDate, 4, 2));	//開始日付の月を、現在の月とする。
		foreach ($model->rrule['BYMONTH'] as $month) {
			if ($first && $currentMonth > $month) {
				continue;
			}

			$this->setDtStartendData($first, $currentMonth, $month, $startDate, $startTime, $endDate, $endTime, $diffNum, $eventData);

			if (!empty($model->rrule['BYDAY']) && count($model->rrule['BYDAY']) > 0) {
				$result = $this->insertYearlyByday($model, $planParams, $rruleData, $eventData, $first);
			} else {
				$result = $this->insertYearlyByMonthday($model, $planParams, $rruleData, $eventData, $bymonthday, $first);
			}
			if ($result === false) {
				return false;
			}
		}
		$startDateTime = $startDate . $startTime;
		$endDateTime = $endDate . $endTime;
		if (is_array($result)) {
			list($startDateTime, $endDateTime) = $result;
		}

		return array($eventData, $startDateTime, $endDateTime);
	}

/**
 * eventDataへのデータセット
 *
 * @param int $first first
 * @param int $currentMonth currentMonth
 * @param int $month month
 * @param string $startDate startDate
 * @param string $startTime startTime
 * @param string $endDate endDate
 * @param string $endTime endTime
 * @param int $diffNum diffNum
 * @param array &$eventData eventData
 * @return void
 */
	public function setDtStartendData($first, $currentMonth, $month, $startDate, $startTime, $endDate, $endTime, $diffNum, &$eventData) {
		if ($first && $currentMonth === $month) {
			$eventData['CalendarEvent']['start_date'] = $startDate;
			$eventData['CalendarEvent']['start_time'] = $startTime;
			$eventData['CalendarEvent']['end_date'] = $endDate;
			$eventData['CalendarEvent']['end_time'] = $endTime;
		} else {
			$eventData['CalendarEvent']['start_date'] = substr($startDate, 0, 4) . sprintf('%02d', $month) . '01';
			$eventData['CalendarEvent']['start_time'] = $startTime;
			// end_dateにはstartDate + $diffNumの日付をセット(12/31の場合に翌年がセットされるため)
			$eventData['CalendarEvent']['end_date'] = substr($startDate, 0, 4) . sprintf('%02d', $month) . sprintf('%02d', 1 + $diffNum);
			$eventData['CalendarEvent']['end_time'] = $endTime;
		}
	}

/**
 * 年周期の登録（年単位－開始日と同日）
 *
 * @param Model &$model 実際のモデル名
 * @param array $planParams planParams
 * @param array $rruleData rruleData
 * @param array $eventData eventデータ(CalendarEventのモデルデータ)
 * @param int $bymonthday bymonthday
 * @param int $first 最初のデータかどうか 1:最初である  0:最初ではない
 * @return mixed boolean true:登録せず終了 false:失敗、array 登録成功: array(登録した開始年月日時分秒, 登録した終了年月日時分秒)
 */
	public function insertYearlyByMonthday(Model &$model, $planParams, $rruleData, $eventData, $bymonthday, $first) {
		$model->rrule['INDEX']++;

		//開始日付時刻の処理
		$sTime = $eventData['CalendarEvent']['start_date'] . $eventData['CalendarEvent']['start_time'];
		$startTimestamp = mktime(0, 0, 0, substr($sTime, 4, 2), substr($sTime, 6, 2), substr($sTime, 0, 4));

		//インターバル日数の計算
		$currentDay = intval(substr($sTime, 6, 2));

		//１月の該当日(日付時刻)の計算
		$firstTimestamp = mktime(0, 0, 0, substr($sTime, 4, 2), 1, substr($sTime, 0, 4));	//その年初月(=１月)の最初の該当日

		if ($bymonthday > date('t', $firstTimestamp)) {
			//毎月ｘ日のｘ日が、開始日の日より後なら、その差分の日数を、インターバル日数とする。
			$intervalDay = $bymonthday - date('t', $firstTimestamp);
		} else {
			//毎月ｘ日のｘ日が、開始日の日と同じか前なら、インターバル日数を０とする。
			$intervalDay = 0;
		}

		//call復帰条件のチェック
		if ($first && $currentDay >= $bymonthday) {
			//現在日が毎月ｘ日のｘ日を超したら、行き過ぎなので、INDEXをデクリメントして、callから復帰する。
			$model->rrule['INDEX']--;
			return true;
		}

		//終了日付時刻の処理
		$eTime = $eventData['CalendarEvent']['end_date'] . $eventData['CalendarEvent']['end_time'];
		$endTimestamp = mktime(0, 0, 0, substr($eTime, 4, 2), substr($eTime, 6, 2), substr($eTime, 0, 4));

		//開始日と終了日の差分日数の計算
		$diffNum = ($endTimestamp - $startTimestamp) / 86400;

		//毎月ｘ日のｘ日を考慮した開始日付時刻の実日付時刻を計算
		//
		//(毎月ｘ日のｘ日が、開始日の日と同じかより後ならその差分の日数(=インターバル日数)を引くので、開始日の日をつかった実日数計算になる。）
		//(毎月ｘ日のｘ日が、開始日の日より前ならその差分の日数(=インターバル日数)は０なので、毎月ｘ日のｘ日をつかった実日数計算になる。）
		//
		$startTimestamp = mktime(substr($sTime, 8, 2), substr($sTime, 10, 2), substr($sTime, 12, 2),
							substr($sTime, 4, 2), $bymonthday - $intervalDay, substr($sTime, 0, 4));
		$startDate = date('Ymd', $startTimestamp);
		$startTime = date('His', $startTimestamp);

		//毎月ｘ日のｘ日を考慮した終了日付時刻の実日付時刻を計算
		//
		//(毎月ｘ日のｘ日が、開始日の日と同じかより後ならその差分の日数(=インターバル日数)を引くので、開始日の日をつかった実日数計算になる。）
		//(毎月ｘ日のｘ日が、開始日の日より前ならその差分の日数(=インターバル日数)は０なので、毎月ｘ日のｘ日をつかった実日数計算になる。）
		//(上記２ケースとも、日に開始日と終了日の差分日数を加算しているので、終了の日となる。）
		//
		$endTimestamp = mktime(substr($eTime, 8, 2), substr($eTime, 10, 2), substr($eTime, 12, 2),
							substr($sTime, 4, 2), $bymonthday - $intervalDay + $diffNum, substr($sTime, 0, 4));
		$endDate = date('Ymd', $endTimestamp);
		$endTime = date('His', $endTimestamp);

		if (!CalendarSupport::isRepeatable($model->rrule, ($startDate . $startTime), $eventData['CalendarEvent']['timezone_offset'])) {
			//繰返しがとまったので、callから復帰する。
			return true;
		}

		$rEventData = $this->insert($model, $planParams, $rruleData, $eventData, ($startDate . $startTime), ($endDate . $endTime));
		if ($rEventData['CalendarEvent']['id'] === null) {
			return false;
		} else {
			return array(($startDate . $startTime), ($endDate . $endTime));
		}
	}

/**
 * 年周期の登録（年単位－第Ｍ週Ｎ曜日)
 *
 * @param Model &$model 実際のモデル名
 * @param array $planParams planParams
 * @param array $rruleData rruleData
 * @param array $eventData eventデータ(CalendarEventのモデルデータ)
 * @param int $first 最初のデータかどうか 1:最初である  0:最初ではない
 * @return mixed boolean true:登録せず終了 false:失敗、array 登録成功: array(登録した開始年月日時分秒, 登録した終了年月日時分秒)
 */
	public function insertYearlyByday(Model &$model, $planParams, $rruleData, $eventData, $first = 0) {
		$model->rrule['INDEX']++;

		//BYDAYは'2MO','3SA'といった形式である
		//よって、wdayNumにはSUなら0, SAなら6とった値になる。
		$wdayNum = array_search(substr($model->rrule['BYDAY'][0], -2), self::$calendarWdayArray);
		//よって、weekには、、最後２文字を取り除いた、第x月曜、第y土曜のx、yが取り出せる。
		$week = intval(substr($model->rrule['BYDAY'][0], 0, -2));	//-2で最後２文字をけずる。

		$sTime = $eventData['CalendarEvent']['start_date'] . $eventData['CalendarEvent']['start_time'];
		$eTime = $eventData['CalendarEvent']['end_date'] . $eventData['CalendarEvent']['end_time'];

		$timestamp = mktime(0, 0, 0, substr($sTime, 4, 2), 1, substr($sTime, 0, 4));	//開始日の同年同月1日のtimestamp

		$byday = CalendarSupport::getByday($timestamp, $week, $wdayNum);

		//call復帰条件のチェック
		if ($first && $sTime >= $byday) {
			//開始日(対象日？）が繰返しENDのb(第x週第y曜日の実日）を超したら、行き過ぎなので、INDEXをデクリメントして、callから復帰する。
			$model->rrule['INDEX']--;
			return true;
		}

		$startDate = $startTime = $endDate = $endTime = '';
		$this->setStartDateTiemAndEndDateTime($sTime, $eTime, $startDate, $startTime, $endDate, $endTime);

		if (!CalendarSupport::isRepeatable($model->rrule, ($startDate . $startTime), $eventData['CalendarEvent']['timezone_offset'])) {
			//繰返しがとまったので、復帰する。
			return true;
		}

		$rEventData = $this->insert($model, $planParams, $rruleData, $eventData, ($startDate . $startTime), ($endDate . $endTime));
		if ($rEventData['CalendarEvent']['id'] === null) {
			return false;
		}

		return array(($startDate . $startTime), ($endDate . $endTime));
	}
}
