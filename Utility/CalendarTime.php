<?php
/**
 * CalendarTime Utility
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

/**
 * CalendarTime Utility
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Utility
 */
class CalendarTime {

/**
 * geTheTimeInTheLastHour
 *
 * "Y/m/d H:i:s"形式の指定日付時刻からの直近１時間の日付時刻(from,to)を取得
 *
 * @param string $ymdHis "Y/m/d H:i:s"形式の指定日付時刻
 * @return array 直近１時間の日付、from時刻, to時刻の配列を返す。
 */
	public static function geTheTimeInTheLastHour($ymdHis) {
		$baseHi = self::getHourColonMin($ymdHis);	// hh:mm
		$hour = intval(substr($baseHi, 0, 2));
		if ($hour <= 21) {
			$ymd = substr($ymdHis, 0, 10);	//YYYY-MM-DD
			$fromHi = sprintf("%02d:00", $hour + 1);	//hh:mm
			$toHi = sprintf("%02d:00", $hour + 2);	//hh:mm
		} else {
			//22,23時は特例1.datetimepickerには24:00という表記は存在しないので、当日の最後１時間弱(23:00-23:55)に抑え込む.
			$ymd = substr($ymdHis, 0, 10);	//YYYY-MM-DD
			$fromHi = '23:00';
			$toHi = '23:55';

			//} else {	//23時は特例2. 翌日の00:00-01:00とする。->NC2では23時台は22時台と同じ動作なのでNC3もそれに準じ特例2は中止.
			//	$year = intval(substr($ymdHis, 0, 4));
			//	$month = intval(substr($ymdHis, 5, 2));
			//	$day = intval(substr($ymdHis, 8, 2));
			//	list($yearOfNextDay, $monthOfNextDay, $nextDay) = self::getNextDay($year, $month, $day);
			//	$ymd = sprintf("%04d-%02d-%02d", $yearOfNextDay, $monthOfNextDay, $nextDay);
			//	$fromHi = '00:00';
			//	$toHi = '01:00';
		}
		return array($ymd, $fromHi, $toHi);
	}

/**
 * getHourColonMin
 *
 * "Y/m/d H:i:s"形式より"H:i"を抜き出す
 *
 * @param string $ymdHis "Y/m/d H:i:s"形式のDatetime文字列
 * @return string "H:i"形式の文字列
 */
	public static function getHourColonMin($ymdHis) {
		return substr($ymdHis, 11, 5);
	}

/**
 * getNextDay
 *
 * 年月日の次日を取得する
 *
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @return array 次日の年と月と日を配列で返す。
 */
	public static function getNextDay($year, $month, $day) {
		//mktimeのday引数の「その月の日数より大きい値は、翌月以降の該当する日を表す」仕様を応用して次日の年月日を求める。
		list($yearOfNextDay, $monthOfNextDay, $nextDay) = explode('/', date('Y/m/d', mktime(0, 0, 0, $month, $day + 1, $year)));
		return array($yearOfNextDay, $monthOfNextDay, $nextDay);
	}

/**
 * getPrevMonth
 *
 * 年月の前月を取得する
 *
 * @param int $year 年
 * @param int $month 月
 * @return array 前月の年と月を配列で返す。
 */
	public static function getPrevMonth($year, $month) {
		$yearOfPrevMonth = $year;
		$prevMonth = $month - 1;
		if ($month == 1) {
			$yearOfPrevMonth = $year - 1;
			$prevMonth = 12;
		}
		return array($yearOfPrevMonth, $prevMonth);
	}

/**
 * getNextMonth
 *
 * 年月の次月を取得する
 *
 * @param int $year 年
 * @param int $month 月
 * @return array 次月の年と次月の月を配列で返す。
 */
	public static function getNextMonth($year, $month) {
		$yearOfNextMonth = $year;
		$nextMonth = $month + 1;
		if ($month == 12) {
			$yearOfNextMonth = $year + 1;
			$nextMonth = 1;
		}
		return array($yearOfNextMonth, $nextMonth);
	}

/**
 * dt2CalDt
 *
 * Y/m/d H:i:s形式からYmdHis形式に変換する
 *
 * @param string $datetime "Y/m/d H:i:s"形式の日付時刻
 * @return string "YmdHis"形式の日付時刻
 */
	public static function dt2CalDt($datetime) {
		return (substr($datetime, 0, 4) . substr($datetime, 5, 2) . substr($datetime, 8, 2) .
			substr($datetime, 11, 2) . substr($datetime, 14, 2) . substr($datetime, 17, 2));
	}

/**
 * getWday
 *
 * 年月日から曜日(0-6)を返す
 *
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @return int 曜日(0-6)
 */
	public static function getWday($year, $month, $day) {
		$julianDay = gregoriantojd($month, $day, $year);	//指定年月日のグレゴリウス日をユリウス積算日に変換
		$wDay = jddayofweek($julianDay);	//ユリウス積算日から曜日を返す
		return $wDay;
	}

/**
 * getMonthlyInfo
 *
 * 月カレンダーで必要な情報を返す
 *
 * @param int $year 年
 * @param int $month 月
 * @return array 前月、次月、今月の月カレンダー情報の配列
 */
	public static function getMonthlyInfo($year, $month) {
		$julian1stDay = gregoriantojd($month, 1, $year);	//当月１日のグレゴリウス日をユリウス積算日に変換
		$wdayOf1stDay = jddayofweek($julian1stDay);	//ユリウス積算日から曜日を返す

		$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year); //指定した年とカレンダーについて月の日数を返す(グレゴリオ暦)
		$julianLastDay = gregoriantojd($month, $daysInMonth, $year);	//当月末日のグレゴリウス日をユリウス積算日に変換
		$wdayOfLastDay = jddayofweek($julianLastDay);	//ユリウス積算日から曜日を返す

