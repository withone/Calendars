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
			$until = self::calendarDateFormat((substr($rrule['UNTIL'], 0, 8) . substr($rrule['UNTIL'], -6)), $timezoneOffset, 0, $calendarWeek);
			if ($startDateTime >= $until) {
				return false;
			}
		} else {
			$count = isset($rrule['COUNT']) ? intval($rrule['COUNT']) : 3;	//初期値は３回繰り返す
			if ($rrule['INDEX'] > $count) {
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
	public static function calendarDateFormat($time, $timezoneOffset = null, $insertFlag = 0, $calendarWeek = '', $timeFormat = 'YmdHis', $toFlag = 0) {
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
			$timestamp = mktime(intval(substr($time, 8, 2)), intval(substr($time, 10, 2)), intval(substr($time, 12, 2)),
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
		return date('YmdHis', mktime(intval(substr($time, 8, 2)) + $timezoneOffset, intval(substr($time, 10, 2)) + $timezoneMiniteOffset, intval(substr($time, 12, 2)),
						intval(substr($time, 4, 2)), intval(substr($time, 6, 2)), intval(substr($time, 0, 4))));
	}

/**
 * bydayデータ生成
 *
 * @param int $timestamp timestamp
 * @param int $week week
 * @param int $wdayNum wdayNum
 * @return string $byday 生成されたbyday
 */
	public static function getByday($timestamp, $week, $wdayNum) {
		$year = date('Y', $timestamp);
		$month = date('m', $timestamp);
		if ($week === -1) {
			//ここに飛び込むのは、BYDAYが'-1SA'といった値-1+曜日の書式のときのみ。
			//過去にこういう-1をつけたデータがあり、互換性・移植のためのこしていると推測できる。
			//
			$timestamp = mktime(0, 0, 0, $month, $lastDay, $year);
			$wLastDay = date('w', $timestamp);
			$wLastDay = ($wdayNum <= $wLastDay ? $wLastDay : 7 + $wLastDay);
			$timestamp = mktime(0, 0, 0, $month, $lastDay - $wLastDay + $wdayNum, $year);
		} else {
			$w1Day = date('w', $timestamp);		//開始日の同年同月1日の曜日(0-6) a を取り出す。
			$w1Day = ($w1Day <= $wdayNum ? 7 + $w1Day : $w1Day);	// aが指定曜日以前ならaに1週加算。
			$day = $week * 7 + $wdayNum + 1;	//1日が日曜日スタートとした場合の第x週第x日の「日」を計算
			$timestamp = mktime(0, 0, 0, $month, $day - $w1Day, $year);	//$day-$w1Dayで実際の1日が実曜日スタートになるよう調整した日のタイムスタンプ b を計算。
		}
		$byday = date('YmdHis', $timestamp); // b(第x週第y曜日の実際の日)を日付時刻文字列型に変換	//bbbbbb
		return $byday;
	}
}
