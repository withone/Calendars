<?php
/**
 * CalendarSupport Utiltiy
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

//App:uses('CalendarTime', 'Calendars.Utility');

/**
 * CalendarSupport Utiltiy
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Utility
 */
class CalendarSupport {

/**
 * generateIcalUid
 *
 * iCalendar仕様のUID生成
 *
 * @param string $startDate カレンダー開始日付
 * @param string $startTime カレンダー開始時刻
 * @return string iCalendar仕様のUIDを生成.
 */
	public static function generateIcalUid($startDate, $startTime) {
		$domain = 'localhost';
		if (preg_match("/(?:.+)(?:\/\/)([^\/]+)/", FULL_BASE_URL, $matches) === 1) {
			$domain = $matches[1];
		}
		$iCalendarUid = $startDate . 'T' . $startTime . 'Z' . '-' . uniqid() . '@' . $domain;
		return $iCalendarUid;
	}

/**
 * 繰返し可能かどうか
 *
 * @param array $rrule rrule配列
 * @param string $startDateTime 開始日付時刻文字列 YmdHis形式
 * @param float $timezoneOffset timezoneOffset (-12.0～+12.0)
 * @param string $calendarWeek calendar_week(Sun|Mon|....Sat、日|月|....|土 などの文字列。初期値は''
 * @return true or false
 */
	public static function isRepeatable($rrule, $startDateTime, $timezoneOffset, $calendarWeek = '') {
		if (isset($rrule['UNTIL'])) {
			////$until = self::calendarDateFormat((substr($rrule['UNTIL'], 0, 8) . substr($rrule['UNTIL'], -6)), $timezoneOffset, 0, $calendarWeek);
			//NC3では、rrule['UNTIL']はサーバ系(UTC)になっている。また、$startDateTimeもサーバ系(UTC)になっている。
			//なので、calendarDateFormatをつかってTZ考慮したcalendarDateFormatはつかわなくてＯＫ。
			$until = substr($rrule['UNTIL'], 0, 8) . substr($rrule['UNTIL'], -6);

			CakeLog::debug("DBG:
				In isRepeatable(). startDateTime[" . $startDateTime . "] until[" . $until . "]");
			if ($startDateTime >= $until) {
				CakeLog::debug("DBG: isRepeatable() return FALSE!
					 startDateTime[" . $startDateTime . "] >= until[" . $until . "]");
				return false;
			}
		} else {
			$count = isset($rrule['COUNT']) ? intval($rrule['COUNT']) : 3;	//初期値は３回繰り返す
			CakeLog::debug("DBG: In isRepeatable(COUNT case).
				 rrule[INDEX][" . $rrule['INDEX'] . "] count[" . $count . "]");
			if ($rrule['INDEX'] > $count) {
				CakeLog::debug("DBG: isRepeatable(COUNT case) return FALSE!
					 rrule[INDEX][" . $rrule['INDEX'] . "] > count[" . $count . "]");
				return false;
			}
		}
		return true;
	}

/**
 * 日付をフォーマットする
 *
 * @param string $time 日付時刻文字列 (YmdHis形式)
 * @param float $timezoneOffset timezoneoffset (-12.0 ～ +12.0) 初期値はnull
 * @param int $insertFlag insertFlag  1:登録 0:非登録(＝更新)  初期値は0
 * @param string $calendarWeek calendar_week(Sun|Mon|....Sat、日|月|....|土 などの文字列。初期値は''
 * @param string $timeFormat 日付時刻書式文字列 初期値は'YmdHis'
 * @param int $toFlag 「まで」フラグ 1:「まで」有り 0:「まで」無し 初期値は0
 * @return string 書式化された日付時刻文字列
 */
	/*
	//public static function calendarDateFormat($time, $timezoneOffset = null, $insertFlag = 0, $calendarWeek = '', $timeFormat = 'YmdHis', $toFlag = 0) {
		if (isset($timezoneOffset)) {

			//タイムゾーンを考慮した日付時刻文字列にする
			$time = self::setDateFormatWithTimezoneoffset($timezoneOffset, $insertFlag, $time);

		} else {
			$time = CalendarTime::timezoneDate($time, $insertFlag, 'YmdHis');
		}
		if ($toFlag && substr($time, 8) === '000000') {
			$timeFormat = str_replace('H', '24', $timeFormat);
			$timeFormat = str_replace('is', '0000', $timeFormat);
			$timeFormat = str_replace('i', '00', $timeFormat);
			$timestamp = mktime(0, 0, 0,
						intval(substr($time, 4, 2)), intval(substr($time, 6, 2)), intval(substr($time, 0, 4)));
			$timestamp = $timestamp - 1;
		} else {
			$timestamp = mktime(intval(substr($time, 8, 2)),
				 intval(substr($time, 10, 2)), intval(substr($time, 12, 2)),
						intval(substr($time, 4, 2)), intval(substr($time, 6, 2)), intval(substr($time, 0, 4)));
		}
		if ($calendarWeek === '') {
			$weekNameArray = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');
		} else {
			$weekNameArray = explode('|', $calendarWeek);
		}
		$week = date('w', $timestamp);
		return date(sprintf($timeFormat, $weekNameArray[$week]), $timestamp);
	}
	*/

/**
 * タイムゾーンつきの日付をフォーマットする
 *
 * @param float $timezoneOffset timezoneoffset (-12.0 ～ +12.0) 初期値はnull
 * @param int $insertFlag insertFlag  1:登録 0:非登録(＝更新)  初期値は0
 * @param string $time 日付時刻文字列 (YmdHis形式)
 * @return string タイムゾーンを考慮した書式化された日付時刻文字列
 */
	public static function setDateFormatWithTimezoneoffset($timezoneOffset, $insertFlag, $time) {
		$timezoneMiniteOffset = 0;
		if (round($timezoneOffset) !== intval($timezoneOffset)) {
			$timezoneOffset = ($timezoneOffset > 0) ? floor($timezoneOffset) : ceil($timezoneOffset);
			$timezoneMiniteOffset = ($timezoneOffset > 0) ? 30 : -30;			// 0.5minute
		}
		if ($insertFlag) {
			$timezoneOffset = -1 * $timezoneOffset;
		}
		return date('YmdHis', mktime(intval(substr($time, 8, 2)) + $timezoneOffset,
			intval(substr($time, 10, 2)) + $timezoneMiniteOffset, intval(substr($time, 12, 2)),
				intval(substr($time, 4, 2)), intval(substr($time, 6, 2)), intval(substr($time, 0, 4))));
	}

/**
 * bydayデータ生成
 *
 * @param int $timestamp timestamp
 * @param int $week week
 * @param int $wdayNum wdayNum
 * @param string $userTz ユーザタイムゾーン(ex.Asia/Tokyo)
 * @return string $byday 生成されたbyday(このとき、サーバー系(UTC)にもどして返す。
 */
	public static function getByday($timestamp, $week, $wdayNum, $userTz) {
		$date = new DateTime('now', (new DateTimeZone($userTz)));	//TZはユーザ系

		$date->setTimestamp($timestamp);
		$year = $date->format('Y');
		$month = $date->format('m');
		if ($week === -1) {
			//ここに飛び込むのは、BYDAYが'-1SA'といった値-1+曜日の書式のとき
			$lastDay = $date->format('t');	//$timestampの月の日数

			//カレンダー上(=ユーザー系)の指定月の月末日の00:00:00のタイムスタンプ
			$date->setDate($year, $month, $lastDay);	//日付の再設定
			$date->setTime(0, 0, 0);	//時刻の再設定
			$timestamp = $date->getTimestamp();
			$wLastDay = $date->format('w');	//カレンダー上の月末日の曜日のindex(0-6)

			//wdayNumはBYDAY(ex.3SA)の曜日(SA)のindex値(0-6,SAだと6)が入っている
			//($wdayNum <= $wLastDay)の場合は、月末日の曜日indexをそのままつかう。
			//($wdayNum > $wLastDay)の場合は、月末日の曜日indexに+7する
			//
			$wLastDay = ($wdayNum <= $wLastDay ? $wLastDay : 7 + $wLastDay);

			//下記は、カレンダー上の月末日に一番近い(=その月の最後の）BYDAYの
			//指定曜日に該当する日付の00:00:00のタイムスタンプを求める。
			//
			$date->setDate($year, $month, $lastDay - $wLastDay + $wdayNum); //日付の再設定
			$date->setTime(0, 0, 0);	//時刻の再設定
			$timestamp = $date->getTimestamp();
		} else {
			$w1Day = $date->format('w');	//timestampの曜日のindex a を取り出す
			// aがBYDAYの指定曜日以前ならaに1週加算。
			$w1Day = ($w1Day <= $wdayNum ? 7 + $w1Day : $w1Day);
			//第nＸ曜日(3MOなら第３月曜日)の日を計算する。
			$day = $week * 7 + $wdayNum + 1;
			//$day-$w1Dayで実際の1日が実曜日スタートになるよう調整した日のタイムスタンプ b を計算。
			$date->setDate($year, $month, $day - $w1Day);	//日付の再設定
			$date->setTime(0, 0, 0);	//時刻の再設定
			$timestamp = $date->getTimestamp();
		}

		// b(第x週第y曜日の実際の日)を日付時刻文字列型に変換
		//このとき、ユーザー系TZからサーバー系TZに直す
		$date->setTimestamp($timestamp);
		$date->setTimezone(new DateTimeZone('UTC'));
		$byday = $date->format('YmdHis');
		return $byday;
	}
}