		$numOfWeek = ceil(($daysInMonth + $wdayOf1stDay) / 7);	//当月の週数

		if ($month == 1) {
			$yearOfPrevMonth = $year - 1;
			$prevMonth = 12;
		} else {
			$yearOfPrevMonth = $year;
			$prevMonth = $month - 1;
		}
		$daysInPrevMonth = cal_days_in_month(CAL_GREGORIAN, $prevMonth, $yearOfPrevMonth);

		if ($month == 12) {
			$yearOfNextMonth = $year + 1;
			$nextMonth = 1;
		} else {
			$yearOfNextMonth = $year;
			$nextMonth = $month + 1;
		}
		$daysInNextMonth = cal_days_in_month(CAL_GREGORIAN, $nextMonth, $yearOfNextMonth);

		return array(
			'yearOfPrevMonth' => $yearOfPrevMonth,
			'prevMonth' => $prevMonth,
			'daysInPrevMonth' => $daysInPrevMonth,
			'yearOfNextMonth' => $yearOfNextMonth,
			'nextMonth' => $nextMonth,
			'daysInNextMonth' => $daysInNextMonth,
			'year' => $year,
			'month' => $month,
			'wdayOf1stDay' => $wdayOf1stDay,
			'daysInMonth' => $daysInMonth,
			'wdayOfLastDay' => $wdayOfLastDay,
			'numOfWeek' => $numOfWeek,
		);
	}

/**
 * transFromYmdHisToArray
 *
 * Y-m-d H:i:s形式の文字列から配列に変える
 *
 * @param string $strYmdHis Y-m-d H:i:s形式の文字列
 * @return mix 成功時は、各時間の構成要素からなる配列(array). 失敗時はfalse.
 */
	public static function transFromYmdHisToArray($strYmdHis) {
		$tmArray = array();
		if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2}) (\d{1,2}):(\d{1,2}):(\d{1,2})$/', $strYmdHis, $matches) !== 1) {
			return false;
		}
		$tmArray['year'] = $matches[1];
		$tmArray['month'] = $matches[2];
		$tmArray['day'] = $matches[3];
		$tmArray['hour'] = $matches[4];
		$tmArray['min'] = $matches[5];
		$tmArray['sec'] = $matches[6];
		return $tmArray;
	}

/**
 * タイムゾーンの計算処理
 * 登録時：第一引数が入っていれば画面から取得したものとみなして、_default_TZから引く
 * 　　　　第一引数がnullならば、_server_TZから引く
 * 表示時：GMTから会員のタイムゾーンを足す
 *
 * @param string $time time(YmdHis or Hisの形式)
 * @param int $insertFlag insertFlag(登録、更新時かどうか) default:1(true). MDがboolのデフォルト引数を拒否するのでintに変更
 * @param string  $format format default:date('YmdHis')
 * @return string timezone str
 */
	public static function timezoneDate($time = null, $insertFlag = 1, $format = null) {
		$_defaultTZ = Session::read('Calendars._timezoneOffset');
		$timeNullFlag = false;
		if ($time === null) {	//$time===null.つまり、画面以外のI/Fから渡された。
			$timeNullFlag = true;
			list($time, $timezoneOffset) = self::getTimezoneOffsetAndMinitueOffsetEtc($insertFlag);
		}
		if ($insertFlag) { // 登録時　サーバのタイムゾーンを引く
			$timezoneOffset = self::getTimezoneOffsetWithSummerTimeWhenInsert($timeNullFlag, $_defaultTZ, $timezoneOffset);
		} else {
			// 表示時　会員のタイムゾーンを足す（ログインしていない場合、デフォルトタイムゾーン）
			$timezoneOffset = Session::read('Calendars._timezoneOffset');
			if ($timezoneOffset === null) {
				$timezoneOffset = $_defaultTZ;
			}
		}

		return self::getFormatedTimezoneDate($time, $format, $timezoneOffset);
	}

/**
 * タイムゾーン等の設定
 *
 * @param int $insertFlag insertFlag
 * @return array array(string $time, fload $timezoneOffset)
 */
	public static function getTimezoneOffsetAndMinitueOffsetEtc($insertFlag) {
		$time = '';
		$timezoneOffset = 0;
		if ($insertFlag) {	//登録ケース
			$time = date('YmdHis');
		} else {	//更新ケース
			$timezoneOffset = Session::read('Calendars._server_TZ');
			$timezoneMinuteOffset = 0;
			if (round($timezoneOffset) != intval($timezoneOffset)) {
				$timezoneOffset = ($timezoneOffset > 0) ? floor($timezoneOffset) : ceil($timezoneOffset);
				$timezoneMinuteOffset = ($timezoneOffset > 0) ? 30 : -30;			// 0.5minute
			}
			//_server_TZだけ引く。つまり、サーバTZをつかい、LOCAL時間からUTC時間に変換する。
			$timezoneOffset = -1 * $timezoneOffset;
			$timezoneMinuteOffset = -1 * $timezoneMinuteOffset;
			$intTime = mktime(date('H') + $timezoneOffset, date('i') + $timezoneMinuteOffset, date('s'), date('m'), date('d'), date('Y'));
			$time = date('YmdHis', $intTime);
		}
		return array($time, $timezoneOffset);
	}

/**
 * 登録時のサーバタイムゾーン減算(サマータイム考慮)
 *
 * @param bool $timeNullFlag timeNullFlag
 * @param float $_defaultTZ defaultTimeZone
 * @param float $timezoneOffset timezoneOffset
 * @return float $timezoneOffset 計算した結果のtimezoneOffset
 */
	public static function getTimezoneOffsetWithSummerTimeWhenInsert($timeNullFlag, $_defaultTZ, $timezoneOffset) {
		$summertimeOffset = 0; // サマータイムも取得できれば考慮する
		if (date('I')) {
			$summertimeOffset = -1;
		}
		//
		// 第一引数が入っていれば画面から取得したものとみなして、_defaultTZから引く
		//
		if ($timeNullFlag) {
			//// $timezoneOffset = -1 * $config[_GENERAL_CONF_CATID]['server_TZ']['conf_value'];
			$timezoneOffset = -1 * Session::read('Calendars._server_TZ');
		} else {
			$timezoneOffset = -1 * $_defaultTZ;
		}
		$timezoneOffset += $summertimeOffset;
		return $timezoneOffset;
	}

/**
 * フォーマット指定のタイムゾーン考慮日付取得
 *
 * @param string $time time
 * @param string $format format
 * @param float $timezoneOffset timezoneOffset
 * @return string $date
 */
	public static function getFormatedTimezoneDate($time, $format, $timezoneOffset) {
		$timezoneMinuteOffset = 0;
		if (round($timezoneOffset) != intval($timezoneOffset)) {
			$timezoneOffset = ($timezoneOffset > 0) ? floor($timezoneOffset) : ceil($timezoneOffset);
			$timezoneMinuteOffset = ($timezoneOffset > 0) ? 30 : -30;			// 0.5minute
		}
		if (strlen($time) === 6) {	//時分秒
			$intTime = mktime(intval(substr($time, 0, 2)) + $timezoneOffset, intval(substr($time, 2, 2)) + $timezoneMinuteOffset, intval(substr($time, 4, 2)));
			if ($format === null) {
				$format = 'His';
			}
		} elseif (strlen($time) === 14) {	//年月日時分秒
			$intTime = mktime(intval(substr($time, 8, 2)) + $timezoneOffset, intval(substr($time, 10, 2)) + $timezoneMinuteOffset, intval(substr($time, 12, 2)),
							intval(substr($time, 4, 2)), intval(substr($time, 6, 2)), intval(substr($time, 0, 4)));
			if ($format == null) {
				$format = 'YmdHis';
			}
		}
		return date($format, $intTime);
	}
}
